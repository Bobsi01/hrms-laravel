@extends('layouts.app')

@section('title', $pageTitle ?? 'BIR Reports & Compliance')

@section('content')
{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">BIR Reports & Compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Generate BIR forms, alphalists, and statutory remittance reports.</p>
    </div>
</div>

{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.bir-reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                <select name="year" class="input-text w-28">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            @if($activeTab === 'remittances')
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Month</label>
                <select name="month" class="input-text w-36">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m)) }}</option>
                    @endfor
                </select>
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Branch</label>
                <select name="branch_id" class="input-text w-40">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
        </form>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="border-b border-slate-200 mb-6">
    <nav class="-mb-px flex gap-4 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('admin.bir-reports.index', ['tab' => 'form-2316', 'year' => $year, 'branch_id' => $branchId]) }}"
           class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition {{ $activeTab === 'form-2316' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Form 2316
        </a>
        <a href="{{ route('admin.bir-reports.index', ['tab' => 'alphalist-1604c', 'year' => $year, 'branch_id' => $branchId]) }}"
           class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition {{ $activeTab === 'alphalist-1604c' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            1604-C Alphalist
        </a>
        <a href="{{ route('admin.bir-reports.index', ['tab' => 'remittances', 'year' => $year, 'branch_id' => $branchId, 'month' => $month]) }}"
           class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition {{ $activeTab === 'remittances' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 10-10 0v2M5 9h14l1 10H4L5 9zm5 5h4"/></svg>
            Monthly Remittances
        </a>
    </nav>
</div>

{{-- ═══ Form 2316 Tab ═══ --}}
@if($activeTab === 'form-2316')
<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>BIR Form 2316 — Certificate of Compensation Payment / Tax Withheld ({{ $year }})</span>
        @if(($employees ?? collect())->count())
        <a href="{{ route('admin.bir-reports.export-2316', ['year' => $year, 'branch_id' => $branchId]) }}" class="btn btn-outline text-sm" data-no-loader>
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
        @endif
    </div>
    <div class="card-body">
        @if(($employees ?? collect())->count())
            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="flex items-center gap-3 rounded-lg bg-indigo-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ $employees->count() }}</div>
                        <div class="text-xs text-slate-500">Employees</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-emerald-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->gross_compensation ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Gross Compensation</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-amber-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->total_statutory ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Statutory Deductions</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-red-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->tax_withheld ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Tax Withheld</div>
                    </div>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="overflow-x-auto">
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>TIN</th>
                            <th class="text-right">Gross Comp.</th>
                            <th class="text-right">Basic Pay</th>
                            <th class="text-right">OT Pay</th>
                            <th class="text-right">SSS (EE)</th>
                            <th class="text-right">PhilHealth</th>
                            <th class="text-right">Pag-IBIG</th>
                            <th class="text-right">Taxable Income</th>
                            <th class="text-right">Tax Withheld</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        <tr>
                            <td class="font-medium">
                                <div>{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                                <div class="text-xs text-slate-400">{{ $emp->employee_code }}</div>
                            </td>
                            <td>
                                <span class="text-xs text-slate-400">N/A</span>
                            </td>
                            <td class="text-right font-medium">{{ number_format($emp->gross_compensation, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->basic_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->overtime_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->sss_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->phic_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->hdmf_ee, 2) }}</td>
                            <td class="text-right font-medium">{{ number_format($emp->taxable_income, 2) }}</td>
                            <td class="text-right font-medium text-red-600">{{ number_format($emp->tax_withheld, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold">
                        <tr>
                            <td colspan="2">Totals</td>
                            <td class="text-right">{{ number_format($totals->gross_compensation, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->basic_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->overtime_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->sss_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->phic_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->hdmf_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->taxable_income, 2) }}</td>
                            <td class="text-right text-red-600">{{ number_format($totals->tax_withheld, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- TIN data not yet available — column does not exist in employees table --}}
        @else
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm font-medium">No payslip data found for {{ $year }}</p>
                <p class="text-xs mt-1">Process payroll runs to generate data for BIR reports.</p>
            </div>
        @endif
    </div>
</div>
@endif

{{-- ═══ 1604-C Alphalist Tab ═══ --}}
@if($activeTab === 'alphalist-1604c')
<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>BIR Form 1604-C Alphalist — Annual Information Return ({{ $year }})</span>
        @if(($employees ?? collect())->count())
        <a href="{{ route('admin.bir-reports.export-alphalist', ['year' => $year, 'branch_id' => $branchId]) }}" class="btn btn-outline text-sm" data-no-loader>
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
        @endif
    </div>
    <div class="card-body">
        @if(($employees ?? collect())->count())
            {{-- Summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="flex items-center gap-3 rounded-lg bg-indigo-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ $totals->employee_count ?? 0 }}</div>
                        <div class="text-xs text-slate-500">Total Employees</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-emerald-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->gross_compensation ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Gross</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-blue-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->taxable_income ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Taxable</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg bg-red-50 p-4">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($totals->tax_withheld ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Total Tax Withheld</div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th class="w-12">Seq</th>
                            <th>TIN</th>
                            <th>Employee Name</th>
                            <th class="text-right">Gross Compensation</th>
                            <th class="text-right">Non-Taxable</th>
                            <th class="text-right">Taxable Compensation</th>
                            <th class="text-right">Tax Withheld</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $idx => $emp)
                        <tr>
                            <td class="text-slate-400">{{ $idx + 1 }}</td>
                            <td>
                                <span class="text-xs text-slate-400">N/A</span>
                            </td>
                            <td class="font-medium">{{ $emp->last_name }}, {{ $emp->first_name }}</td>
                            <td class="text-right">{{ number_format($emp->gross_compensation, 2) }}</td>
                            <td class="text-right">{{ number_format($emp->non_taxable ?? 0, 2) }}</td>
                            <td class="text-right font-medium">{{ number_format($emp->taxable_income, 2) }}</td>
                            <td class="text-right font-medium text-red-600">{{ number_format($emp->tax_withheld, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold">
                        <tr>
                            <td colspan="3">Totals ({{ $totals->employee_count }} employees)</td>
                            <td class="text-right">{{ number_format($totals->gross_compensation, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->non_taxable, 2) }}</td>
                            <td class="text-right">{{ number_format($totals->taxable_income, 2) }}</td>
                            <td class="text-right text-red-600">{{ number_format($totals->tax_withheld, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <p class="text-sm font-medium">No alphalist data found for {{ $year }}</p>
                <p class="text-xs mt-1">Process payroll runs to generate data for BIR alphalist.</p>
            </div>
        @endif
    </div>
</div>
@endif

{{-- ═══ Monthly Remittances Tab ═══ --}}
@if($activeTab === 'remittances')
<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>Statutory Remittance Summary — {{ $month ? date('F', mktime(0,0,0,(int)$month)) . ' ' : '' }}{{ $year }}</span>
        @if(($monthly ?? collect())->count())
        <a href="{{ route('admin.bir-reports.export-remittance', ['year' => $year, 'month' => $month, 'branch_id' => $branchId]) }}" class="btn btn-outline text-sm" data-no-loader>
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
        @endif
    </div>
    <div class="card-body">
        @if(($monthly ?? collect())->count())
            {{-- Agency summary cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">SSS (SBR)</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($annualTotals->sss_total ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">EE: {{ number_format($annualTotals->sss_ee ?? 0, 2) }} / ER: {{ number_format($annualTotals->sss_er ?? 0, 2) }}</div>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">PhilHealth (RF-1)</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($annualTotals->phic_total ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">EE: {{ number_format($annualTotals->phic_ee ?? 0, 2) }} / ER: {{ number_format($annualTotals->phic_er ?? 0, 2) }}</div>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <div class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-1">Pag-IBIG</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($annualTotals->hdmf_total ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">EE: {{ number_format($annualTotals->hdmf_ee ?? 0, 2) }} / ER: {{ number_format($annualTotals->hdmf_er ?? 0, 2) }}</div>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Withholding Tax</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($annualTotals->tax ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Annual total withheld per BIR TRAIN law</div>
                </div>
            </div>

            {{-- Monthly breakdown table --}}
            <div class="overflow-x-auto">
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">SSS (EE)</th>
                            <th class="text-right">SSS (ER)</th>
                            <th class="text-right">SSS Total</th>
                            <th class="text-right">PhilHealth (EE)</th>
                            <th class="text-right">PhilHealth (ER)</th>
                            <th class="text-right">PhilHealth Total</th>
                            <th class="text-right">Pag-IBIG (EE)</th>
                            <th class="text-right">Pag-IBIG (ER)</th>
                            <th class="text-right">Pag-IBIG Total</th>
                            <th class="text-right">W/Tax</th>
                            <th class="text-right font-semibold">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly as $row)
                        <tr>
                            <td class="font-medium">{{ $row->month_label }}</td>
                            <td class="text-right">{{ number_format($row->sss_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($row->sss_er, 2) }}</td>
                            <td class="text-right font-medium text-blue-600">{{ number_format($row->sss_total, 2) }}</td>
                            <td class="text-right">{{ number_format($row->phic_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($row->phic_er, 2) }}</td>
                            <td class="text-right font-medium text-emerald-600">{{ number_format($row->phic_total, 2) }}</td>
                            <td class="text-right">{{ number_format($row->hdmf_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($row->hdmf_er, 2) }}</td>
                            <td class="text-right font-medium text-amber-600">{{ number_format($row->hdmf_total, 2) }}</td>
                            <td class="text-right font-medium text-red-600">{{ number_format($row->tax, 2) }}</td>
                            <td class="text-right font-bold">{{ number_format($row->grand_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold">
                        <tr>
                            <td>Annual Totals</td>
                            <td class="text-right">{{ number_format($annualTotals->sss_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->sss_er, 2) }}</td>
                            <td class="text-right text-blue-600">{{ number_format($annualTotals->sss_total, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->phic_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->phic_er, 2) }}</td>
                            <td class="text-right text-emerald-600">{{ number_format($annualTotals->phic_total, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->hdmf_ee, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->hdmf_er, 2) }}</td>
                            <td class="text-right text-amber-600">{{ number_format($annualTotals->hdmf_total, 2) }}</td>
                            <td class="text-right text-red-600">{{ number_format($annualTotals->tax, 2) }}</td>
                            <td class="text-right">{{ number_format($annualTotals->grand_total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Remittance deadlines info --}}
            <div class="mt-4 rounded-lg bg-blue-50 border border-blue-200 p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs text-blue-700">
                    <div class="font-semibold mb-1">Remittance Deadlines</div>
                    <div><strong>SSS:</strong> Due within the month following the applicable month (based on last digit of SSS ER ID).</div>
                    <div><strong>PhilHealth:</strong> Due on or before the 15th of the month following the applicable month.</div>
                    <div><strong>Pag-IBIG:</strong> Due on or before the 15th of the month following the applicable month.</div>
                    <div><strong>BIR W/Tax (1601-C):</strong> Due on or before the 10th of the month following the month of withholding.</div>
                </div>
            </div>
        @else
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a5 5 0 10-10 0v2M5 9h14l1 10H4L5 9zm5 5h4"/></svg>
                <p class="text-sm font-medium">No remittance data for {{ $month ? date('F', mktime(0,0,0,(int)$month)) . ' ' : '' }}{{ $year }}</p>
                <p class="text-xs mt-1">Process payroll runs to generate statutory deduction data.</p>
            </div>
        @endif
    </div>
</div>
@endif
@endsection
