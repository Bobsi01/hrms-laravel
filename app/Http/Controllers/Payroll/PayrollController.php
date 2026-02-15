<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\CutoffPeriod;
use App\Models\PayrollBatch;
use App\Models\PayrollComplaint;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Branch;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Payroll runs dashboard with stats and filtering.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $canWrite = $this->permissions->userHasAccess($userId, 'payroll', 'payroll_runs', 'write');
        $canManage = $this->permissions->userHasAccess($userId, 'payroll', 'payroll_runs', 'manage');

        $query = PayrollRun::with(['generatedBy'])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('status', 'ilike', "%{$escaped}%")
                  ->orWhere('notes', 'ilike', "%{$escaped}%")
                  ->orWhere('run_mode', 'ilike', "%{$escaped}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $stats = [
            'total' => PayrollRun::count(),
            'draft' => PayrollRun::where('status', 'draft')->count(),
            'processing' => PayrollRun::whereIn('status', ['for_review', 'submitted'])->count(),
            'awaiting_approval' => PayrollRun::where('status', 'for_review')->count(),
            'released' => PayrollRun::where('status', 'released')->count(),
        ];

        // Open complaints count
        $openComplaints = PayrollComplaint::whereIn('status', ['pending', 'in_review'])->count();

        $payrollRuns = $query->paginate(20);

        return view('payroll.index', compact('payrollRuns', 'stats', 'canWrite', 'canManage', 'openComplaints'));
    }

    /**
     * View a single payroll run with batches and payslips.
     */
    public function show(PayrollRun $payrollRun)
    {
        $payrollRun->load(['batches.branch', 'generatedBy']);

        $payslips = Payslip::where('payroll_run_id', $payrollRun->id)
            ->with('employee')
            ->orderBy('employee_id')
            ->get();

        $complaints = PayrollComplaint::where('payroll_run_id', $payrollRun->id)
            ->with('employee')
            ->orderByDesc('submitted_at')
            ->get();

        $batchSummary = [
            'total' => $payrollRun->batches->count(),
            'approved' => $payrollRun->batches->where('status', 'approved')->count(),
            'pending' => $payrollRun->batches->whereIn('status', ['pending', 'draft', 'submitted'])->count(),
            'rejected' => $payrollRun->batches->where('status', 'rejected')->count(),
        ];

        $payslipSummary = [
            'count' => $payslips->count(),
            'total_gross' => $payslips->sum('gross_pay'),
            'total_deductions' => $payslips->sum('total_deductions'),
            'total_net' => $payslips->sum('net_pay'),
        ];

        return view('payroll.show', compact('payrollRun', 'payslips', 'complaints', 'batchSummary', 'payslipSummary'));
    }

    /**
     * Show create payroll run form.
     */
    public function create()
    {
        $cutoffPeriods = CutoffPeriod::where('is_locked', false)
            ->orderByDesc('start_date')
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('payroll.create', compact('cutoffPeriods', 'branches'));
    }

    /**
     * Store a new payroll run.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'run_mode' => 'required|in:automatic,manual',
            'notes' => 'nullable|string|max:500',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id',
        ]);

        try {
            $payrollRun = PayrollRun::create([
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'run_mode' => $validated['run_mode'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'initiated_by' => auth()->id(),
                'generated_by' => auth()->id(),
            ]);

            // Create batches per selected branch
            foreach ($validated['branches'] as $branchId) {
                PayrollBatch::create([
                    'payroll_run_id' => $payrollRun->id,
                    'branch_id' => $branchId,
                    'status' => 'draft',
                ]);
            }

            $this->audit->actionLog('payroll', 'create_run', 'success', [
                'payroll_run_id' => $payrollRun->id,
                'period' => $validated['period_start'] . ' to ' . $validated['period_end'],
                'branches' => count($validated['branches']),
            ]);

            return redirect()->route('payroll.show', $payrollRun)->with('success', 'Payroll run created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create payroll run', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create payroll run.');
        }
    }

    /**
     * Complaints management page.
     */
    public function complaints(Request $request)
    {
        $statusFilter = $request->input('status', 'open');
        $query = PayrollComplaint::with(['employee', 'payrollRun', 'payslip']);

        switch ($statusFilter) {
            case 'open':
                $query->whereIn('status', ['pending', 'in_review']);
                break;
            case 'pending':
            case 'in_review':
            case 'resolved':
            case 'confirmed':
            case 'rejected':
                $query->where('status', $statusFilter);
                break;
            // 'all' — no filter
        }

        $complaints = $query->orderByDesc('submitted_at')->paginate(20);

        $stats = [
            'pending' => PayrollComplaint::where('status', 'pending')->count(),
            'in_review' => PayrollComplaint::where('status', 'in_review')->count(),
            'resolved' => PayrollComplaint::where('status', 'resolved')->count(),
            'total' => PayrollComplaint::count(),
        ];

        return view('payroll.complaints', compact('complaints', 'stats', 'statusFilter'));
    }

    /**
     * Update complaint status.
     */
    public function updateComplaint(Request $request, PayrollComplaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_review,resolved,rejected,confirmed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $complaint->status;
        $complaint->status = $validated['status'];
        $complaint->resolution_notes = $validated['resolution_notes'] ?? $complaint->resolution_notes;

        if (in_array($validated['status'], ['resolved', 'rejected'])) {
            $complaint->resolved_at = now();
        }
        if ($validated['status'] === 'in_review') {
            $complaint->assigned_to = auth()->id();
        }

        $complaint->save();

        $this->audit->actionLog('payroll', 'complaint_update', 'success', [
            'complaint_id' => $complaint->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);

        return back()->with('success', 'Complaint status updated to ' . ucfirst($validated['status']) . '.');
    }

    /**
     * My payslips (self-service) with complaints tab.
     */
    public function myPayslips(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('payroll.my-payslips', [
                'payslips' => collect(),
                'complaints' => collect(),
                'activeTab' => 'payslips',
            ]);
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->where(function ($q) {
                $q->whereNotNull('released_at')->orWhere('status', 'released');
            })
            ->orderByDesc('period_start')
            ->paginate(20);

        $complaints = PayrollComplaint::where('employee_id', $employee->id)
            ->with('payrollRun')
            ->orderByDesc('submitted_at')
            ->get();

        $activeTab = $request->input('tab', 'payslips');

        return view('payroll.my-payslips', compact('payslips', 'complaints', 'activeTab'));
    }

    /**
     * File a complaint from my payslips.
     */
    public function fileComplaint(Request $request)
    {
        $validated = $request->validate([
            'payslip_id' => 'required|exists:payslips,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'issue_type' => 'required|string|max:100',
        ]);

        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Your account is not linked to an employee profile.');
        }

        $payslip = Payslip::where('id', $validated['payslip_id'])
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payslip) {
            return back()->with('error', 'You cannot file a complaint for this payslip.');
        }

        // Check for existing open complaint
        $existing = PayrollComplaint::where('payslip_id', $payslip->id)
            ->whereNotIn('status', ['resolved', 'rejected', 'confirmed'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have an open complaint for this payslip.');
        }

        $complaint = PayrollComplaint::create([
            'payroll_run_id' => $payslip->payroll_run_id,
            'payslip_id' => $payslip->id,
            'employee_id' => $employee->id,
            'issue_type' => $validated['issue_type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => 'pending',
            'submitted_at' => now(),
            'ticket_code' => 'PC-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        ]);

        $this->audit->actionLog('payroll', 'complaint_create', 'success', [
            'complaint_id' => $complaint->id,
            'payslip_id' => $payslip->id,
        ]);

        return back()->with('success', 'Complaint submitted successfully. Ticket: ' . $complaint->ticket_code);
    }

    /**
     * View individual payslip detail.
     */
    public function payslipShow(Payslip $payslip)
    {
        $user = auth()->user();
        $employee = $user->employee;
        $isAdmin = $this->permissions->userHasAccess($user->id, 'payroll', 'payroll_runs', 'read');

        // Verify ownership or admin access
        if (!$isAdmin && (!$employee || $payslip->employee_id !== $employee->id)) {
            return redirect()->route('payroll.my-payslips')->with('error', 'You do not have access to this payslip.');
        }

        $payslip->load(['employee', 'payrollRun', 'items']);

        return view('payroll.payslip-show', compact('payslip', 'isAdmin'));
    }
}
