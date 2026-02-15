<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index()
    {
        $categories = DB::table('inv_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Item counts
        foreach ($categories as $cat) {
            $cat->item_count = DB::table('inv_items')
                ->where('category_id', $cat->id)
                ->where('is_active', true)
                ->count();
        }

        return view('inventory.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = true;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $id = DB::table('inv_categories')->insertGetId($validated);

        $this->audit->actionLog('inventory', 'create_category', 'success', ['category_id' => $id]);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category created.');
    }

    public function update(Request $request, int $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['updated_at'] = now();

        DB::table('inv_categories')->where('id', $category)->update($validated);

        $this->audit->actionLog('inventory', 'update_category', 'success', ['category_id' => $category]);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(int $category)
    {
        DB::table('inv_categories')->where('id', $category)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $this->audit->actionLog('inventory', 'deactivate_category', 'success', ['category_id' => $category]);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category deactivated.');
    }
}
