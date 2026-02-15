<?php

namespace App\Http\Controllers\Positions;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\PositionAccessPermission;
use App\Services\AuditService;
use App\Services\PermissionCatalog;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $query = Position::with('department')->withCount('employees');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where('name', 'ilike', "%{$escaped}%");
        }

        $positions = $query->orderBy('name')->paginate(20);
        $canWrite = $this->permissions->userCan('hr_core', 'positions', 'write');

        return view('positions.index', compact('positions', 'canWrite'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:500',
            'base_salary'   => 'nullable|numeric|min:0',
        ]);

        $position = Position::create($validated);

        $this->audit->actionLog('positions', 'create_position', 'success', [
            'position_id' => $position->id,
            'name' => $position->name,
        ]);

        return redirect()->route('positions.index')
            ->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        $departments = Department::orderBy('name')->get();
        return view('positions.edit', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:500',
            'base_salary'   => 'nullable|numeric|min:0',
        ]);

        $position->update($validated);

        $this->audit->actionLog('positions', 'update_position', 'success', [
            'position_id' => $position->id,
            'name' => $position->name,
        ]);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $empCount = $position->employees()->where('status', 'active')->count();

        if ($empCount > 0) {
            return redirect()->route('positions.index')
                ->with('error', "Cannot delete position: {$empCount} active employee(s) are assigned.");
        }

        $name = $position->name;
        $id = $position->id;
        $position->delete();

        $this->audit->actionLog('positions', 'delete_position', 'success', [
            'position_id' => $id,
            'name' => $name,
        ]);

        return redirect()->route('positions.index')
            ->with('success', "Position \"{$name}\" deleted successfully.");
    }

    public function permissions(Position $position)
    {
        $position->load('accessPermissions');
        $catalog = PermissionCatalog::all();

        $currentPerms = [];
        foreach ($position->accessPermissions as $perm) {
            $currentPerms["{$perm->domain}.{$perm->resource_key}"] = $perm->access_level;
        }

        return view('positions.permissions', compact('position', 'catalog', 'currentPerms'));
    }

    public function updatePermissions(Request $request, Position $position)
    {
        $perms = $request->input('permissions', []);

        // Delete existing permissions
        PositionAccessPermission::where('position_id', $position->id)->delete();

        // Insert new permissions
        foreach ($perms as $key => $level) {
            if ($level === 'none' || !$level) {
                continue;
            }
            [$domain, $resource] = explode('.', $key, 2);
            PositionAccessPermission::create([
                'position_id'  => $position->id,
                'domain'       => $domain,
                'resource_key' => $resource,
                'access_level' => $level,
            ]);
        }

        // Clear cached permissions for all users with this position
        $userIds = \App\Models\Employee::where('position_id', $position->id)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        foreach ($userIds as $uid) {
            $this->permissions->clearUserCache($uid);
        }

        $this->audit->actionLog('positions', 'update_permissions', 'success', [
            'position_id' => $position->id,
            'name' => $position->name,
        ]);

        return redirect()->route('positions.permissions', $position)
            ->with('success', 'Permissions updated successfully.');
    }
}
