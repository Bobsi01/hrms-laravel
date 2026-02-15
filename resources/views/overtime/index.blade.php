@extends('layouts.app')
@section('title', 'My Overtime Requests')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Overtime Requests</h1>
        <p class="text-sm text-slate-500 mt-0.5">View and file overtime requests</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('overtime.create') }}" class="btn btn-primary">+ File Overtime</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Overtime History</div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hours</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Approved By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overtimeRequests as $ot)
                <tr>
                    <td class="font-medium text-slate-900">{{ $ot->overtime_date->format('M d, Y') }}</td>
                    <td>{{ number_format($ot->hours_worked, 1) }} hrs</td>
                    <td class="text-sm text-slate-500">{{ Str::limit($ot->reason, 50) }}</td>
                    <td>
                        @if($ot->status === 'pending')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Pending</span>
                        @elseif($ot->status === 'approved')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                        @elseif($ot->status === 'rejected')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Rejected</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-500">{{ $ot->approvedBy->full_name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-8">No overtime requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($overtimeRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $overtimeRequests->hasPages())
<div class="mt-4">{{ $overtimeRequests->links() }}</div>
@endif
@endsection
