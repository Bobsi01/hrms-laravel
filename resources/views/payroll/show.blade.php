@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('payroll.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Payroll Runs</a>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Payroll Run #{{ $payrollRun->id }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ \Carbon\Carbon::parse($payrollRun->period_start)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($payrollRun->period_end)->format('M d, Y') }}
            </p>
        </div>
        <div>
            @php
                $statusColors = [
                    'draft' => 'bg-slate-100 text-slate-600',
                    'processing' => 'bg-blue-100 text-blue-700',
                    'computing' => 'bg-blue-100 text-blue-700',
                    'awaiting_approval' => 'bg-amber-100 text-amber-700',
                    'submitted' => 'bg-indigo-100 text-indigo-700',
                    'released' => 'bg-emerald-100 text-emerald-700',
                    'closed' => 'bg-slate-100 text-slate-500',
                ];
            @endphp
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$payrollRun->status] ?? 'bg-slate-100 text-slate-600' }}">
                {{ ucfirst(str_replace('_', ' ', $payrollRun->status)) }}
            </span>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body">
        <div class="text-xs text-slate-500 mb-1">Total Payslips</div>
        <div class="text-2xl font-bold text-slate-900">{{ $payslipSummary['count'] }}</div>
    </div>
    <div class="card card-body">
        <div class="text-xs text-slate-500 mb-1">Total Gross Pay</div>
        <div class="text-2xl font-bold text-emerald-600">₱{{ number_format($payslipSummary['total_gross'], 2) }}</div>
    </div>
    <div class="card card-body">
        <div class="text-xs text-slate-500 mb-1">Total Deductions</div>
        <div class="text-2xl font-bold text-red-600">₱{{ number_format($payslipSummary['total_deductions'], 2) }}</div>
    </div>
    <div class="card card-body">
        <div class="text-xs text-slate-500 mb-1">Total Net Pay</div>
        <div class="text-2xl font-bold text-indigo-600">₱{{ number_format($payslipSummary['total_net'], 2) }}</div>
    </div>
</div>

{{-- Run Details --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card lg:col-span-1">
        <div class="card-header"><span>Run Details</span></div>
        <div class="card-body space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Run Mode</span>
                <span class="font-medium text-slate-900">{{ ucfirst($payrollRun->run_mode ?? '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Computation</span>
                <span class="font-medium text-slate-900">{{ ucfirst($payrollRun->computation_mode ?? '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Initiated By</span>
                <span class="font-medium text-slate-900">{{ $payrollRun->generatedBy->full_name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Created</span>
                <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($payrollRun->created_at)->format('M d, Y h:i A') }}</span>
            </div>
            @if($payrollRun->released_at)
            <div class="flex justify-between">
                <span class="text-slate-500">Released</span>
                <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($payrollRun->released_at)->format('M d, Y h:i A') }}</span>
            </div>
            @endif
            @if($payrollRun->notes)
            <div class="pt-2 border-t border-slate-200">
                <span class="text-slate-500 block mb-1">Notes</span>
                <p class="text-slate-900">{{ $payrollRun->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Batches --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <span>Batches ({{ $batchSummary['total'] }})</span>
            <div class="flex gap-2 text-xs">
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $batchSummary['approved'] }} Approved</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $batchSummary['pending'] }} Pending</span>
                @if($batchSummary['rejected'] > 0)
                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ $batchSummary['rejected'] }} Rejected</span>
                @endif
            </div>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Computed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrollRun->batches as $batch)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $batch->branch->name ?? 'Branch #' . $batch->branch_id }}</td>
                        <td>
                            @php
                                $bStatusColors = [
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'submitted' => 'bg-indigo-100 text-indigo-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $bStatusColors[$batch->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                        <td class="text-sm text-slate-500">{{ $batch->last_computed_at ? \Carbon\Carbon::parse($batch->last_computed_at)->format('M d, Y h:i A') : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500 py-4">No batches.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Payslips --}}
<div class="card mb-6">
    <div class="card-header"><span>Payslips ({{ $payslips->count() }})</span></div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Basic Pay</th>
                    <th>Gross Pay</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $slip)
                <tr>
                    <td class="font-medium text-slate-900">{{ $slip->employee->full_name ?? '—' }}</td>
                    <td>₱{{ number_format($slip->basic_pay ?? 0, 2) }}</td>
                    <td>₱{{ number_format($slip->gross_pay ?? 0, 2) }}</td>
                    <td class="text-red-600">₱{{ number_format($slip->total_deductions ?? 0, 2) }}</td>
                    <td class="font-semibold">₱{{ number_format($slip->net_pay ?? 0, 2) }}</td>
                    <td>
                        @php
                            $pColors = [
                                'released' => 'bg-emerald-100 text-emerald-700',
                                'draft' => 'bg-slate-100 text-slate-600',
                                'computed' => 'bg-blue-100 text-blue-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $pColors[$slip->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($slip->status ?? 'Draft') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('payroll.payslip', $slip) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No payslips generated for this run.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Complaints --}}
@if($complaints->count() > 0)
<div class="card">
    <div class="card-header"><span>Complaints ({{ $complaints->count() }})</span></div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Employee</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Filed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $c)
                <tr>
                    <td class="font-medium text-slate-900">{{ $c->ticket_code ?? '—' }}</td>
                    <td>{{ $c->employee->full_name ?? '—' }}</td>
                    <td>{{ $c->subject ?? $c->issue_type }}</td>
                    <td>
                        @php
                            $ccColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'in_review' => 'bg-blue-100 text-blue-700',
                                'resolved' => 'bg-emerald-100 text-emerald-700',
                                'confirmed' => 'bg-indigo-100 text-indigo-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $ccColors[$c->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $c->status)) }}
                        </span>
                    </td>
                    <td class="text-sm text-slate-500">{{ $c->submitted_at ? \Carbon\Carbon::parse($c->submitted_at)->format('M d, Y') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
