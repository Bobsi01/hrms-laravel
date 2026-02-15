<?php

namespace App\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $query = Department::withCount('employees', 'supervisors');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where('name', 'ilike', "%{$escaped}%");
        }

        $departments = $query->orderBy('name')->paginate(20);
        $canWrite = $this->permissions->userCan('hr_core', 'departments', 'write');

        return view('departments.index', compact('departments', 'canWrite'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string|max:500',
        ]);

        $department = Department::create($validated);

        $this->audit->actionLog('departments', 'create_department', 'success', [
            'department_id' => $department->id,
            'name' => $department->name,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $department->load('supervisors');
        $employees = Employee::where('status', 'active')->orderBy('last_name')->get();

        return view('departments.edit', compact('department', 'employees'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'        => "required|string|max:100|unique:departments,name,{$department->id}",
            'description' => 'nullable|string|max:500',
        ]);

        $department->update($validated);

        $this->audit->actionLog('departments', 'update_department', 'success', [
            'department_id' => $department->id,
            'name' => $department->name,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Check for active employees
        $empCount = Employee::where('department_id', $department->id)
            ->where('status', 'active')->count();

        if ($empCount > 0) {
            return redirect()->route('departments.index')
                ->with('error', "Cannot delete department: {$empCount} active employee(s) are still assigned.");
        }

        $name = $department->name;
        $id = $department->id;
        $department->delete();

        $this->audit->actionLog('departments', 'delete_department', 'success', [
            'department_id' => $id,
            'name' => $name,
        ]);

        return redirect()->route('departments.index')
            ->with('success', "Department \"{$name}\" deleted successfully.");
    }

    public function supervisors(Department $department)
    {
        $department->load(['supervisors.supervisor']);
        $users = User::where('status', 'active')->orderBy('full_name')->get();

        return view('departments.supervisors', compact('department', 'users'));
    }

    public function addSupervisor(Request $request, Department $department)
    {
        $validated = $request->validate([
            'supervisor_user_id' => 'required|exists:users,id',
        ]);

        // Check if already a supervisor
        $exists = DepartmentSupervisor::where('department_id', $department->id)
            ->where('supervisor_user_id', $validated['supervisor_user_id'])->exists();

        if ($exists) {
            return redirect()->route('departments.supervisors', $department)
                ->with('error', 'This user is already a supervisor of this department.');
        }

        DepartmentSupervisor::create([
            'department_id' => $department->id,
            'supervisor_user_id' => $validated['supervisor_user_id'],
        ]);

        $this->audit->actionLog('departments', 'add_supervisor', 'success', [
            'department_id' => $department->id,
            'supervisor_user_id' => $validated['supervisor_user_id'],
        ]);

        return redirect()->route('departments.supervisors', $department)
            ->with('success', 'Supervisor added successfully.');
    }

    public function removeSupervisor(Department $department, DepartmentSupervisor $supervisor)
    {
        $supervisor->delete();

        $this->audit->actionLog('departments', 'remove_supervisor', 'success', [
            'department_id' => $department->id,
        ]);

        return redirect()->route('departments.supervisors', $department)
            ->with('success', 'Supervisor removed.');
    }
}
