<?php

namespace App\Http\Controllers\Memos;

use App\Http\Controllers\Controller;
use App\Models\Memo;
use App\Models\MemoRecipient;
use App\Models\Department;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * List memos visible to the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = Memo::with('issuer')
            ->where('status', 'published')
            ->orderByDesc('published_at');

        $memos = $query->paginate(20);

        return view('memos.index', compact('memos'));
    }

    /**
     * Show a single memo.
     */
    public function show(Memo $memo)
    {
        $memo->load(['recipients', 'attachments', 'issuer']);
        return view('memos.show', compact('memo'));
    }

    /**
     * Admin memo management.
     */
    public function admin(Request $request)
    {
        $query = Memo::with('issuer')->orderByDesc('published_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where('header', 'ilike', "%{$escaped}%");
        }

        $memos = $query->paginate(20);

        return view('memos.admin', compact('memos'));
    }

    /**
     * Create memo form.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('memos.create', compact('departments'));
    }

    /**
     * Store a new memo.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'header'          => 'required|string|max:255',
            'body'            => 'required|string',
            'status'          => 'required|in:draft,published',
            'audience_type'   => 'required|in:all,department,individual',
            'department_ids'  => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        $memoCode = 'MEMO-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $memo = Memo::create([
            'memo_code'          => $memoCode,
            'header'             => $validated['header'],
            'body'               => $validated['body'],
            'issued_by_user_id'  => $user->id,
            'issued_by_name'     => $user->full_name,
            'issued_by_position' => $user->employee?->position?->name ?? 'N/A',
            'status'             => $validated['status'],
            'allow_downloads'    => $request->boolean('allow_downloads'),
            'published_at'       => $validated['status'] === 'published' ? now() : null,
        ]);

        // Add recipients
        if ($validated['audience_type'] === 'all') {
            MemoRecipient::create([
                'memo_id'             => $memo->id,
                'audience_type'       => 'all',
                'audience_identifier' => 'all',
                'audience_label'      => 'All Employees',
            ]);
        } elseif ($validated['audience_type'] === 'department' && !empty($validated['department_ids'])) {
            foreach ($validated['department_ids'] as $deptId) {
                $dept = Department::find($deptId);
                MemoRecipient::create([
                    'memo_id'             => $memo->id,
                    'audience_type'       => 'department',
                    'audience_identifier' => (string) $deptId,
                    'audience_label'      => $dept?->name ?? 'Department #' . $deptId,
                ]);
            }
        }

        $this->audit->actionLog('memos', 'create_memo', 'success', [
            'memo_id' => $memo->id,
            'memo_code' => $memo->memo_code,
        ]);

        return redirect()->route('memos.admin')
            ->with('success', 'Memo created successfully.');
    }

    /**
     * Edit memo form.
     */
    public function edit(Memo $memo)
    {
        $memo->load('recipients');
        $departments = Department::orderBy('name')->get();
        return view('memos.edit', compact('memo', 'departments'));
    }

    /**
     * Update a memo.
     */
    public function update(Request $request, Memo $memo)
    {
        $validated = $request->validate([
            'header' => 'required|string|max:255',
            'body'   => 'required|string',
            'status' => 'required|in:draft,published',
        ]);

        $memo->update([
            'header' => $validated['header'],
            'body'   => $validated['body'],
            'status' => $validated['status'],
            'published_at' => ($validated['status'] === 'published' && !$memo->published_at) ? now() : $memo->published_at,
        ]);

        $this->audit->actionLog('memos', 'update_memo', 'success', [
            'memo_id' => $memo->id,
        ]);

        return redirect()->route('memos.admin')
            ->with('success', 'Memo updated successfully.');
    }

    /**
     * Delete a memo.
     */
    public function destroy(Memo $memo)
    {
        $code = $memo->memo_code;
        $id = $memo->id;

        $memo->recipients()->delete();
        $memo->attachments()->delete();
        $memo->delete();

        $this->audit->actionLog('memos', 'delete_memo', 'success', [
            'memo_id' => $id,
            'memo_code' => $code,
        ]);

        return redirect()->route('memos.admin')
            ->with('success', "Memo \"{$code}\" deleted.");
    }
}
