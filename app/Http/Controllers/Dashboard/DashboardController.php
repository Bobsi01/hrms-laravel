<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $stats = [];

        // Basic stats available to most users
        if ($this->permissions->userHasAccess($user->id, 'hr_core', 'employees', 'read')) {
            $stats['total_employees'] = Employee::where('status', 'active')->count();
            $stats['total_departments'] = Department::count();
        }

        if ($this->permissions->userHasAccess($user->id, 'leave', 'leave_admin', 'read')) {
            $stats['pending_leaves'] = LeaveRequest::where('status', 'pending')->count();
        }

        if ($this->permissions->userHasAccess($user->id, 'attendance', 'attendance_admin', 'read')) {
            $stats['today_attendance'] = Attendance::where('date', today())->count();
        }

        return view('dashboard.index', compact('stats'));
    }
}
