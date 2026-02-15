<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\DataCorrectionRequest;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\PermissionService;
use App\Traits\AuditsModelChanges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataCorrectionController extends Controller
{
    use AuditsModelChanges;

    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Employee self-service: list own correction requests.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $requests = $employee
            ? DataCorrectionRequest::where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->paginate(20)
            : collect();

        return view('compliance.corrections.index', [
            'pageTitle'  => 'My Data Correction Requests',
            'requests'   => $requests,
            'employee'   => $employee,
            'categories' => DataCorrectionRequest::categories(),
        ]);
    }

    /**
     * Employee self-service: show form to file a correction request.
     */
    public function create()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->route('corrections.index')
                ->with('error', 'No employee record linked to your account.');
        }

        return view('compliance.corrections.create', [
            'pageTitle'  => 'File Data Correction Request',
            'employee'   => $employee,
            'categories' => DataCorrectionRequest::categories(),
            'fields'     => DataCorrectionRequest::correctableFields(),
        ]);
    }

    /**
     * Employee self-service: store a new correction request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->route('corrections.index')
                ->with('error', 'No employee record linked to your account.');
        }

        $validated = $request->validate([
            'category'        => 'required|in:' . implode(',', array_keys(DataCorrectionRequest::categories())),
            'field_name'      => 'required|string|max:100',
            'requested_value' => 'required|string|max:500',
            'reason'          => 'required|string|max:1000',
        ]);

        // Resolve current value from employee record
        $currentValue = $employee->getAttribute($validated['field_name']);

        $correction = DataCorrectionRequest::create([
            'employee_id'     => $employee->id,
            'requested_by'    => $user->id,
            'category'        => $validated['category'],
            'field_name'      => $validated['field_name'],
            'current_value'   => $currentValue,
            'requested_value' => $validated['requested_value'],
            'reason'          => $validated['reason'],
            'status'          => 'pending',
        ]);

        $this->audit->actionLog('data_corrections', 'create', 'success', [
            'correction_id' => $correction->id,
            'employee_id'   => $employee->id,
            'field'         => $validated['field_name'],
        ]);

        return redirect()->route('corrections.index')
            ->with('success', 'Data correction request submitted successfully. An HR administrator will review it shortly.');
    }

    /**
     * Admin: list all pending/recent correction requests.
     */
    public function admin(Request $request)
    {
        $query = DataCorrectionRequest::with(['employee', 'requester', 'reviewer']);

        $status = $request->input('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'pending'   => DataCorrectionRequest::where('status', 'pending')->count(),
            'approved'  => DataCorrectionRequest::where('status', 'approved')->count(),
            'rejected'  => DataCorrectionRequest::where('status', 'rejected')->count(),
            'completed' => DataCorrectionRequest::where('status', 'completed')->count(),
        ];

        return view('compliance.corrections.admin', [
            'pageTitle'  => 'Data Correction Requests',
            'requests'   => $requests,
            'stats'      => $stats,
            'status'     => $status,
            'categories' => DataCorrectionRequest::categories(),
        ]);
    }

    /**
     * Admin: approve a correction request.
     */
    public function approve(Request $request, DataCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            return redirect()->route('corrections.admin')
                ->with('error', 'This request has already been processed.');
        }

        $correction->update([
            'status'       => 'approved',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->input('review_notes', ''),
        ]);

        // Apply the correction to the employee record
        $employee = $correction->employee;
        if ($employee && $employee->isFillable($correction->field_name)) {
            $oldValue = $employee->getAttribute($correction->field_name);
            $employee->update([$correction->field_name => $correction->requested_value]);

            $correction->update(['status' => 'completed']);

            $this->audit->actionLog('data_corrections', 'approve_and_apply', 'success', [
                'correction_id' => $correction->id,
                'employee_id'   => $employee->id,
                'field'         => $correction->field_name,
                'old_values'    => [$correction->field_name => $oldValue],
                'new_values'    => [$correction->field_name => $correction->requested_value],
            ]);
        } else {
            $this->audit->actionLog('data_corrections', 'approve', 'success', [
                'correction_id' => $correction->id,
                'note'          => 'Approved but could not auto-apply — field not fillable or employee not found',
            ]);
        }

        return redirect()->route('corrections.admin')
            ->with('success', 'Correction request approved and applied.');
    }

    /**
     * Admin: reject a correction request.
     */
    public function reject(Request $request, DataCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            return redirect()->route('corrections.admin')
                ->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'review_notes' => 'required|string|max:500',
        ]);

        $correction->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        $this->audit->actionLog('data_corrections', 'reject', 'success', [
            'correction_id' => $correction->id,
            'employee_id'   => $correction->employee_id,
            'reason'        => $validated['review_notes'],
        ]);

        return redirect()->route('corrections.admin')
            ->with('success', 'Correction request rejected.');
    }
}
