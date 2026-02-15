<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index()
    {
        $approvers = DB::table('payroll_approvers')
            ->leftJoin('users', 'payroll_approvers.user_id', '=', 'users.id')
            ->select('payroll_approvers.*', 'users.full_name', 'users.email')
            ->orderBy('payroll_approvers.step_order')
            ->get();

        $users = User::where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'email']);

        $stats = [
            'active' => $approvers->where('active', true)->count(),
            'total' => $approvers->count(),
        ];

        return view('admin.approval-workflow.index', compact('approvers', 'users', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'step_order' => 'required|integer|min:1',
            'scope' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $active = $request->boolean('active', true);

        // Check if user already exists with same scope
        $existing = DB::table('payroll_approvers')
            ->where('user_id', $validated['user_id'])
            ->where('scope', $validated['scope'] ?? '')
            ->first();

        if ($existing) {
            DB::table('payroll_approvers')
                ->where('id', $existing->id)
                ->update([
                    'step_order' => $validated['step_order'],
                    'active' => $active,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('payroll_approvers')->insert([
                'user_id' => $validated['user_id'],
                'step_order' => $validated['step_order'],
                'scope' => $validated['scope'] ?? '',
                'active' => $active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->audit->actionLog('approval_workflow', 'add_approver', 'success', [
            'user_id' => $validated['user_id'],
        ]);

        return redirect()->route('admin.approval-workflow.index')
            ->with('success', 'Approver saved.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'step_order' => 'required|integer|min:1',
            'scope' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        DB::table('payroll_approvers')
            ->where('id', $id)
            ->update([
                'step_order' => $validated['step_order'],
                'scope' => $validated['scope'] ?? '',
                'active' => $request->boolean('active', true),
                'updated_at' => now(),
            ]);

        $this->audit->actionLog('approval_workflow', 'update_approver', 'success', [
            'approver_id' => $id,
        ]);

        return redirect()->route('admin.approval-workflow.index')
            ->with('success', 'Approver updated.');
    }

    public function destroy(int $id)
    {
        $approver = DB::table('payroll_approvers')->where('id', $id)->first();
        if (!$approver) {
            return back()->with('error', 'Approver not found.');
        }

        DB::table('payroll_approvers')->where('id', $id)->delete();

        $this->audit->actionLog('approval_workflow', 'delete_approver', 'success', [
            'approver_id' => $id,
            'user_id' => $approver->user_id,
        ]);

        return redirect()->route('admin.approval-workflow.index')
            ->with('success', 'Approver removed.');
    }

    public function reorder(Request $request)
    {
        $steps = $request->input('steps', []);

        DB::beginTransaction();
        try {
            foreach ($steps as $step) {
                DB::table('payroll_approvers')
                    ->where('id', $step['id'])
                    ->update(['step_order' => $step['step']]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reorder approvers.');
        }

        return redirect()->route('admin.approval-workflow.index')
            ->with('success', 'Approver order updated.');
    }
}
