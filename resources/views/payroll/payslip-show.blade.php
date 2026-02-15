@extends('layouts.app')

@section('content')
<div class="mb-6">
    @if($isAdmin)
    <a href="{{ route('payroll.show', $payslip->payroll_run_id) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Run #{{ $payslip->payroll_run_id }}</a>
    @else
    <a href="{{ route('payroll.my-payslips') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to My Payslips</a>
    @endif
    <h1 class="text-xl font-bold text-slate-900 mt-2">Payslip Detail</h1>
    <p class="text-sm text-slate-500 mt-0.5">
        {{ $payslip->employee->full_name ?? 'Employee' }} — 
        {{ \Carbon\Carbon::parse($payslip->period_start)->format('M d') }} to {{ \Carbon\Carbon::parse($payslip->period_end)->format('M d, Y') }}
    </p>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="card card-body text-center">
        <div class="text-xs text-slate-500 mb-1">Basic Pay</div>
        <div class="text-xl font-bold text-slate-900">₱{{ number_format($payslip->basic_pay ?? 0, 2) }}</div>
    </div>
    <div class="card card-body text-center">
        <div class="text-xs text-slate-500 mb-1">Total Earnings</div>
        <div class="text-xl font-bold text-emerald-600">₱{{ number_format($payslip->total_earnings ?? 0, 2) }}</div>
    </div>
    <div class="card card-body text-center">
        <div class="text-xs text-slate-500 mb-1">Gross Pay</div>
        <div class="text-xl font-bold text-slate-900">₱{{ number_format($payslip->gross_pay ?? 0, 2) }}</div>
    </div>
    <div class="card card-body text-center">
        <div class="text-xs text-slate-500 mb-1">Deductions</div>
        <div class="text-xl font-bold text-red-600">₱{{ number_format($payslip->total_deductions ?? 0, 2) }}</div>
    </div>
    <div class="card card-body text-center col-span-2 lg:col-span-1">
        <div class="text-xs text-slate-500 mb-1">Net Pay</div>
        <div class="text-2xl font-bold text-indigo-600">₱{{ number_format($payslip->net_pay ?? 0, 2) }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Earnings --}}
    <div class="card">
        <div class="card-header"><span>Earnings</span></div>
        <div class="card-body">
            @php
                $earnings = $payslip->items ? $payslip->items->where('type', 'earning') : collect();
                $earningsJson = is_array($payslip->earnings_json) ? $payslip->earnings_json : [];
            @endphp

            @if($earnings->count() > 0)
            <table class="table-basic">
                <tbody>
                    @foreach($earnings as $item)
                    <tr>
                        <td class="text-slate-700">{{ $item->label }}</td>
                        <td class="text-right font-medium text-slate-900">₱{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200">
                        <td class="font-semibold text-slate-900">Total Earnings</td>
                        <td class="text-right font-bold text-emerald-600">₱{{ number_format($payslip->total_earnings ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @elseif(count($earningsJson) > 0)
            <table class="table-basic">
                <tbody>
                    @foreach($earningsJson as $key => $value)
                    <tr>
                        <td class="text-slate-700">{{ ucfirst(str_replace('_', ' ', is_string($key) ? $key : ($value['label'] ?? 'Item'))) }}</td>
                        <td class="text-right font-medium text-slate-900">₱{{ number_format(is_numeric($value) ? $value : ($value['amount'] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-slate-500 py-3 text-center">No itemized earnings.</p>
            @endif
        </div>
    </div>

    {{-- Deductions --}}
    <div class="card">
        <div class="card-header"><span>Deductions</span></div>
        <div class="card-body">
            @php
                $deductions = $payslip->items ? $payslip->items->where('type', 'deduction') : collect();
                $deductionsJson = is_array($payslip->deductions_json) ? $payslip->deductions_json : [];
            @endphp

            @if($deductions->count() > 0)
            <table class="table-basic">
                <tbody>
                    @foreach($deductions as $item)
                    <tr>
                        <td class="text-slate-700">{{ $item->label }}</td>
                        <td class="text-right font-medium text-red-600">₱{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200">
                        <td class="font-semibold text-slate-900">Total Deductions</td>
                        <td class="text-right font-bold text-red-600">₱{{ number_format($payslip->total_deductions ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @elseif(count($deductionsJson) > 0)
            <table class="table-basic">
                <tbody>
                    @foreach($deductionsJson as $key => $value)
                    <tr>
                        <td class="text-slate-700">{{ ucfirst(str_replace('_', ' ', is_string($key) ? $key : ($value['label'] ?? 'Item'))) }}</td>
                        <td class="text-right font-medium text-red-600">₱{{ number_format(is_numeric($value) ? $value : ($value['amount'] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-slate-500 py-3 text-center">No itemized deductions.</p>
            @endif
        </div>
    </div>
</div>

{{-- Payslip Metadata --}}
<div class="card">
    <div class="card-header"><span>Payslip Information</span></div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-slate-500 block">Payslip #</span>
                <span class="font-medium text-slate-900">{{ $payslip->id }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">Status</span>
                <span class="font-medium text-slate-900">{{ ucfirst($payslip->status ?? 'N/A') }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">Version</span>
                <span class="font-medium text-slate-900">{{ $payslip->version ?? 1 }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">Released</span>
                <span class="font-medium text-slate-900">{{ $payslip->released_at ? \Carbon\Carbon::parse($payslip->released_at)->format('M d, Y h:i A') : '—' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
