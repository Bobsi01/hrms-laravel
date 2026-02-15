<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Employee's own overtime requests.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('overtime.index', ['overtimeRequests' => collect()]);
        }

        $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
            ->orderByDesc('overtime_date')
            ->paginate(20);

        return view('overtime.index', compact('overtimeRequests'));
    }

    /**
     * Overtime request form.
     */
    public function create(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return redirect()->route('overtime.index')
                ->with('error', 'No employee record linked to your account. Please contact HR.');
        }

        return view('overtime.create');
    }

    /**
     * Store a new overtime request.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('overtime.index')
                ->with('error', 'No employee record linked to your account.');
        }

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'hours_worked'  => 'required|numeric|min:0.5|max:24',
            'reason'        => 'required|string|max:1000',
        ]);

        OvertimeRequest::create([
            'employee_id'   => $employee->id,
            'overtime_date' => $validated['overtime_date'],
            'hours_worked'  => $validated['hours_worked'],
            'reason'        => $validated['reason'],
            'status'        => 'pending',
        ]);

        $this->audit->actionLog('overtime', 'file_overtime', 'success', [
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('overtime.index')
            ->with('success', 'Overtime request submitted successfully.');
    }

    /**
     * Admin overtime management dashboard.
     */
    public function admin(Request $request)
    {
        $query = OvertimeRequest::with(['employee.department', 'employee.position', 'approvedBy'])
            ->orderByDesc('overtime_date');

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

        $overtimeRequests = $query->paginate(20);

        $stats = [
            'pending'  => OvertimeRequest::where('status', 'pending')->count(),
            'approved' => OvertimeRequest::where('status', 'approved')->count(),
            'rejected' => OvertimeRequest::where('status', 'rejected')->count(),
        ];

        return view('overtime.admin', compact('overtimeRequests', 'stats'));
    }

    /**
     * Approve an overtime request.
     */
    public function approve(OvertimeRequest $overtime)
    {
        if ($overtime->status !== 'pending') {
            return redirect()->route('overtime.admin')
                ->with('error', 'This overtime request has already been processed.');
        }

        $overtime->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->audit->actionLog('overtime', 'approve_overtime', 'success', [
            'overtime_id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
        ]);

        return redirect()->route('overtime.admin')
            ->with('success', 'Overtime request approved.');
    }

    /**
     * Reject an overtime request.
     */
    public function reject(Request $request, OvertimeRequest $overtime)
    {
        if ($overtime->status !== 'pending') {
            return redirect()->route('overtime.admin')
                ->with('error', 'This overtime request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $overtime->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->audit->actionLog('overtime', 'reject_overtime', 'success', [
            'overtime_id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
        ]);

        return redirect()->route('overtime.admin')
            ->with('success', 'Overtime request rejected.');
    }
}
