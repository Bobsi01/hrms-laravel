@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Payroll Runs</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage payroll processing and runs</p>
    </div>
    <div class="flex items-center gap-2">
        @if($canWrite)
        <a href="{{ route('payroll.create') }}" class="btn btn-primary">+ New Payroll Run</a>
        @endif
        @if($canManage)
        <a href="{{ route('payroll.complaints') }}" class="btn btn-outline">
            Complaints
            @if($openComplaints > 0)
            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $openComplaints }}</span>
            @endif
        </a>
        @endif
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total Runs</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['draft'] }}</div>
            <div class="text-xs text-slate-500">Draft</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['processing'] }}</div>
            <div class="text-xs text-slate-500">Processing</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['awaiting_approval'] }}</div>
            <div class="text-xs text-slate-500">Awaiting Approval</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['released'] }}</div>
            <div class="text-xs text-slate-500">Released</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search runs..." class="input-text w-full">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="input-text">
                    <option value="">All Statuses</option>
                    @foreach(['draft', 'for_review', 'approved', 'submitted', 'released', 'for_revision', 'rejected', 'closed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Run #</th>
                    <th>Period</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th class="hidden md:table-cell">Initiated By</th>
                    <th class="hidden md:table-cell">Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollRuns as $run)
                <tr>
                    <td class="font-medium text-slate-900">#{{ $run->id }}</td>
                    <td>
                        @if($run->period_start && $run->period_end)
                        {{ \Carbon\Carbon::parse($run->period_start)->format('M d') }} — {{ \Carbon\Carbon::parse($run->period_end)->format('M d, Y') }}
                        @else
                        —
                        @endif
                    </td>
                    <td><span class="text-sm text-slate-600">{{ ucfirst($run->run_mode ?? '—') }}</span></td>
                    <td>
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
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$run->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $run->status ?? 'N/A')) }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-500">{{ $run->generatedBy->full_name ?? '—' }}</td>
                    <td class="hidden md:table-cell text-sm text-slate-500">{{ \Carbon\Carbon::parse($run->created_at)->format('M d, Y') }}</td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('payroll.show', $run) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No payroll runs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($payrollRuns->hasPages())
<div class="mt-4">{{ $payrollRuns->withQueryString()->links() }}</div>
@endif
@endsection
