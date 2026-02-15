<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentAssignment;
use App\Models\Employee;
use App\Models\Department;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Self-service: show documents assigned to current employee / their department / global.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $query = Document::query()
            ->select('documents.*')
            ->leftJoin('document_assignments', 'documents.id', '=', 'document_assignments.document_id')
            ->where(function ($q) use ($employee) {
                // Global documents (no assignments)
                $q->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('document_assignments as da')
                        ->whereColumn('da.document_id', 'documents.id');
                });

                if ($employee) {
                    // Documents assigned to this employee
                    $q->orWhere('document_assignments.employee_id', $employee->id);

                    // Documents assigned to this employee's department
                    if ($employee->department_id) {
                        $q->orWhere('document_assignments.department_id', $employee->department_id);
                    }
                }
            })
            ->whereNull('documents.deleted_at')
            ->distinct();

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('documents.title', 'ilike', "%{$escaped}%")
                  ->orWhere('documents.doc_type', 'ilike', "%{$escaped}%");
            });
        }

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('documents.doc_type', $type);
        }

        $documents = $query->orderByDesc('documents.created_at')->paginate(20)->withQueryString();

        return view('documents.index', compact('documents'));
    }

    /**
     * Admin: list all documents with management options.
     */
    public function admin(Request $request)
    {
        $canWrite = $this->permissions->userCan('documents', 'documents', 'write');
        $canManage = $this->permissions->userCan('documents', 'documents', 'manage');

        $query = Document::with(['creator', 'assignments.employee', 'assignments.department'])
            ->whereNull('deleted_at');

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'ilike', "%{$escaped}%")
                  ->orWhere('doc_type', 'ilike', "%{$escaped}%");
            });
        }

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('doc_type', $type);
        }

        $stats = [
            'total' => Document::whereNull('deleted_at')->count(),
            'memo' => Document::whereNull('deleted_at')->where('doc_type', 'memo')->count(),
            'policy' => Document::whereNull('deleted_at')->where('doc_type', 'policy')->count(),
            'contract' => Document::whereNull('deleted_at')->where('doc_type', 'contract')->count(),
            'other' => Document::whereNull('deleted_at')->where('doc_type', 'other')->count(),
        ];

        $documents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('documents.admin', compact('documents', 'stats', 'canWrite', 'canManage'));
    }

    /**
     * Show form to create a new document.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('documents.create', compact('employees', 'departments'));
    }

    /**
     * Store a new document with file upload + assignments.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'doc_type' => 'required|in:memo,contract,policy,other',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip',
            'assign_type' => 'required|in:global,employees,departments',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('documents', $filename, 'public');

            $document = Document::create([
                'title' => $validated['title'],
                'doc_type' => $validated['doc_type'],
                'file_path' => $path,
                'created_by' => Auth::id(),
            ]);

            // Create assignments
            if ($validated['assign_type'] === 'employees' && !empty($validated['employee_ids'])) {
                foreach ($validated['employee_ids'] as $empId) {
                    DocumentAssignment::create([
                        'document_id' => $document->id,
                        'employee_id' => $empId,
                    ]);
                }
            } elseif ($validated['assign_type'] === 'departments' && !empty($validated['department_ids'])) {
                foreach ($validated['department_ids'] as $deptId) {
                    DocumentAssignment::create([
                        'document_id' => $document->id,
                        'department_id' => $deptId,
                    ]);
                }
            }
            // 'global' → no assignments needed

            $this->audit->actionLog('documents', 'create', 'success', [
                'document_id' => $document->id,
                'title' => $document->title,
            ]);

            return redirect()->route('documents.admin')->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Document upload failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to upload document. Please try again.');
        }
    }

    /**
     * Show a single document.
     */
    public function show(Document $document)
    {
        $document->load(['creator', 'assignments.employee', 'assignments.department']);

        return view('documents.show', compact('document'));
    }

    /**
     * Show edit form.
     */
    public function edit(Document $document)
    {
        $document->load('assignments');

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        $assignedEmployeeIds = $document->assignments->whereNotNull('employee_id')->pluck('employee_id')->toArray();
        $assignedDepartmentIds = $document->assignments->whereNotNull('department_id')->pluck('department_id')->toArray();

        $assignType = 'global';
        if (!empty($assignedEmployeeIds)) {
            $assignType = 'employees';
        } elseif (!empty($assignedDepartmentIds)) {
            $assignType = 'departments';
        }

        return view('documents.edit', compact('document', 'employees', 'departments', 'assignedEmployeeIds', 'assignedDepartmentIds', 'assignType'));
    }

    /**
     * Update document.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'doc_type' => 'required|in:memo,contract,policy,other',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip',
            'assign_type' => 'required|in:global,employees,departments',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        try {
            $document->title = $validated['title'];
            $document->doc_type = $validated['doc_type'];

            if ($request->hasFile('file')) {
                // Delete old file if it exists
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $file = $request->file('file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $document->file_path = $file->storeAs('documents', $filename, 'public');
            }

            $document->save();

            // Rebuild assignments
            DocumentAssignment::where('document_id', $document->id)->delete();

            if ($validated['assign_type'] === 'employees' && !empty($validated['employee_ids'])) {
                foreach ($validated['employee_ids'] as $empId) {
                    DocumentAssignment::create([
                        'document_id' => $document->id,
                        'employee_id' => $empId,
                    ]);
                }
            } elseif ($validated['assign_type'] === 'departments' && !empty($validated['department_ids'])) {
                foreach ($validated['department_ids'] as $deptId) {
                    DocumentAssignment::create([
                        'document_id' => $document->id,
                        'department_id' => $deptId,
                    ]);
                }
            }

            $this->audit->actionLog('documents', 'update', 'success', [
                'document_id' => $document->id,
                'title' => $document->title,
            ]);

            return redirect()->route('documents.admin')->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update document. Please try again.');
        }
    }

    /**
     * Soft-delete (archive) a document.
     */
    public function destroy(Document $document)
    {
        try {
            $employee = Employee::where('user_id', Auth::id())->first();
            $document->update([
                'deleted_at' => now(),
                'deleted_by' => $employee?->id,
            ]);

            $this->audit->actionLog('documents', 'delete', 'success', [
                'document_id' => $document->id,
                'title' => $document->title,
            ]);

            return redirect()->route('documents.admin')->with('success', 'Document archived successfully.');
        } catch (\Exception $e) {
            Log::error('Document delete failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to archive document.');
        }
    }

    /**
     * Download / serve a document file.
     */
    public function download(Document $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        return response()->download($filePath, basename($document->file_path));
    }

    /**
     * CSV export of documents visible to the current user.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $documents = Document::query()
            ->select('documents.*')
            ->leftJoin('document_assignments', 'documents.id', '=', 'document_assignments.document_id')
            ->where(function ($q) use ($employee) {
                $q->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('document_assignments as da')
                        ->whereColumn('da.document_id', 'documents.id');
                });
                if ($employee) {
                    $q->orWhere('document_assignments.employee_id', $employee->id);
                    if ($employee->department_id) {
                        $q->orWhere('document_assignments.department_id', $employee->department_id);
                    }
                }
            })
            ->whereNull('documents.deleted_at')
            ->distinct()
            ->orderByDesc('documents.created_at')
            ->get();

        $this->audit->actionLog('documents', 'export', 'success', [
            'count' => $documents->count(),
        ]);

        return response()->streamDownload(function () use ($documents) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Type', 'File', 'Created At']);
            foreach ($documents as $doc) {
                fputcsv($handle, [
                    $doc->id,
                    $doc->title,
                    $doc->doc_type,
                    $doc->file_path,
                    $doc->created_at?->format('M d, Y h:i A'),
                ]);
            }
            fclose($handle);
        }, 'documents_export_' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
