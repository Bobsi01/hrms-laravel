<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index()
    {
        $locations = DB::table('inv_locations')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($locations as $loc) {
            $loc->item_count = DB::table('inv_items')
                ->where('location_id', $loc->id)
                ->where('is_active', true)
                ->count();
        }

        return view('inventory.locations', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = true;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $id = DB::table('inv_locations')->insertGetId($validated);

        $this->audit->actionLog('inventory', 'create_location', 'success', ['location_id' => $id]);

        return redirect()->route('inventory.locations.index')
            ->with('success', 'Location created.');
    }

    public function update(Request $request, int $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['updated_at'] = now();

        DB::table('inv_locations')->where('id', $location)->update($validated);

        $this->audit->actionLog('inventory', 'update_location', 'success', ['location_id' => $location]);

        return redirect()->route('inventory.locations.index')
            ->with('success', 'Location updated.');
    }

    public function destroy(int $location)
    {
        DB::table('inv_locations')->where('id', $location)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $this->audit->actionLog('inventory', 'deactivate_location', 'success', ['location_id' => $location]);

        return redirect()->route('inventory.locations.index')
            ->with('success', 'Location deactivated.');
    }
}
