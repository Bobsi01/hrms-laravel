<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $query = User::with(['branch', 'employee.department', 'employee.position'])
            ->orderBy('full_name');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('full_name', 'ilike', "%{$escaped}%")
                  ->orWhere('email', 'ilike', "%{$escaped}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(20);

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'admins' => User::where('is_system_admin', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $employees = Employee::whereNull('user_id')
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        return view('admin.users.create', compact('branches', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
            'branch_id' => 'nullable|exists:branches,id',
            'is_system_admin' => 'boolean',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            'branch_id' => $validated['branch_id'],
            'is_system_admin' => $request->boolean('is_system_admin'),
        ]);

        // Link employee if provided
        if (!empty($validated['employee_id'])) {
            Employee::where('id', $validated['employee_id'])->update(['user_id' => $user->id]);
        }

        $this->audit->actionLog('users', 'create', 'success', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User account created.');
    }

    public function edit(User $user)
    {
        $user->load('employee.department', 'employee.position', 'branch');
        $branches = Branch::orderBy('name')->get();

        $superadminId = config('hrms.superadmin.user_id');
        $isSuperadmin = $user->isSuperadmin();

        return view('admin.users.edit', compact('user', 'branches', 'isSuperadmin'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->isSuperadmin()) {
            return back()->with('error', 'Cannot edit the superadmin account.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'role' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
            'branch_id' => 'nullable|exists:branches,id',
            'is_system_admin' => 'boolean',
        ]);

        $validated['is_system_admin'] = $request->boolean('is_system_admin');

        $user->update($validated);

        $this->audit->actionLog('users', 'update', 'success', [
            'user_id' => $user->id,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->isSuperadmin()) {
            return back()->with('error', 'Cannot reset superadmin password.');
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        $this->audit->actionLog('users', 'reset_password', 'success', [
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->isSuperadmin()) {
            return back()->with('error', 'Cannot delete the superadmin account.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        // Unlink employee
        Employee::where('user_id', $user->id)->update(['user_id' => null]);

        $this->audit->actionLog('users', 'delete', 'success', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User account deleted.');
    }
}
