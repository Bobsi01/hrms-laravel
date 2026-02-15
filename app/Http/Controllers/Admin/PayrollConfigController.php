<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompensationTemplate;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollConfigController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * OT codes and their metadata for the overtime-rates tab.
     */
    private const OT_CODES = [
        'overtime_multiplier'              => ['label' => 'Regular Overtime',     'description' => 'Regular working day overtime (125%)',   'default' => 1.25],
        'rest_day_ot_multiplier'           => ['label' => 'Rest Day Overtime',    'description' => 'Rest day overtime rate',                'default' => 1.30],
        'regular_holiday_multiplier'       => ['label' => 'Regular Holiday',      'description' => 'Regular holiday premium',               'default' => 2.00],
        'regular_holiday_ot_multiplier'    => ['label' => 'Regular Holiday OT',   'description' => 'Regular holiday overtime',              'default' => 2.60],
        'special_holiday_multiplier'       => ['label' => 'Special Holiday',      'description' => 'Special non-working holiday',           'default' => 1.30],
        'special_holiday_ot_multiplier'    => ['label' => 'Special Holiday OT',   'description' => 'Special holiday overtime',              'default' => 1.69],
    ];

    /**
     * Valid tabs and their corresponding compensation_templates.category value.
     */
    private const CATEGORY_MAP = [
        'allowances'    => 'allowance',
        'contributions' => 'contribution',
        'taxes'         => 'tax',
        'deductions'    => 'deduction',
    ];

    private const VALID_TABS = ['overtime-rates', 'allowances', 'contributions', 'taxes', 'deductions'];

    /**
     * Display the unified payroll configuration page.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'overtime-rates');
        if (!in_array($tab, self::VALID_TABS)) {
            $tab = 'overtime-rates';
        }

        // Always load OT rates (shown in their own tab)
        $rates = $this->loadOvertimeRates();

        // Always load compensation stats (for tab badges)
        $compensationStats = [];
        foreach (self::CATEGORY_MAP as $tabKey => $category) {
            $compensationStats[$tabKey] = CompensationTemplate::where('category', $category)
                ->where('is_active', true)
                ->count();
        }

        // Load templates for the active compensation tab
        $templates = collect();
        if ($tab !== 'overtime-rates' && isset(self::CATEGORY_MAP[$tab])) {
            $templates = CompensationTemplate::where('category', self::CATEGORY_MAP[$tab])
                ->orderBy('name')
                ->get();
        }

        $pageTitle = 'Payroll Configuration';

        return view('admin.payroll-config.index', compact(
            'tab',
            'rates',
            'compensationStats',
            'templates',
            'pageTitle'
        ));
    }

    // ─── Overtime Rates ────────────────────────────────────────────────

    /**
     * Update overtime multiplier rates.
     */
    public function updateOvertimeRates(Request $request)
    {
        $changes = [];
        foreach (self::OT_CODES as $code => $meta) {
            $newVal = trim($request->input("rates.{$code}", ''));
            if ($newVal === '') continue;

            $newVal = (float) $newVal;

            // Range validation (0.00 – 99.00)
            if ($newVal < 0 || $newVal > 99) continue;

            $row = DB::table('payroll_formula_settings')->where('code', $code)->first();
            $oldVal = $row ? (float) $row->default_value : $meta['default'];

            if (abs($newVal - $oldVal) < 0.001) continue;

            if ($row) {
                DB::table('payroll_formula_settings')
                    ->where('code', $code)
                    ->update([
                        'default_value' => $newVal,
                        'updated_by'    => auth()->id(),
                        'updated_at'    => now(),
                    ]);
            } else {
                DB::table('payroll_formula_settings')->insert([
                    'code'          => $code,
                    'label'         => $meta['label'],
                    'default_value' => $newVal,
                    'created_by'    => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $changes[$code] = ['old' => $oldVal, 'new' => $newVal];
        }

        if (empty($changes)) {
            return redirect()->route('admin.payroll-config.index', ['tab' => 'overtime-rates'])
                ->with('error', 'No changes detected.');
        }

        $this->audit->actionLog('payroll_config', 'update_overtime_rates', 'success', [
            'changes' => $changes,
        ]);

        return redirect()->route('admin.payroll-config.index', ['tab' => 'overtime-rates'])
            ->with('success', count($changes) . ' overtime rate(s) updated.');
    }

    // ─── Compensation Templates ────────────────────────────────────────

    /**
     * Store a new compensation template.
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'category'         => 'required|in:allowance,contribution,tax,deduction',
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:50',
            'amount_type'      => 'required|in:static,percentage',
            'static_amount'    => 'nullable|numeric|min:0',
            'percentage'       => 'nullable|numeric|min:0|max:100',
            'is_modifiable'    => 'boolean',
            'effectivity_until' => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $validated['is_active']    = true;
        $validated['is_modifiable'] = $request->boolean('is_modifiable');
        $validated['created_by']   = auth()->id();

        $template = CompensationTemplate::create($validated);

        $this->audit->actionLog('payroll_config', 'create_template', 'success', [
            'template_id' => $template->id,
            'name'        => $template->name,
            'category'    => $template->category,
        ]);

        return redirect()->route('admin.payroll-config.index', ['tab' => $this->tabForCategory($validated['category'])])
            ->with('success', 'Compensation template created.');
    }

    /**
     * Update an existing compensation template.
     */
    public function updateTemplate(Request $request, CompensationTemplate $template)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:50',
            'amount_type'      => 'required|in:static,percentage',
            'static_amount'    => 'nullable|numeric|min:0',
            'percentage'       => 'nullable|numeric|min:0|max:100',
            'is_modifiable'    => 'boolean',
            'effectivity_until' => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $validated['is_modifiable'] = $request->boolean('is_modifiable');
        $validated['updated_by']    = auth()->id();

        $template->update($validated);

        $this->audit->actionLog('payroll_config', 'update_template', 'success', [
            'template_id' => $template->id,
            'category'    => $template->category,
        ]);

        return redirect()->route('admin.payroll-config.index', ['tab' => $this->tabForCategory($template->category)])
            ->with('success', 'Template updated.');
    }

    /**
     * Deactivate (soft-delete) a compensation template.
     */
    public function destroyTemplate(CompensationTemplate $template)
    {
        $tab = $this->tabForCategory($template->category);

        $template->update(['is_active' => false, 'updated_by' => auth()->id()]);

        $this->audit->actionLog('payroll_config', 'deactivate_template', 'success', [
            'template_id' => $template->id,
            'category'    => $template->category,
        ]);

        return redirect()->route('admin.payroll-config.index', ['tab' => $tab])
            ->with('success', 'Template deactivated.');
    }

    // ─── Private Helpers ───────────────────────────────────────────────

    /**
     * Load current overtime rate values from DB.
     */
    private function loadOvertimeRates(): array
    {
        $rates = [];
        foreach (self::OT_CODES as $code => $meta) {
            $row = DB::table('payroll_formula_settings')->where('code', $code)->first();
            $rates[$code] = [
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'default'     => $meta['default'],
                'current'     => $row ? (float) $row->default_value : $meta['default'],
            ];
        }
        return $rates;
    }

    /**
     * Map a category value back to its tab key.
     */
    private function tabForCategory(string $category): string
    {
        return match ($category) {
            'allowance'    => 'allowances',
            'contribution' => 'contributions',
            'tax'          => 'taxes',
            'deduction'    => 'deductions',
            default        => 'allowances',
        };
    }
}
