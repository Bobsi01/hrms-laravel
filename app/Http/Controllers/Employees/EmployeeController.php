<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\AuditService;
use App\Services\PermissionService;
use App\Traits\AuditsModelChanges;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use AuditsModelChanges;
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Employee list with search, filtering, pagination.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'branch']);

        // Status filter (default: active)
        $status = $request->input('status', 'active');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('last_name', 'ilike', "%{$escaped}%")
                  ->orWhere('employee_code', 'ilike', "%{$escaped}%")
                  ->orWhere('email', 'ilike', "%{$escaped}%");
            });
        }

        // Filter by department
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        $employees = $query->orderBy('last_name')->paginate(20);
        $departments = Department::orderBy('name')->get();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'on_leave' => Employee::where('status', 'on-leave')->count(),
            'inactive' => Employee::whereIn('status', ['terminated', 'resigned'])->count(),
        ];

        $canWrite = $this->permissions->userCan('hr_core', 'employees', 'write');

        return view('employees.index', compact('employees', 'departments', 'stats', 'canWrite'));
    }

    /**
     * Show employee creation form.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('employees.create', compact('departments', 'positions', 'branches'));
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:employees,email',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'position_id'   => 'required|exists:positions,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'hire_date'     => 'required|date',
            'employment_type' => 'required|in:regular,probationary,contract,part-time',
            'status'        => 'required|in:active,terminated,resigned,on-leave',
            'salary'        => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::create($validated);

        $this->audit->actionLog('employees', 'create_employee', 'success', [
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
        ]);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * View employee profile.
     */
    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'branch', 'user']);

        $canWrite = $this->permissions->userCan('hr_core', 'employees', 'write');

        return view('employees.show', compact('employee', 'canWrite'));
    }

    /**
     * Edit employee form.
     */
    public function edit(Employee $employee)
    {
        $employee->load(['department', 'position', 'branch']);

        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'positions', 'branches'));
    }

    /**
     * Update employee record.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => "required|string|max:50|unique:employees,employee_code,{$employee->id}",
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => "required|email|max:150|unique:employees,email,{$employee->id}",
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'position_id'   => 'required|exists:positions,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'hire_date'     => 'required|date',
            'employment_type' => 'required|in:regular,probationary,contract,part-time',
            'status'        => 'required|in:active,terminated,resigned,on-leave',
            'salary'        => 'nullable|numeric|min:0',
        ]);

        $changes = $this->captureChanges($employee, $validated, ['salary']);
        $employee->update($validated);

        $this->audit->actionLog('employees', 'update_employee', 'success', array_merge([
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
        ], $changes));

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Delete employee.
     */
    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $id = $employee->id;
        $snapshot = $this->snapshotForDelete($employee, [
            'id', 'employee_code', 'first_name', 'last_name', 'email',
            'department_id', 'position_id', 'branch_id', 'status',
        ]);

        $employee->delete();

        $this->audit->actionLog('employees', 'delete_employee', 'success', array_merge([
            'employee_id' => $id,
            'name' => $name,
        ], $snapshot));

        return redirect()->route('employees.index')
            ->with('success', "Employee \"{$name}\" deleted successfully.");
    }
}
