<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Memo;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $stats = [];
        $actionItems = [];
        $systemPulse = [];

        // Basic stats
        if ($this->permissions->userHasAccess($userId, 'hr_core', 'employees', 'read')) {
            $stats['total_employees'] = Employee::where('status', 'active')->count();
            $stats['total_departments'] = Department::count();
        }

        if ($this->permissions->userHasAccess($userId, 'leave', 'leave_admin', 'read')) {
            $stats['pending_leaves'] = LeaveRequest::where('status', 'pending')->count();
        }

        if ($this->permissions->userHasAccess($userId, 'attendance', 'attendance_admin', 'read')) {
            $stats['today_attendance'] = Attendance::where('date', today())->count();
        }

        // Extended stats for privileged users
        if ($this->permissions->userHasAccess($userId, 'payroll', 'payroll_runs', 'read')) {
            $stats['open_payroll'] = PayrollRun::whereIn('status', ['draft', 'for_review', 'submitted'])->count();
        }

        if ($this->permissions->userHasAccess($userId, 'payroll', 'overtime', 'read')) {
            $stats['pending_overtime'] = OvertimeRequest::where('status', 'pending')->count();
        }

        // Action items for admins
        $isAdmin = $user->is_system_admin || $this->permissions->userHasAccess($userId, 'system', 'system_settings', 'read');

        if ($isAdmin) {
            if (($stats['pending_leaves'] ?? 0) > 0) {
                $actionItems[] = [
                    'label' => 'Leave requests awaiting review',
                    'count' => $stats['pending_leaves'],
                    'route' => route('leave.admin'),
                    'description' => 'Review and approve/reject pending leave requests',
                ];
            }
            if (($stats['pending_overtime'] ?? 0) > 0) {
                $actionItems[] = [
                    'label' => 'Overtime requests pending',
                    'count' => $stats['pending_overtime'],
                    'route' => route('overtime.admin'),
                    'description' => 'Approve or reject overtime filings',
                ];
            }
            if (($stats['open_payroll'] ?? 0) > 0) {
                $actionItems[] = [
                    'label' => 'Open payroll runs',
                    'count' => $stats['open_payroll'],
                    'route' => route('payroll.index'),
                    'description' => 'Payroll runs requiring attention',
                ];
            }

            // System pulse — latest audit entries
            $systemPulse = AuditLog::orderByDesc('created_at')
                ->limit(5)
                ->get(['action', 'module', 'details', 'created_at']);

            // Recent system events count (24h)
            $stats['system_events_24h'] = AuditLog::where('created_at', '>=', now()->subDay())->count();
        }

        // Quick links for admin
        $quickLinks = [];
        if ($isAdmin) {
            $quickLinks = [
                ['label' => 'Employees', 'route' => route('employees.index'), 'description' => 'Manage employee records'],
                ['label' => 'Departments', 'route' => route('departments.index'), 'description' => 'Organize departments'],
                ['label' => 'Positions', 'route' => route('positions.index'), 'description' => 'Position & permission setup'],
                ['label' => 'Audit Trail', 'route' => route('audit.index'), 'description' => 'System activity history'],
                ['label' => 'Memos', 'route' => route('memos.admin'), 'description' => 'Company announcements'],
            ];
        }

        // Self-service items for regular employees
        $selfService = [];
        $employee = $user->employee;
        if ($employee) {
            $myPendingLeaves = LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count();
            $selfService['pending_leaves'] = $myPendingLeaves;
            $selfService['has_employee'] = true;
        }

        return view('dashboard.index', compact('stats', 'actionItems', 'systemPulse', 'quickLinks', 'selfService', 'isAdmin'));
    }
}
