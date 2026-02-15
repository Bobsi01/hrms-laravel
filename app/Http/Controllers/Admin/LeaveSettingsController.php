<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveFilingPolicy;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveSettingsController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Leave defaults — global & department overrides.
     */
    public function defaults(Request $request)
    {
        $leaveTypes = $this->getLeaveTypes();
        $systemDefaults = config('hrms.leave_entitlements', []);

        // Global overrides from leave_entitlements table
        $globalOverrides = DB::table('leave_entitlements')
            ->where('scope_type', 'global')
            ->whereNull('scope_id')
            ->pluck('days_entitled', 'leave_type')
            ->toArray();

        // Department overrides
        $departments = DB::table('departments')->orderBy('name')->get();
        $deptOverrides = DB::table('leave_entitlements')
            ->where('scope_type', 'department')
            ->get()
            ->groupBy('scope_id');

        return view('admin.leave-settings.defaults', compact(
            'leaveTypes', 'systemDefaults', 'globalOverrides', 'departments', 'deptOverrides'
        ));
    }

    public function saveGlobals(Request $request)
    {
        $leaveTypes = $this->getLeaveTypes();

        foreach ($leaveTypes as $type) {
            $val = $request->input("leave_{$type}");
            if ($val !== null && $val !== '') {
                DB::table('leave_entitlements')->upsert(
                    ['leave_type' => $type, 'scope_type' => 'global', 'scope_id' => null, 'days_entitled' => (float) $val],
                    ['leave_type', 'scope_type'],
                    ['days_entitled']
                );
            } else {
                DB::table('leave_entitlements')
                    ->where('leave_type', $type)
                    ->where('scope_type', 'global')
                    ->whereNull('scope_id')
                    ->delete();
            }
        }

        $this->audit->actionLog('leave_settings', 'update_globals', 'success');

        return redirect()->route('admin.leave-defaults.index')
            ->with('success', 'Global leave defaults updated.');
    }

    public function saveDepartment(Request $request)
    {
        $deptId = $request->input('department_id');
        $leaveTypes = $this->getLeaveTypes();

        foreach ($leaveTypes as $type) {
            $val = $request->input("leave_{$type}");
            if ($val !== null && $val !== '') {
                DB::table('leave_entitlements')->upsert(
                    ['leave_type' => $type, 'scope_type' => 'department', 'scope_id' => $deptId, 'days_entitled' => (float) $val],
                    ['leave_type', 'scope_type', 'scope_id'],
                    ['days_entitled']
                );
            } else {
                DB::table('leave_entitlements')
                    ->where('leave_type', $type)
                    ->where('scope_type', 'department')
                    ->where('scope_id', $deptId)
                    ->delete();
            }
        }

        $this->audit->actionLog('leave_settings', 'update_department', 'success', [
            'department_id' => $deptId,
        ]);

        return redirect()->route('admin.leave-defaults.index')
            ->with('success', 'Department leave overrides updated.');
    }

    /**
     * Leave entitlements — balances + policies.
     */
    public function entitlements(Request $request)
    {
        $tab = $request->input('tab', 'balances');
        $leaveTypes = $this->getLeaveTypes();

        if ($tab === 'policies') {
            $policies = LeaveFilingPolicy::orderBy('leave_type')->get()->keyBy('leave_type');
            return view('admin.leave-settings.entitlements', compact('tab', 'leaveTypes', 'policies'));
        }

        // Balances tab
        $search = $request->input('search');
        $query = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
            ->where('employees.status', 'active')
            ->select('employees.*', 'departments.name as department_name', 'branches.name as branch_name');

        if ($search) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('employees.first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('employees.last_name', 'ilike', "%{$escaped}%")
                  ->orWhere('employees.email', 'ilike', "%{$escaped}%")
                  ->orWhere('employees.employee_code', 'ilike', "%{$escaped}%");
            });
        }

        $employees = $query->orderBy('employees.last_name')->paginate(15);

        // Calculate balances per employee
        $systemDefaults = config('hrms.leave_entitlements', []);
        $globalOverrides = DB::table('leave_entitlements')
            ->where('scope_type', 'global')
            ->whereNull('scope_id')
            ->pluck('days_entitled', 'leave_type')
            ->toArray();

        $currentYear = now()->year;
        $balances = [];
        foreach ($employees as $emp) {
            $deptOverrides = DB::table('leave_entitlements')
                ->where('scope_type', 'department')
                ->where('scope_id', $emp->department_id)
                ->pluck('days_entitled', 'leave_type')
                ->toArray();

            $used = DB::table('leave_requests')
                ->where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->select('leave_type', DB::raw('SUM(days_requested) as total'))
                ->groupBy('leave_type')
                ->pluck('total', 'leave_type')
                ->toArray();

            $empBalances = [];
            foreach ($leaveTypes as $type) {
                $entitled = $deptOverrides[$type] ?? $globalOverrides[$type] ?? ($systemDefaults[$type] ?? 0);
                $usedDays = $used[$type] ?? 0;
                $empBalances[$type] = [
                    'entitled' => $entitled,
                    'used' => $usedDays,
                    'remaining' => $entitled - $usedDays,
                ];
            }
            $balances[$emp->id] = $empBalances;
        }

        return view('admin.leave-settings.entitlements', compact('tab', 'leaveTypes', 'employees', 'balances', 'search'));
    }

    public function updatePolicy(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|string',
            'require_advance_notice' => 'boolean',
            'advance_notice_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['require_advance_notice'] = $request->boolean('require_advance_notice');
        $validated['updated_by'] = auth()->id();

        LeaveFilingPolicy::updateOrCreate(
            ['leave_type' => $validated['leave_type']],
            $validated
        );

        $this->audit->actionLog('leave_settings', 'update_policy', 'success', [
            'leave_type' => $validated['leave_type'],
        ]);

        return redirect()->route('admin.leave-entitlements.index', ['tab' => 'policies'])
            ->with('success', 'Leave policy updated.');
    }

    private function getLeaveTypes(): array
    {
        $defaults = array_keys(config('hrms.leave_entitlements', []));
        // Also fetch any custom types from the DB
        $custom = DB::table('leave_type_labels')->pluck('leave_type')->toArray();
        return array_unique(array_merge($defaults, $custom));
    }
}
