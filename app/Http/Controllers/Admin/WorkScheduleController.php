<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkScheduleTemplate;
use App\Models\EmployeeWorkSchedule;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $templates = WorkScheduleTemplate::where('is_active', true)->orderBy('name')->get();
        $editTemplate = null;

        if ($request->has('edit')) {
            $editTemplate = WorkScheduleTemplate::find($request->input('edit'));
        }

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        $assignments = EmployeeWorkSchedule::with(['employee', 'template'])
            ->whereNull('effective_to')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.work-schedules.index', compact('templates', 'editTemplate', 'employees', 'assignments'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration_minutes' => 'nullable|integer|min:0|max:120',
            'break_start_time' => 'nullable|date_format:H:i',
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'integer|min:0|max:6',
        ]);

        $validated['config_level'] = 'system';
        $validated['template_type'] = 'system';
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        $template = WorkScheduleTemplate::create($validated);

        $this->audit->actionLog('work_schedules', 'create_template', 'success', [
            'template_id' => $template->id,
        ]);

        return redirect()->route('admin.work-schedules.index')
            ->with('success', 'Schedule template created.');
    }

    public function updateTemplate(Request $request, WorkScheduleTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration_minutes' => 'nullable|integer|min:0|max:120',
            'break_start_time' => 'nullable|date_format:H:i',
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'integer|min:0|max:6',
        ]);

        $template->update($validated);

        $this->audit->actionLog('work_schedules', 'update_template', 'success', [
            'template_id' => $template->id,
        ]);

        return redirect()->route('admin.work-schedules.index')
            ->with('success', 'Schedule template updated.');
    }

    public function destroyTemplate(WorkScheduleTemplate $template)
    {
        $template->update(['is_active' => false]);

        $this->audit->actionLog('work_schedules', 'delete_template', 'success', [
            'template_id' => $template->id,
        ]);

        return redirect()->route('admin.work-schedules.index')
            ->with('success', 'Schedule template deactivated.');
    }

    public function assignTemplate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'schedule_template_id' => 'required|exists:work_schedule_templates,id',
            'effective_from' => 'required|date',
            'priority' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['assigned_by'] = auth()->id();

        // Close existing active assignment for this employee
        EmployeeWorkSchedule::where('employee_id', $validated['employee_id'])
            ->whereNull('effective_to')
            ->update(['effective_to' => now()->toDateString()]);

        EmployeeWorkSchedule::create($validated);

        $this->audit->actionLog('work_schedules', 'assign', 'success', [
            'employee_id' => $validated['employee_id'],
            'template_id' => $validated['schedule_template_id'],
        ]);

        return redirect()->route('admin.work-schedules.index')
            ->with('success', 'Schedule assigned to employee.');
    }
}
