@extends('layouts.app')
@section('title', 'Leave Management')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Leave Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Review and approve/reject leave requests</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</div>
            <div class="text-xs text-slate-500">Pending</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['approved'] }}</div>
            <div class="text-xs text-slate-500">Approved</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['rejected'] }}</div>
            <div class="text-xs text-slate-500">Rejected</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leave.admin') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee name..." class="input-text flex-1">
            <select name="status" class="input-text w-full sm:w-40">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('leave.admin') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Leave Requests ({{ $leaveRequests->total() }})</div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $leave)
                <tr>
                    <td class="font-medium text-slate-900">{{ $leave->employee->full_name ?? '—' }}</td>
                    <td class="text-sm text-slate-500">{{ $leave->employee->department->name ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}</td>
                    <td>{{ $leave->start_date->format('M d, Y') }}</td>
                    <td>{{ $leave->end_date->format('M d, Y') }}</td>
                    <td>{{ $leave->total_days }}</td>
                    <td>
                        @if($leave->status === 'pending')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Pending</span>
                        @elseif($leave->status === 'approved')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                        @elseif($leave->status === 'rejected')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Rejected</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('leave.show', $leave) }}">View</a>
                            @if($leave->status === 'pending')
                                <form method="POST" action="{{ route('leave.approve', $leave) }}" class="inline" data-confirm="Approve this leave request?">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-sm">Approve</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-slate-500 py-8">No leave requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($leaveRequests->hasPages())
<div class="mt-4">{{ $leaveRequests->withQueryString()->links() }}</div>
@endif
@endsection
