<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BirReportController extends Controller
{
    private const VALID_TABS = ['form-2316', 'alphalist-1604c', 'remittances'];

    /**
     * Payslip item codes for statutory deductions.
     */
    private const TAX_CODE   = 'TAX';
    private const SSS_CODE   = 'SSS';
    private const PHIC_CODE  = 'PHIC';
    private const HDMF_CODE  = 'HDMF';
    private const BASIC_CODE = 'BASIC';

    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * BIR Reports hub — tabbed interface.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'form-2316');
        if (!in_array($tab, self::VALID_TABS)) {
            $tab = 'form-2316';
        }

        $year = (int) $request->input('year', now()->year);
        $month = $request->input('month');
        $branchId = $request->input('branch_id');

        $data = match ($tab) {
            'form-2316'       => $this->getForm2316Data($year, $branchId),
            'alphalist-1604c' => $this->getAlphalistData($year, $branchId),
            'remittances'     => $this->getRemittanceData($year, $month, $branchId),
            default           => [],
        };

        $branches = DB::table('branches')->orderBy('name')->get(['id', 'name']);
        $years = $this->getAvailableYears();

        return view('admin.bir-reports.index', array_merge($data, [
            'pageTitle' => 'BIR Reports & Compliance',
            'activeTab' => $tab,
            'year'      => $year,
            'month'     => $month,
            'branchId'  => $branchId,
            'branches'  => $branches,
            'years'     => $years,
        ]));
    }

    /**
     * Export Form 2316 data as CSV.
     */
    public function exportForm2316(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $branchId = $request->input('branch_id');
        $data = $this->getForm2316Data($year, $branchId);

        $this->audit->actionLog('bir_reports', 'export_2316', 'success', [
            'year' => $year,
            'branch_id' => $branchId,
            'record_count' => count($data['employees'] ?? []),
        ]);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Employee Code', 'Last Name', 'First Name', 'TIN',
                'SSS No.', 'PhilHealth No.', 'Pag-IBIG No.',
                'Gross Compensation', 'Basic Pay', 'Overtime',
                'Other Earnings', 'SSS (EE)', 'PhilHealth (EE)',
                'Pag-IBIG (EE)', 'Total Deductions (Statutory)',
                'Taxable Income', 'Tax Withheld',
            ]);
            foreach ($data['employees'] ?? [] as $emp) {
                fputcsv($out, [
                    $emp->employee_code,
                    $emp->last_name,
                    $emp->first_name,
                    'N/A', // TIN — not stored in employees table yet
                    'N/A', // SSS No.
                    'N/A', // PhilHealth No.
                    'N/A', // Pag-IBIG No.
                    number_format($emp->gross_compensation, 2, '.', ''),
                    number_format($emp->basic_pay, 2, '.', ''),
                    number_format($emp->overtime_pay, 2, '.', ''),
                    number_format($emp->other_earnings, 2, '.', ''),
                    number_format($emp->sss_ee, 2, '.', ''),
                    number_format($emp->phic_ee, 2, '.', ''),
                    number_format($emp->hdmf_ee, 2, '.', ''),
                    number_format($emp->total_statutory, 2, '.', ''),
                    number_format($emp->taxable_income, 2, '.', ''),
                    number_format($emp->tax_withheld, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, "bir-2316-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Export 1604-C Alphalist as CSV.
     */
    public function exportAlphalist(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $branchId = $request->input('branch_id');
        $data = $this->getAlphalistData($year, $branchId);

        $this->audit->actionLog('bir_reports', 'export_alphalist', 'success', [
            'year' => $year,
            'branch_id' => $branchId,
            'record_count' => count($data['employees'] ?? []),
        ]);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Seq', 'TIN', 'Last Name', 'First Name',
                'Gross Compensation', 'Non-Taxable (13th Month & MWE)',
                'Taxable Compensation', 'Tax Withheld', 'Tax Due',
                'Adjustment',
            ]);
            $seq = 0;
            foreach ($data['employees'] ?? [] as $emp) {
                $seq++;
                $adjustment = round($emp->tax_withheld - ($emp->taxable_income > 0 ? $emp->tax_withheld : 0), 2);
                fputcsv($out, [
                    $seq,
                    'N/A', // TIN — not stored in employees table yet
                    $emp->last_name,
                    $emp->first_name,
                    number_format($emp->gross_compensation, 2, '.', ''),
                    number_format($emp->non_taxable, 2, '.', ''),
                    number_format($emp->taxable_income, 2, '.', ''),
                    number_format($emp->tax_withheld, 2, '.', ''),
                    number_format($emp->tax_withheld, 2, '.', ''),
                    number_format($adjustment, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, "bir-1604c-alphalist-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Export monthly remittance report as CSV.
     */
    public function exportRemittance(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = $request->input('month');
        $branchId = $request->input('branch_id');
        $data = $this->getRemittanceData($year, $month, $branchId);

        $this->audit->actionLog('bir_reports', 'export_remittance', 'success', [
            'year' => $year,
            'month' => $month,
            'branch_id' => $branchId,
        ]);

        $period = $month ? date('F', mktime(0, 0, 0, (int) $month)) . "-{$year}" : $year;

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Month', 'SSS (Employee)', 'SSS (Employer)', 'SSS Total',
                'PhilHealth (Employee)', 'PhilHealth (Employer)', 'PhilHealth Total',
                'Pag-IBIG (Employee)', 'Pag-IBIG (Employer)', 'Pag-IBIG Total',
                'Withholding Tax', 'Grand Total',
            ]);
            foreach ($data['monthly'] ?? [] as $row) {
                fputcsv($out, [
                    $row->month_label,
                    number_format($row->sss_ee, 2, '.', ''),
                    number_format($row->sss_er, 2, '.', ''),
                    number_format($row->sss_total, 2, '.', ''),
                    number_format($row->phic_ee, 2, '.', ''),
                    number_format($row->phic_er, 2, '.', ''),
                    number_format($row->phic_total, 2, '.', ''),
                    number_format($row->hdmf_ee, 2, '.', ''),
                    number_format($row->hdmf_er, 2, '.', ''),
                    number_format($row->hdmf_total, 2, '.', ''),
                    number_format($row->tax, 2, '.', ''),
                    number_format($row->grand_total, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, "remittance-summary-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    // ─── Data Builders ────────────────────────────────────────────

    /**
     * Build per-employee annual compensation & tax data for Form 2316.
     */
    private function getForm2316Data(int $year, ?string $branchId): array
    {
        $rows = $this->annualPayslipSummary($year, $branchId);

        // Compute totals
        $totals = (object) [
            'gross_compensation' => $rows->sum('gross_compensation'),
            'basic_pay'          => $rows->sum('basic_pay'),
            'overtime_pay'       => $rows->sum('overtime_pay'),
            'other_earnings'     => $rows->sum('other_earnings'),
            'sss_ee'             => $rows->sum('sss_ee'),
            'phic_ee'            => $rows->sum('phic_ee'),
            'hdmf_ee'            => $rows->sum('hdmf_ee'),
            'total_statutory'    => $rows->sum('total_statutory'),
            'taxable_income'     => $rows->sum('taxable_income'),
            'tax_withheld'       => $rows->sum('tax_withheld'),
        ];

        return [
            'employees' => $rows,
            'totals'    => $totals,
        ];
    }

    /**
     * Build alphalist data for 1604-C (same base data, slightly different presentation).
     */
    private function getAlphalistData(int $year, ?string $branchId): array
    {
        $rows = $this->annualPayslipSummary($year, $branchId);

        // Non-taxable: 13th month + de minimis (estimate from other_earnings or separate code)
        foreach ($rows as $row) {
            $row->non_taxable = min($row->other_earnings, 90000); // 13th month + de minimis ceiling per BIR
        }

        $totals = (object) [
            'gross_compensation' => $rows->sum('gross_compensation'),
            'non_taxable'        => $rows->sum('non_taxable'),
            'taxable_income'     => $rows->sum('taxable_income'),
            'tax_withheld'       => $rows->sum('tax_withheld'),
            'employee_count'     => $rows->count(),
        ];

        return [
            'employees' => $rows,
            'totals'    => $totals,
        ];
    }

    /**
     * Build monthly remittance summary (SSS SBR, PhilHealth RF-1, Pag-IBIG, Tax).
     */
    private function getRemittanceData(int $year, ?string $month, ?string $branchId): array
    {
        $query = DB::table('payslip_items as pi')
            ->join('payslips as p', 'pi.payslip_id', '=', 'p.id')
            ->join('employees as e', 'p.employee_id', '=', 'e.id')
            ->whereRaw("EXTRACT(YEAR FROM p.period_start) = ?", [$year])
            ->whereIn('pi.code', [self::SSS_CODE, self::PHIC_CODE, self::HDMF_CODE, self::TAX_CODE])
            ->whereIn('p.status', ['locked', 'released']);

        if ($branchId) {
            $query->where('e.branch_id', $branchId);
        }
        if ($month) {
            $query->whereRaw("EXTRACT(MONTH FROM p.period_start) = ?", [(int) $month]);
        }

        $raw = $query->select([
            DB::raw("EXTRACT(MONTH FROM p.period_start)::int AS month_num"),
            DB::raw("TO_CHAR(p.period_start, 'Month') AS month_label"),
            DB::raw("SUM(CASE WHEN pi.code = 'SSS'  THEN pi.amount ELSE 0 END) AS sss_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'PHIC' THEN pi.amount ELSE 0 END) AS phic_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'HDMF' THEN pi.amount ELSE 0 END) AS hdmf_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'TAX'  THEN pi.amount ELSE 0 END) AS tax"),
        ])
        ->groupBy(DB::raw("EXTRACT(MONTH FROM p.period_start)::int"), DB::raw("TO_CHAR(p.period_start, 'Month')"))
        ->orderBy('month_num')
        ->get();

        // Compute employer shares using standard Philippine rates
        foreach ($raw as $row) {
            $row->month_label  = trim($row->month_label);
            $row->sss_ee       = (float) $row->sss_ee;
            $row->phic_ee      = (float) $row->phic_ee;
            $row->hdmf_ee      = (float) $row->hdmf_ee;
            $row->tax          = (float) $row->tax;

            // SSS employer share ≈ employee share × (9.5/4.5) per 2025 schedule
            $row->sss_er       = round($row->sss_ee * (9.5 / 4.5), 2);
            $row->sss_total    = round($row->sss_ee + $row->sss_er, 2);

            // PhilHealth employer = equal to employee share
            $row->phic_er      = $row->phic_ee;
            $row->phic_total   = round($row->phic_ee + $row->phic_er, 2);

            // Pag-IBIG employer = employee share (both capped at ₱100 for ≤₱5K, 2% for >₱5K)
            $row->hdmf_er      = $row->hdmf_ee;
            $row->hdmf_total   = round($row->hdmf_ee + $row->hdmf_er, 2);

            $row->grand_total  = round($row->sss_total + $row->phic_total + $row->hdmf_total + $row->tax, 2);
        }

        // Annual totals
        $annualTotals = (object) [
            'sss_ee'      => $raw->sum('sss_ee'),
            'sss_er'      => $raw->sum('sss_er'),
            'sss_total'   => $raw->sum('sss_total'),
            'phic_ee'     => $raw->sum('phic_ee'),
            'phic_er'     => $raw->sum('phic_er'),
            'phic_total'  => $raw->sum('phic_total'),
            'hdmf_ee'     => $raw->sum('hdmf_ee'),
            'hdmf_er'     => $raw->sum('hdmf_er'),
            'hdmf_total'  => $raw->sum('hdmf_total'),
            'tax'         => $raw->sum('tax'),
            'grand_total' => $raw->sum('grand_total'),
        ];

        return [
            'monthly'      => $raw,
            'annualTotals' => $annualTotals,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Core query: aggregate payslip items into per-employee annual totals.
     */
    private function annualPayslipSummary(int $year, ?string $branchId)
    {
        $query = DB::table('payslips as p')
            ->join('employees as e', 'p.employee_id', '=', 'e.id')
            ->leftJoin('payslip_items as pi', 'pi.payslip_id', '=', 'p.id')
            ->whereRaw("EXTRACT(YEAR FROM p.period_start) = ?", [$year])
            ->whereIn('p.status', ['locked', 'released']);

        if ($branchId) {
            $query->where('e.branch_id', $branchId);
        }

        $rows = $query->select([
            'e.id',
            'e.employee_code',
            'e.first_name',
            'e.last_name',
            DB::raw("SUM(p.gross_pay) / COUNT(DISTINCT p.id) * COUNT(DISTINCT p.id) AS raw_gross"),
            // Earnings breakdown from payslip_items
            DB::raw("SUM(CASE WHEN pi.code = 'BASIC' AND pi.type = 'earning' THEN pi.amount ELSE 0 END) AS basic_pay"),
            DB::raw("SUM(CASE WHEN pi.code = 'OT' AND pi.type = 'earning' THEN pi.amount ELSE 0 END) AS overtime_pay"),
            DB::raw("SUM(CASE WHEN pi.type = 'earning' AND pi.code NOT IN ('BASIC','OT') THEN pi.amount ELSE 0 END) AS other_earnings"),
            DB::raw("SUM(CASE WHEN pi.type = 'earning' THEN pi.amount ELSE 0 END) AS gross_compensation"),
            // Statutory deductions
            DB::raw("SUM(CASE WHEN pi.code = 'SSS'  AND pi.type = 'deduction' THEN pi.amount ELSE 0 END) AS sss_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'PHIC' AND pi.type = 'deduction' THEN pi.amount ELSE 0 END) AS phic_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'HDMF' AND pi.type = 'deduction' THEN pi.amount ELSE 0 END) AS hdmf_ee"),
            DB::raw("SUM(CASE WHEN pi.code = 'TAX'  AND pi.type = 'deduction' THEN pi.amount ELSE 0 END) AS tax_withheld"),
        ])
        ->groupBy('e.id', 'e.employee_code', 'e.first_name', 'e.last_name')
        ->orderBy('e.last_name')
        ->orderBy('e.first_name')
        ->get();

        // Compute derived fields
        foreach ($rows as $row) {
            $row->basic_pay          = (float) $row->basic_pay;
            $row->overtime_pay       = (float) $row->overtime_pay;
            $row->other_earnings     = (float) $row->other_earnings;
            $row->gross_compensation = (float) $row->gross_compensation;
            $row->sss_ee             = (float) $row->sss_ee;
            $row->phic_ee            = (float) $row->phic_ee;
            $row->hdmf_ee            = (float) $row->hdmf_ee;
            $row->tax_withheld       = (float) $row->tax_withheld;
            $row->total_statutory    = round($row->sss_ee + $row->phic_ee + $row->hdmf_ee, 2);
            $row->taxable_income     = round($row->gross_compensation - $row->total_statutory, 2);
        }

        return $rows;
    }

    /**
     * Get list of years that have payslip data.
     */
    private function getAvailableYears(): array
    {
        $years = DB::table('payslips')
            ->selectRaw("DISTINCT EXTRACT(YEAR FROM period_start)::int AS yr")
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        // Always include current year
        $currentYear = (int) now()->year;
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return $years;
    }
}
