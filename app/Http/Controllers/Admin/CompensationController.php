<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompensationTemplate;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class CompensationController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'allowances');
        $validTabs = ['allowances', 'contributions', 'taxes', 'deductions'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'allowances';
        }

        $categoryMap = [
            'allowances' => 'allowance',
            'contributions' => 'contribution',
            'taxes' => 'tax',
            'deductions' => 'deduction',
        ];

        $templates = CompensationTemplate::where('category', $categoryMap[$tab])
            ->orderBy('name')
            ->get();

        $stats = [];
        foreach ($categoryMap as $tabKey => $category) {
            $stats[$tabKey] = CompensationTemplate::where('category', $category)
                ->where('is_active', true)
                ->count();
        }

        return view('admin.compensation.index', compact('templates', 'tab', 'stats', 'categoryMap'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:allowance,contribution,tax,deduction',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'amount_type' => 'required|in:static,percentage',
            'static_amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'is_modifiable' => 'boolean',
            'effectivity_until' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = true;
        $validated['is_modifiable'] = $request->boolean('is_modifiable');
        $validated['created_by'] = auth()->id();

        $template = CompensationTemplate::create($validated);

        $this->audit->actionLog('compensation', 'create', 'success', [
            'template_id' => $template->id,
            'name' => $template->name,
        ]);

        return redirect()->route('admin.compensation.index', ['tab' => $this->tabForCategory($validated['category'])])
            ->with('success', 'Compensation template created.');
    }

    public function update(Request $request, CompensationTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'amount_type' => 'required|in:static,percentage',
            'static_amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'is_modifiable' => 'boolean',
            'effectivity_until' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_modifiable'] = $request->boolean('is_modifiable');
        $validated['updated_by'] = auth()->id();

        $template->update($validated);

        $this->audit->actionLog('compensation', 'update', 'success', [
            'template_id' => $template->id,
        ]);

        return redirect()->route('admin.compensation.index', ['tab' => $this->tabForCategory($template->category)])
            ->with('success', 'Template updated.');
    }

    public function destroy(CompensationTemplate $template)
    {
        $template->update(['is_active' => false, 'updated_by' => auth()->id()]);

        $this->audit->actionLog('compensation', 'delete', 'success', [
            'template_id' => $template->id,
        ]);

        return redirect()->route('admin.compensation.index', ['tab' => $this->tabForCategory($template->category)])
            ->with('success', 'Template deactivated.');
    }

    private function tabForCategory(string $category): string
    {
        return match ($category) {
            'allowance' => 'allowances',
            'contribution' => 'contributions',
            'tax' => 'taxes',
            'deduction' => 'deductions',
            default => 'allowances',
        };
    }
}
