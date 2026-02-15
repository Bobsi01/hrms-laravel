@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Leave Requests</h1>
        <p class="text-sm text-slate-500 mt-0.5">View and file leave requests</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('leave.create') }}" class="btn btn-primary">+ File Leave</a>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Filed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $leave)
                <tr>
                    <td class="font-medium text-slate-900">{{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                    <td>{{ $leave->total_days }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$leave->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td class="text-slate-500 text-sm">{{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No leave requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($leaveRequests, 'links'))
<div class="mt-4">{{ $leaveRequests->withQueryString()->links() }}</div>
@endif
@endsection
