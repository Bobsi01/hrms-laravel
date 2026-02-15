<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index()
    {
        $suppliers = DB::table('inv_suppliers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($suppliers as $sup) {
            $sup->item_count = DB::table('inv_items')
                ->where('supplier_id', $sup->id)
                ->where('is_active', true)
                ->count();
        }

        return view('inventory.suppliers', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = true;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $id = DB::table('inv_suppliers')->insertGetId($validated);

        $this->audit->actionLog('inventory', 'create_supplier', 'success', ['supplier_id' => $id]);

        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier created.');
    }

    public function update(Request $request, int $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['updated_at'] = now();

        DB::table('inv_suppliers')->where('id', $supplier)->update($validated);

        $this->audit->actionLog('inventory', 'update_supplier', 'success', ['supplier_id' => $supplier]);

        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier updated.');
    }

    public function destroy(int $supplier)
    {
        DB::table('inv_suppliers')->where('id', $supplier)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $this->audit->actionLog('inventory', 'deactivate_supplier', 'success', ['supplier_id' => $supplier]);

        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier deactivated.');
    }
}
