<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment;
use App\Models\RecruitmentFile;
use App\Models\RecruitmentTemplate;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    protected const STATUSES = ['new', 'shortlist', 'interviewed', 'hired', 'rejected'];

    protected const STATUS_LABELS = [
        'new' => 'Pending',
        'shortlist' => 'For Final Interview',
        'interviewed' => 'Interviewed',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
    ];

    protected const STATUS_COLORS = [
        'new' => 'bg-blue-100 text-blue-700',
        'shortlist' => 'bg-amber-100 text-amber-700',
        'interviewed' => 'bg-indigo-100 text-indigo-700',
        'hired' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];

    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Pipeline listing with stats, search, filter, inline status update.
     */
    public function index(Request $request)
    {
        $canWrite = $this->permissions->userCan('hr_core', 'recruitment', 'write');
        $canManage = $this->permissions->userCan('hr_core', 'recruitment', 'manage');

        // Stats
        $stats = [
            'total' => Recruitment::count(),
            'new' => Recruitment::where('status', 'new')->count(),
            'shortlist' => Recruitment::where('status', 'shortlist')->count(),
            'interviewed' => Recruitment::where('status', 'interviewed')->count(),
            'hired' => Recruitment::where('status', 'hired')->count(),
            'rejected' => Recruitment::where('status', 'rejected')->count(),
        ];

        $query = Recruitment::with('template');

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('full_name', 'ilike', "%{$escaped}%")
                  ->orWhere('email', 'ilike', "%{$escaped}%")
                  ->orWhere('position_applied', 'ilike', "%{$escaped}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $applicants = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('recruitment.index', compact(
            'applicants', 'stats', 'canWrite', 'canManage'
        ));
    }

    /**
     * Create new applicant form.
     */
    public function create()
    {
        $templates = RecruitmentTemplate::orderBy('name')->get();

        return view('recruitment.create', compact('templates'));
    }

    /**
     * Store new applicant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position_applied' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:recruitment_templates,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $applicant = Recruitment::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'position_applied' => $validated['position_applied'] ?? null,
                'template_id' => $validated['template_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'new',
            ]);

            $this->audit->actionLog('recruitment', 'create', 'success', [
                'recruitment_id' => $applicant->id,
                'full_name' => $applicant->full_name,
            ]);

            return redirect()->route('recruitment.show', $applicant)->with('success', 'Applicant added successfully.');
        } catch (\Exception $e) {
            Log::error('Recruitment create failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create applicant.');
        }
    }

    /**
     * View applicant detail with files, edit form, transition form.
     */
    public function show(Recruitment $recruitment)
    {
        $recruitment->load(['files', 'template']);

        $canWrite = $this->permissions->userCan('hr_core', 'recruitment', 'write');
        $canManage = $this->permissions->userCan('hr_core', 'recruitment', 'manage');

        $isConverted = !empty($recruitment->converted_employee_id) && $recruitment->converted_employee_id > 0;

        // For transition form
        $departments = $canManage ? Department::orderBy('name')->get(['id', 'name']) : collect();
        $positions = $canManage ? Position::orderBy('title')->get(['id', 'title']) : collect();

        // Check required files from template
        $missingFiles = collect();
        if ($recruitment->template_id) {
            $requiredLabels = DB::table('recruitment_template_files')
                ->where('template_id', $recruitment->template_id)
                ->where('is_required', true)
                ->pluck('label');

            $uploadedLabels = $recruitment->files->pluck('label')->map(fn ($l) => strtolower(trim($l)));

            $missingFiles = $requiredLabels->filter(function ($label) use ($uploadedLabels) {
                return !$uploadedLabels->contains(strtolower(trim($label)));
            });
        }

        $templates = RecruitmentTemplate::orderBy('name')->get();

        return view('recruitment.show', compact(
            'recruitment', 'canWrite', 'canManage', 'isConverted',
            'departments', 'positions', 'missingFiles', 'templates'
        ));
    }

    /**
     * Update applicant profile.
     */
    public function update(Request $request, Recruitment $recruitment)
    {
        $isConverted = !empty($recruitment->converted_employee_id) && $recruitment->converted_employee_id > 0;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position_applied' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:recruitment_templates,id',
            'status' => 'required|in:' . implode(',', self::STATUSES),
            'notes' => 'nullable|string|max:2000',
        ]);

        // Business rules
        if ($isConverted) {
            return back()->with('error', 'Cannot edit a converted applicant.');
        }

        if ($validated['status'] === 'hired' && !$isConverted) {
            return back()->with('error', 'Cannot set status to Hired without completing employee transition.');
        }

        try {
            $recruitment->update($validated);

            $this->audit->actionLog('recruitment', 'update', 'success', [
                'recruitment_id' => $recruitment->id,
                'full_name' => $recruitment->full_name,
            ]);

            return redirect()->route('recruitment.show', $recruitment)->with('success', 'Applicant updated successfully.');
        } catch (\Exception $e) {
            Log::error('Recruitment update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update applicant.');
        }
    }

    /**
     * Upload a file for an applicant.
     */
    public function uploadFile(Request $request, Recruitment $recruitment)
    {
        $request->validate([
            'label' => 'required|string|max:191',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,xls,xlsx,csv,txt',
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('recruitment', $filename, 'public');

            RecruitmentFile::create([
                'recruitment_id' => $recruitment->id,
                'label' => $request->input('label'),
                'file_path' => $path,
                'uploaded_by' => Auth::id(),
            ]);

            $this->audit->actionLog('recruitment', 'upload_file', 'success', [
                'recruitment_id' => $recruitment->id,
                'label' => $request->input('label'),
            ]);

            return redirect()->route('recruitment.show', $recruitment)->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Recruitment file upload failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to upload file.');
        }
    }

    /**
     * Inline status update.
     */
    public function updateStatus(Request $request, Recruitment $recruitment)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $isConverted = !empty($recruitment->converted_employee_id) && $recruitment->converted_employee_id > 0;

        if ($isConverted) {
            return back()->with('error', 'Cannot change status of a converted applicant.');
        }

        if ($validated['status'] === 'hired' && !$isConverted) {
            return back()->with('error', 'Cannot set status to Hired without completing employee transition.');
        }

        $oldStatus = $recruitment->status;
        $recruitment->update(['status' => $validated['status']]);

        $this->audit->actionLog('recruitment', 'status_update', 'success', [
            'recruitment_id' => $recruitment->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);

        return back()->with('success', 'Status updated to ' . (self::STATUS_LABELS[$validated['status']] ?? $validated['status']) . '.');
    }

    /**
     * Transition applicant to employee (manage-level only).
     */
    public function transition(Request $request, Recruitment $recruitment)
    {
        $isConverted = !empty($recruitment->converted_employee_id) && $recruitment->converted_employee_id > 0;
        if ($isConverted) {
            return back()->with('error', 'This applicant has already been transitioned to an employee.');
        }

        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'emp_email' => 'required|email|max:255|unique:employees,email',
            'emp_phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::create([
                'employee_code' => $validated['employee_code'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['emp_email'],
                'phone' => $validated['emp_phone'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'position_id' => $validated['position_id'] ?? null,
                'hire_date' => now()->toDateString(),
                'employment_type' => 'regular',
                'status' => 'active',
                'salary' => 0,
            ]);

            $recruitment->update([
                'status' => 'hired',
                'converted_employee_id' => $employee->id,
            ]);

            DB::commit();

            $this->audit->actionLog('recruitment', 'transition_to_employee', 'success', [
                'recruitment_id' => $recruitment->id,
                'employee_id' => $employee->id,
                'full_name' => $recruitment->full_name,
            ]);

            return redirect()->route('recruitment.show', $recruitment)->with('success', 'Applicant successfully transitioned to employee.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recruitment transition failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to transition applicant: ' . $e->getMessage());
        }
    }

    /**
     * CSV export.
     */
    public function exportCsv()
    {
        $applicants = Recruitment::orderByDesc('created_at')->get();

        $this->audit->actionLog('recruitment', 'export', 'success', [
            'count' => $applicants->count(),
        ]);

        return response()->streamDownload(function () use ($applicants) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Full Name', 'Email', 'Phone', 'Position Applied', 'Status', 'Created At']);
            foreach ($applicants as $app) {
                fputcsv($handle, [
                    $app->id,
                    $app->full_name,
                    $app->email,
                    $app->phone,
                    $app->position_applied,
                    self::STATUS_LABELS[$app->status] ?? $app->status,
                    $app->created_at?->format('M d, Y h:i A'),
                ]);
            }
            fclose($handle);
        }, 'recruitment_export_' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
