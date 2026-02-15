<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Items list with filters.
     */
    public function index(Request $request)
    {
        $query = DB::table('inv_items')
            ->leftJoin('inv_categories', 'inv_items.category_id', '=', 'inv_categories.id')
            ->leftJoin('inv_suppliers', 'inv_items.supplier_id', '=', 'inv_suppliers.id')
            ->leftJoin('inv_locations', 'inv_items.location_id', '=', 'inv_locations.id')
            ->where('inv_items.is_active', true)
            ->select(
                'inv_items.*',
                'inv_categories.name as category_name',
                'inv_suppliers.name as supplier_name',
                'inv_locations.name as location_name'
            );

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('inv_items.name', 'ilike', "%{$escaped}%")
                  ->orWhere('inv_items.sku', 'ilike', "%{$escaped}%")
                  ->orWhere('inv_items.barcode', 'ilike', "%{$escaped}%");
            });
        }

        // Filters
        if ($cat = $request->input('category')) {
            $query->where('inv_items.category_id', $cat);
        }
        if ($sup = $request->input('supplier')) {
            $query->where('inv_items.supplier_id', $sup);
        }
        if ($loc = $request->input('location')) {
            $query->where('inv_items.location_id', $loc);
        }

        // Stock status filter
        if ($stockStatus = $request->input('stock_status')) {
            match ($stockStatus) {
                'low' => $query->whereColumn('inv_items.quantity_on_hand', '<=', 'inv_items.reorder_level')
                               ->where('inv_items.quantity_on_hand', '>', 0),
                'out' => $query->where('inv_items.quantity_on_hand', '<=', 0),
                'expired' => $query->where('inv_items.expiry_date', '<', now()->toDateString()),
                'expiring' => $query->whereBetween('inv_items.expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]),
                default => null,
            };
        }

        $items = $query->orderBy('inv_items.name')->paginate(30);

        $categories = DB::table('inv_categories')->where('is_active', true)->orderBy('name')->get();
        $suppliers = DB::table('inv_suppliers')->where('is_active', true)->orderBy('name')->get();
        $locations = DB::table('inv_locations')->where('is_active', true)->orderBy('name')->get();

        // Stock stats
        $stats = [
            'total' => DB::table('inv_items')->where('is_active', true)->count(),
            'low' => DB::table('inv_items')->where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0)->count(),
            'out' => DB::table('inv_items')->where('is_active', true)
                ->where('quantity_on_hand', '<=', 0)->count(),
            'expiring' => DB::table('inv_items')->where('is_active', true)
                ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
        ];

        return view('inventory.index', compact('items', 'categories', 'suppliers', 'locations', 'stats'));
    }

    /**
     * Create item form.
     */
    public function create()
    {
        $categories = DB::table('inv_categories')->where('is_active', true)->orderBy('name')->get();
        $suppliers = DB::table('inv_suppliers')->where('is_active', true)->orderBy('name')->get();
        $locations = DB::table('inv_locations')->where('is_active', true)->orderBy('name')->get();

        return view('inventory.item-form', compact('categories', 'suppliers', 'locations'));
    }

    /**
     * Store new item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:inv_items,sku',
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:inv_categories,id',
            'supplier_id' => 'nullable|exists:inv_suppliers,id',
            'location_id' => 'nullable|exists:inv_locations,id',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'initial_qty' => 'nullable|integer|min:0',
        ]);

        $initialQty = $validated['initial_qty'] ?? 0;
        unset($validated['initial_qty']);

        $validated['quantity_on_hand'] = $initialQty;
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $itemId = DB::table('inv_items')->insertGetId($validated);

        // Record initial stock movement
        if ($initialQty > 0) {
            DB::table('inv_stock_movements')->insert([
                'item_id' => $itemId,
                'movement_type' => 'initial',
                'quantity' => $initialQty,
                'notes' => 'Initial stock on item creation',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        $this->audit->actionLog('inventory', 'create_item', 'success', [
            'item_id' => $itemId,
            'sku' => $validated['sku'],
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * View item detail.
     */
    public function show(int $item)
    {
        $itemData = DB::table('inv_items')
            ->leftJoin('inv_categories', 'inv_items.category_id', '=', 'inv_categories.id')
            ->leftJoin('inv_suppliers', 'inv_items.supplier_id', '=', 'inv_suppliers.id')
            ->leftJoin('inv_locations', 'inv_items.location_id', '=', 'inv_locations.id')
            ->where('inv_items.id', $item)
            ->select('inv_items.*', 'inv_categories.name as category_name',
                'inv_suppliers.name as supplier_name', 'inv_locations.name as location_name')
            ->first();

        if (!$itemData) abort(404);

        // Stock movements
        $movements = DB::table('inv_stock_movements')
            ->leftJoin('users', 'inv_stock_movements.created_by', '=', 'users.id')
            ->where('inv_stock_movements.item_id', $item)
            ->select('inv_stock_movements.*', 'users.full_name as user_name')
            ->orderByDesc('inv_stock_movements.created_at')
            ->limit(50)
            ->get();

        return view('inventory.show', compact('itemData', 'movements'));
    }

    /**
     * Edit item form.
     */
    public function edit(int $item)
    {
        $itemData = DB::table('inv_items')->where('id', $item)->first();
        if (!$itemData) abort(404);

        $categories = DB::table('inv_categories')->where('is_active', true)->orderBy('name')->get();
        $suppliers = DB::table('inv_suppliers')->where('is_active', true)->orderBy('name')->get();
        $locations = DB::table('inv_locations')->where('is_active', true)->orderBy('name')->get();

        return view('inventory.item-form', compact('itemData', 'categories', 'suppliers', 'locations'));
    }

    /**
     * Update item.
     */
    public function update(Request $request, int $item)
    {
        $validated = $request->validate([
            'sku' => "required|string|max:50|unique:inv_items,sku,{$item}",
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:inv_categories,id',
            'supplier_id' => 'nullable|exists:inv_suppliers,id',
            'location_id' => 'nullable|exists:inv_locations,id',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $validated['updated_at'] = now();

        DB::table('inv_items')->where('id', $item)->update($validated);

        $this->audit->actionLog('inventory', 'update_item', 'success', ['item_id' => $item]);

        return redirect()->route('inventory.show', $item)
            ->with('success', 'Item updated.');
    }

    /**
     * Stock adjustment.
     */
    public function adjust(Request $request, int $item)
    {
        $validated = $request->validate([
            'movement_type' => 'required|in:adjustment,receipt,disposal,return,transfer',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::table('inv_stock_movements')->insert([
            'item_id' => $item,
            'movement_type' => $validated['movement_type'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        // Update stock
        DB::table('inv_items')->where('id', $item)->increment('quantity_on_hand', $validated['quantity']);

        $this->audit->actionLog('inventory', 'stock_adjustment', 'success', [
            'item_id' => $item,
            'type' => $validated['movement_type'],
            'quantity' => $validated['quantity'],
        ]);

        return redirect()->route('inventory.show', $item)
            ->with('success', 'Stock adjusted.');
    }

    /**
     * Deactivate item.
     */
    public function deactivate(int $item)
    {
        DB::table('inv_items')->where('id', $item)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $this->audit->actionLog('inventory', 'deactivate_item', 'success', ['item_id' => $item]);

        return redirect()->route('inventory.index')
            ->with('success', 'Item deactivated.');
    }
}
