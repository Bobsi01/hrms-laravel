<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * POS terminal view.
     */
    public function index()
    {
        $categories = DB::table('inv_categories')->where('is_active', true)->orderBy('name')->get();

        return view('inventory.pos', compact('categories'));
    }

    /**
     * API: Get items for POS (JSON).
     */
    public function items(Request $request)
    {
        $query = DB::table('inv_items')
            ->leftJoin('inv_categories', 'inv_items.category_id', '=', 'inv_categories.id')
            ->where('inv_items.is_active', true)
            ->where('inv_items.quantity_on_hand', '>', 0)
            ->select('inv_items.id', 'inv_items.sku', 'inv_items.name', 'inv_items.barcode',
                'inv_items.selling_price', 'inv_items.quantity_on_hand', 'inv_items.category_id',
                'inv_categories.name as category_name');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('inv_items.name', 'ilike', "%{$escaped}%")
                  ->orWhere('inv_items.sku', 'ilike', "%{$escaped}%")
                  ->orWhere('inv_items.barcode', 'ilike', "%{$escaped}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('inv_items.category_id', $cat);
        }

        return response()->json($query->orderBy('inv_items.name')->limit(100)->get());
    }

    /**
     * API: Process checkout (JSON).
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:inv_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,charge,check',
            'discount' => 'nullable|numeric|min:0',
            'tendered' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);
            $discount = $validated['discount'] ?? 0;
            $total = $subtotal - $discount;
            $tendered = $validated['tendered'] ?? $total;
            $change = max(0, $tendered - $total);

            // Generate transaction number
            $txnNumber = 'TXN-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            $txnId = DB::table('inv_transactions')->insertGetId([
                'txn_number' => $txnNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'tendered' => $tendered,
                'change_amount' => $change,
                'payment_method' => $validated['payment_method'],
                'customer_name' => $validated['customer_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
                'cashier_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $lineItem) {
                DB::table('inv_transaction_items')->insert([
                    'transaction_id' => $txnId,
                    'item_id' => $lineItem['id'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['price'],
                    'subtotal' => $lineItem['quantity'] * $lineItem['price'],
                    'created_at' => now(),
                ]);

                // Deduct stock
                DB::table('inv_items')->where('id', $lineItem['id'])
                    ->decrement('quantity_on_hand', $lineItem['quantity']);

                // Stock movement
                DB::table('inv_stock_movements')->insert([
                    'item_id' => $lineItem['id'],
                    'movement_type' => 'sale',
                    'quantity' => -$lineItem['quantity'],
                    'reference_type' => 'transaction',
                    'reference_id' => $txnId,
                    'notes' => "POS Sale #{$txnNumber}",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            $this->audit->actionLog('pos', 'checkout', 'success', [
                'txn_id' => $txnId,
                'total' => $total,
            ]);

            return response()->json([
                'success' => true,
                'txn_number' => $txnNumber,
                'total' => $total,
                'change' => $change,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('POS checkout failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Checkout failed.'], 500);
        }
    }

    /**
     * Transactions list.
     */
    public function transactions(Request $request)
    {
        $query = DB::table('inv_transactions')
            ->leftJoin('users', 'inv_transactions.cashier_id', '=', 'users.id')
            ->select('inv_transactions.*', 'users.full_name as cashier_name');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('inv_transactions.txn_number', 'ilike', "%{$escaped}%")
                  ->orWhere('inv_transactions.customer_name', 'ilike', "%{$escaped}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('inv_transactions.status', $status);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('inv_transactions.created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('inv_transactions.created_at', '<=', $to);
        }

        $transactions = $query->orderByDesc('inv_transactions.created_at')->paginate(25);

        return view('inventory.transactions', compact('transactions'));
    }

    /**
     * Void a transaction.
     */
    public function voidTransaction(int $txn)
    {
        $transaction = DB::table('inv_transactions')->where('id', $txn)->first();
        if (!$transaction || $transaction->status !== 'completed') {
            return back()->with('error', 'Cannot void this transaction.');
        }

        DB::beginTransaction();
        try {
            // Restore stock
            $lineItems = DB::table('inv_transaction_items')->where('transaction_id', $txn)->get();
            foreach ($lineItems as $li) {
                DB::table('inv_items')->where('id', $li->item_id)
                    ->increment('quantity_on_hand', $li->quantity);

                DB::table('inv_stock_movements')->insert([
                    'item_id' => $li->item_id,
                    'movement_type' => 'return',
                    'quantity' => $li->quantity,
                    'reference_type' => 'void',
                    'reference_id' => $txn,
                    'notes' => "Void of TXN #{$transaction->txn_number}",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            DB::table('inv_transactions')->where('id', $txn)->update([
                'status' => 'voided',
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->audit->actionLog('pos', 'void_transaction', 'success', ['txn_id' => $txn]);

            return back()->with('success', 'Transaction voided. Stock restored.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to void transaction.');
        }
    }

    /**
     * Reports dashboard.
     */
    public function reports(Request $request)
    {
        $tab = $request->input('tab', 'overview');
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $data = [];

        if ($tab === 'overview') {
            $data['totalItems'] = DB::table('inv_items')->where('is_active', true)->count();
            $data['stockValueCost'] = DB::table('inv_items')->where('is_active', true)
                ->selectRaw('COALESCE(SUM(cost_price * quantity_on_hand), 0) as val')->value('val');
            $data['stockValueRetail'] = DB::table('inv_items')->where('is_active', true)
                ->selectRaw('COALESCE(SUM(selling_price * quantity_on_hand), 0) as val')->value('val');
            $data['periodSales'] = DB::table('inv_transactions')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
                ->sum('total');
            $data['lowStock'] = DB::table('inv_items')->where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0)->count();
            $data['outOfStock'] = DB::table('inv_items')->where('is_active', true)
                ->where('quantity_on_hand', '<=', 0)->count();

            // Top 10 selling
            $data['topSelling'] = DB::table('inv_transaction_items')
                ->join('inv_transactions', 'inv_transaction_items.transaction_id', '=', 'inv_transactions.id')
                ->join('inv_items', 'inv_transaction_items.item_id', '=', 'inv_items.id')
                ->where('inv_transactions.status', 'completed')
                ->whereBetween('inv_transactions.created_at', [$from, $to . ' 23:59:59'])
                ->groupBy('inv_items.id', 'inv_items.name', 'inv_items.sku')
                ->selectRaw('inv_items.name, inv_items.sku, SUM(inv_transaction_items.quantity) as total_qty, SUM(inv_transaction_items.subtotal) as total_revenue')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get();
        }

        if ($tab === 'expiry') {
            $data['items'] = DB::table('inv_items')
                ->where('is_active', true)
                ->whereNotNull('expiry_date')
                ->orderBy('expiry_date')
                ->get();
        }

        return view('inventory.reports', compact('tab', 'from', 'to', 'data'));
    }

    /**
     * Purchase orders list.
     */
    public function purchaseOrders(Request $request)
    {
        $query = DB::table('inv_purchase_orders')
            ->leftJoin('inv_suppliers', 'inv_purchase_orders.supplier_id', '=', 'inv_suppliers.id')
            ->leftJoin('users', 'inv_purchase_orders.created_by', '=', 'users.id')
            ->select('inv_purchase_orders.*', 'inv_suppliers.name as supplier_name', 'users.full_name as creator_name');

        if ($status = $request->input('status')) {
            $query->where('inv_purchase_orders.status', $status);
        }

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where('inv_purchase_orders.po_number', 'ilike', "%{$escaped}%");
        }

        $orders = $query->orderByDesc('inv_purchase_orders.created_at')->paginate(20);

        $stats = [
            'total' => DB::table('inv_purchase_orders')->count(),
            'draft' => DB::table('inv_purchase_orders')->where('status', 'draft')->count(),
            'ordered' => DB::table('inv_purchase_orders')->where('status', 'ordered')->count(),
            'received' => DB::table('inv_purchase_orders')->where('status', 'received')->count(),
        ];

        return view('inventory.purchase-orders', compact('orders', 'stats'));
    }

    /**
     * Create PO form.
     */
    public function createPurchaseOrder()
    {
        $suppliers = DB::table('inv_suppliers')->where('is_active', true)->orderBy('name')->get();
        $items = DB::table('inv_items')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']);

        return view('inventory.purchase-order-form', compact('suppliers', 'items'));
    }

    /**
     * Store PO.
     */
    public function storePurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:inv_suppliers,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:inv_items,id',
            'line_items.*.quantity_ordered' => 'required|integer|min:1',
            'line_items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $poNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            $totalAmount = collect($validated['line_items'])->sum(fn ($li) => $li['quantity_ordered'] * $li['unit_cost']);

            $poId = DB::table('inv_purchase_orders')->insertGetId([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['line_items'] as $li) {
                DB::table('inv_purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'item_id' => $li['item_id'],
                    'quantity_ordered' => $li['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $li['unit_cost'],
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            $this->audit->actionLog('inventory', 'create_po', 'success', ['po_id' => $poId]);

            return redirect()->route('inventory.purchase-orders.index')
                ->with('success', "Purchase Order #{$poNumber} created.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create purchase order.');
        }
    }

    /**
     * View PO detail.
     */
    public function showPurchaseOrder(int $po)
    {
        $order = DB::table('inv_purchase_orders')
            ->leftJoin('inv_suppliers', 'inv_purchase_orders.supplier_id', '=', 'inv_suppliers.id')
            ->leftJoin('users', 'inv_purchase_orders.created_by', '=', 'users.id')
            ->where('inv_purchase_orders.id', $po)
            ->select('inv_purchase_orders.*', 'inv_suppliers.name as supplier_name',
                'inv_suppliers.contact_person', 'inv_suppliers.phone as supplier_phone',
                'users.full_name as creator_name')
            ->first();

        if (!$order) abort(404);

        $lineItems = DB::table('inv_purchase_order_items')
            ->join('inv_items', 'inv_purchase_order_items.item_id', '=', 'inv_items.id')
            ->where('inv_purchase_order_items.purchase_order_id', $po)
            ->select('inv_purchase_order_items.*', 'inv_items.name as item_name', 'inv_items.sku')
            ->get();

        return view('inventory.purchase-order-show', compact('order', 'lineItems'));
    }

    /**
     * Update PO status.
     */
    public function updatePurchaseOrderStatus(Request $request, int $po)
    {
        $validated = $request->validate([
            'status' => 'required|in:ordered,cancelled',
        ]);

        $order = DB::table('inv_purchase_orders')->where('id', $po)->first();
        if (!$order) return back()->with('error', 'PO not found.');

        DB::table('inv_purchase_orders')->where('id', $po)->update([
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        $this->audit->actionLog('inventory', 'update_po_status', 'success', [
            'po_id' => $po,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'PO status updated to ' . $validated['status'] . '.');
    }

    /**
     * Receive items for PO.
     */
    public function receivePurchaseOrder(Request $request, int $po)
    {
        $validated = $request->validate([
            'received' => 'required|array',
            'received.*' => 'integer|min:0',
        ]);

        $order = DB::table('inv_purchase_orders')->where('id', $po)->first();
        if (!$order || in_array($order->status, ['cancelled', 'received'])) {
            return back()->with('error', 'Cannot receive items for this PO.');
        }

        DB::beginTransaction();
        try {
            $lineItems = DB::table('inv_purchase_order_items')
                ->where('purchase_order_id', $po)->get();

            $allReceived = true;
            foreach ($lineItems as $li) {
                $qty = $validated['received'][$li->id] ?? 0;
                if ($qty <= 0) {
                    if ($li->quantity_received < $li->quantity_ordered) $allReceived = false;
                    continue;
                }

                $remaining = $li->quantity_ordered - $li->quantity_received;
                $qty = min($qty, $remaining);

                DB::table('inv_purchase_order_items')->where('id', $li->id)
                    ->increment('quantity_received', $qty);

                // Update stock
                DB::table('inv_items')->where('id', $li->item_id)
                    ->increment('quantity_on_hand', $qty);

                DB::table('inv_stock_movements')->insert([
                    'item_id' => $li->item_id,
                    'movement_type' => 'receipt',
                    'quantity' => $qty,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po,
                    'notes' => "PO #{$order->po_number} receipt",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                if (($li->quantity_received + $qty) < $li->quantity_ordered) {
                    $allReceived = false;
                }
            }

            $newStatus = $allReceived ? 'received' : 'partial';
            DB::table('inv_purchase_orders')->where('id', $po)->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->audit->actionLog('inventory', 'receive_po', 'success', [
                'po_id' => $po,
                'new_status' => $newStatus,
            ]);

            return back()->with('success', 'Items received. Status: ' . $newStatus . '.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to receive items.');
        }
    }
}
