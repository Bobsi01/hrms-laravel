<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected PermissionService $permissions
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

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type'  => $validated['leave_type'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
            'total_days'  => $totalDays,
            'status'      => 'pending',
            'remarks'     => $validated['reason'],
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request submitted successfully.');
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

        $leaveRequests = $query->paginate(20);

        return view('leave.admin', compact('leaveRequests'));
    }
}
