@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Payslips</h1>
        <p class="text-sm text-slate-500 mt-0.5">View your payslip history</p>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Period</th>
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
                    <td class="font-medium text-slate-900">{{ $slip->pay_period ?? '—' }}</td>
                    <td>₱{{ number_format($slip->gross_pay ?? 0, 2) }}</td>
                    <td>₱{{ number_format($slip->total_deductions ?? 0, 2) }}</td>
                    <td class="font-semibold text-slate-900">₱{{ number_format($slip->net_pay ?? 0, 2) }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">
                            {{ ucfirst($slip->status ?? 'issued') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No payslips found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($payslips, 'links'))
<div class="mt-4">{{ $payslips->withQueryString()->links() }}</div>
@endif
@endsection
