@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Leave Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Review and approve/reject leave requests</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leave.admin') }}" class="flex flex-col sm:flex-row gap-3">
            <select name="status" class="input-text">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td class="font-medium text-slate-900">{{ $leave->employee->full_name ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                    <td>{{ $leave->total_days }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$leave->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">Review</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No leave requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($leaves, 'links'))
<div class="mt-4">{{ $leaves->withQueryString()->links() }}</div>
@endif
@endsection
