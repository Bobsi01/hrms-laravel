@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">My Payroll</h1>
    <p class="text-sm text-slate-500 mt-0.5">View your payslips and track complaint status</p>
</div>

{{-- Tabs --}}
<div class="border-b border-slate-200 mb-6">
    <nav class="-mb-px flex space-x-8">
        <a href="?tab=payslips" class="{{ ($activeTab ?? 'payslips') === 'payslips' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
            Payslips
        </a>
        <a href="?tab=complaints" class="{{ ($activeTab ?? 'payslips') === 'complaints' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
            My Complaints
            @if(isset($complaints) && $complaints->whereIn('status', ['pending', 'in_review'])->count() > 0)
            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                {{ $complaints->whereIn('status', ['pending', 'in_review'])->count() }}
            </span>
            @endif
        </a>
    </nav>
</div>

@if(($activeTab ?? 'payslips') === 'payslips')
{{-- Payslips Tab --}}
@if($payslips instanceof \Illuminate\Support\Collection && $payslips->isEmpty())
<div class="card card-body text-center py-12">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p class="text-slate-500">Your account is not linked to an employee profile.<br>Contact HR to resolve this.</p>
</div>
@else
{{-- Complaint Filing (collapsible) --}}
@if($payslips->count() > 0)
<div class="card mb-4">
    <div class="card-body">
        <details class="group">
            <summary class="cursor-pointer text-indigo-600 font-medium hover:text-indigo-700 flex items-center text-sm">
                <svg class="w-4 h-4 mr-2 transform group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                File a Payroll Complaint
            </summary>
            <form method="POST" action="{{ route('payroll.file-complaint') }}" class="mt-4 space-y-4 p-4 bg-slate-50 rounded-lg">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 required">Select Payslip</label>
                        <select name="payslip_id" class="input-text w-full" required>
                            <option value="">Choose the payslip with the issue...</option>
                            @foreach($payslips as $p)
                            <option value="{{ $p->id }}">
                                {{ \Carbon\Carbon::parse($p->period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($p->period_end)->format('M d, Y') }}
                                — Net: ₱{{ number_format($p->net_pay, 2) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 required">Issue Type</label>
                        <select name="issue_type" class="input-text w-full" required>
                            <option value="">Select category...</option>
                            <option value="computation_error">Computation Error</option>
                            <option value="missing_allowance">Missing Allowance</option>
                            <option value="wrong_deduction">Wrong Deduction</option>
                            <option value="attendance_issue">Attendance Issue</option>
                            <option value="overtime_issue">Overtime Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Subject</label>
                    <input type="text" name="subject" class="input-text w-full" placeholder="Brief summary of the issue" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Description</label>
                    <textarea name="description" rows="3" class="input-text w-full" placeholder="Describe the issue in detail..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Complaint</button>
            </form>
        </details>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="hidden sm:table-cell">Basic Pay</th>
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
                    <td class="font-medium text-slate-900">
                        {{ \Carbon\Carbon::parse($slip->period_start)->format('M d') }} — {{ \Carbon\Carbon::parse($slip->period_end)->format('M d, Y') }}
                    </td>
                    <td class="hidden sm:table-cell">₱{{ number_format($slip->basic_pay ?? 0, 2) }}</td>
                    <td>₱{{ number_format($slip->gross_pay ?? 0, 2) }}</td>
                    <td class="text-red-600">₱{{ number_format($slip->total_deductions ?? 0, 2) }}</td>
                    <td class="font-semibold text-slate-900">₱{{ number_format($slip->net_pay ?? 0, 2) }}</td>
                    <td>
                        @php
                            $slipColors = [
                                'released' => 'bg-emerald-100 text-emerald-700',
                                'draft' => 'bg-slate-100 text-slate-600',
                                'computed' => 'bg-blue-100 text-blue-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $slipColors[$slip->status] ?? 'bg-emerald-100 text-emerald-700' }}">
                            {{ ucfirst($slip->status ?? 'Released') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('payroll.payslip', $slip) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No released payslips found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($payslips->hasPages())
<div class="mt-4">{{ $payslips->withQueryString()->links() }}</div>
@endif
@endif

@else
{{-- Complaints Tab --}}
<div class="card">
    <div class="card-header"><span>My Complaints</span></div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Subject</th>
                    <th class="hidden sm:table-cell">Period</th>
                    <th>Status</th>
                    <th class="hidden md:table-cell">Filed</th>
                    <th class="hidden md:table-cell">Resolution</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints ?? [] as $c)
                <tr>
                    <td class="font-medium text-slate-900">{{ $c->ticket_code ?? '—' }}</td>
                    <td>
                        <div class="font-medium text-slate-900">{{ $c->subject ?? $c->issue_type }}</div>
                        <div class="text-xs text-slate-400 line-clamp-1">{{ Str::limit($c->description, 60) }}</div>
                    </td>
                    <td class="hidden sm:table-cell text-sm text-slate-500">
                        @if($c->payrollRun)
                        {{ \Carbon\Carbon::parse($c->payrollRun->period_start)->format('M d') }} — {{ \Carbon\Carbon::parse($c->payrollRun->period_end)->format('M d') }}
                        @else
                        —
                        @endif
                    </td>
                    <td>
                        @php
                            $cColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'in_review' => 'bg-blue-100 text-blue-700',
                                'resolved' => 'bg-emerald-100 text-emerald-700',
                                'confirmed' => 'bg-indigo-100 text-indigo-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $cColors[$c->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $c->status)) }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-500">{{ $c->submitted_at ? \Carbon\Carbon::parse($c->submitted_at)->format('M d, Y') : '—' }}</td>
                    <td class="hidden md:table-cell text-sm text-slate-500">{{ Str::limit($c->resolution_notes, 40) ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No complaints filed.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
