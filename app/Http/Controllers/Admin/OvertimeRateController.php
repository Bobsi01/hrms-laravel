<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvertimeRateController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    private const OT_CODES = [
        'overtime_multiplier' => ['label' => 'Regular Overtime', 'description' => 'Regular working day overtime (125%)', 'default' => 1.25],
        'rest_day_ot_multiplier' => ['label' => 'Rest Day Overtime', 'description' => 'Rest day overtime rate', 'default' => 1.30],
        'regular_holiday_multiplier' => ['label' => 'Regular Holiday', 'description' => 'Regular holiday premium', 'default' => 2.00],
        'regular_holiday_ot_multiplier' => ['label' => 'Regular Holiday OT', 'description' => 'Regular holiday overtime', 'default' => 2.60],
        'special_holiday_multiplier' => ['label' => 'Special Holiday', 'description' => 'Special non-working holiday', 'default' => 1.30],
        'special_holiday_ot_multiplier' => ['label' => 'Special Holiday OT', 'description' => 'Special holiday overtime', 'default' => 1.69],
    ];

    public function index()
    {
        $rates = [];
        foreach (self::OT_CODES as $code => $meta) {
            $row = DB::table('payroll_formula_settings')->where('code', $code)->first();
            $rates[$code] = [
                'label' => $meta['label'],
                'description' => $meta['description'],
                'default' => $meta['default'],
                'current' => $row ? (float) $row->default_value : $meta['default'],
            ];
        }

        return view('admin.overtime-rates.index', compact('rates'));
    }

    public function update(Request $request)
    {
        $changes = [];
        foreach (self::OT_CODES as $code => $meta) {
            $newVal = trim($request->input($code, ''));
            if ($newVal === '') continue;

            $newVal = (float) $newVal;
            $row = DB::table('payroll_formula_settings')->where('code', $code)->first();
            $oldVal = $row ? (float) $row->default_value : $meta['default'];

            if (abs($newVal - $oldVal) < 0.001) continue;

            if ($row) {
                DB::table('payroll_formula_settings')
                    ->where('code', $code)
                    ->update([
                        'default_value' => $newVal,
                        'updated_by' => auth()->id(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('payroll_formula_settings')->insert([
                    'code' => $code,
                    'label' => $meta['label'],
                    'default_value' => $newVal,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $changes[$code] = ['old' => $oldVal, 'new' => $newVal];
        }

        if (empty($changes)) {
            return back()->with('error', 'No changes detected.');
        }

        $this->audit->actionLog('overtime_rates', 'update', 'success', [
            'changes' => $changes,
        ]);

        return redirect()->route('admin.overtime-rates.index')
            ->with('success', count($changes) . ' overtime rate(s) updated.');
    }
}
