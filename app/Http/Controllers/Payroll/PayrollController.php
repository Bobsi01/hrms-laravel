<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Payroll runs dashboard.
     */
    public function index(Request $request)
    {
        $payrollRuns = PayrollRun::orderByDesc('created_at')
            ->paginate(20);

        return view('payroll.index', compact('payrollRuns'));
    }

    /**
     * My payslips (self-service).
     */
    public function myPayslips(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('payroll.my-payslips', ['payslips' => collect()]);
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('payroll.my-payslips', compact('payslips'));
    }
}
