<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestAction;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Employee's own leave history.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('leave.index', ['leaveRequests' => collect()]);
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('leave.index', compact('leaveRequests'));
    }

    /**
     * Leave filing form.
     */
    public function create(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return redirect()->route('leave.index')
                ->with('error', 'No employee record linked to your account. Please contact HR.');
        }

        return view('leave.create');
    }

    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('leave.index')
                ->with('error', 'No employee record linked to your account.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|string|in:sick,vacation,emergency,unpaid,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:1000',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type'  => $validated['leave_type'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
            'total_days'  => $totalDays,
            'status'      => 'pending',
            'remarks'     => $validated['reason'],
        ]);

        $this->audit->actionLog('leave', 'file_leave', 'success', [
            'leave_id' => $leave->id,
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * View a single leave request.
     */
    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.department', 'employee.position', 'actions.actor']);
        $canApprove = $this->permissions->userCan('leave', 'leave_admin', 'write');

        return view('leave.show', compact('leave', 'canApprove'));
    }

    /**
     * Admin leave approval dashboard.
     */
    public function admin(Request $request)
    {
        $query = LeaveRequest::with(['employee.department', 'employee.position'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->whereHas('employee', function ($q) use ($escaped) {
                $q->where('first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('last_name', 'ilike', "%{$escaped}%");
            });
        }

        $leaveRequests = $query->paginate(20);

        $stats = [
            'pending'  => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return view('leave.admin', compact('leaveRequests', 'stats'));
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->route('leave.admin')
                ->with('error', 'This leave request has already been processed.');
        }

        $leave->update(['status' => 'approved']);

        LeaveRequestAction::create([
            'leave_request_id' => $leave->id,
            'action'           => 'approved',
            'acted_by'         => auth()->id(),
            'acted_at'         => now(),
        ]);

        $this->audit->actionLog('leave', 'approve_leave', 'success', [
            'leave_id' => $leave->id,
            'employee_id' => $leave->employee_id,
        ]);

        return redirect()->route('leave.admin')
            ->with('success', 'Leave request approved.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->route('leave.admin')
                ->with('error', 'This leave request has already been processed.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $leave->update(['status' => 'rejected']);

        LeaveRequestAction::create([
            'leave_request_id' => $leave->id,
            'action'           => 'rejected',
            'reason'           => $validated['reason'],
            'acted_by'         => auth()->id(),
            'acted_at'         => now(),
        ]);

        $this->audit->actionLog('leave', 'reject_leave', 'success', [
            'leave_id' => $leave->id,
            'employee_id' => $leave->employee_id,
        ]);

        return redirect()->route('leave.admin')
            ->with('success', 'Leave request rejected.');
    }
}
