@extends('layouts.app')
@section('title', 'Leave Request Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('leave.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Leave Management</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Leave Request Details</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Leave Details --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <span>Leave Request</span>
                @if($leave->status === 'pending')
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Pending</span>
                @elseif($leave->status === 'approved')
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                @elseif($leave->status === 'rejected')
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Rejected</span>
                @endif
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Employee</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->employee->full_name ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Department</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->employee->department->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Leave Type</dt>
                        <dd class="font-medium text-slate-900">{{ ucfirst($leave->leave_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Total Days</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->total_days }} day(s)</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Start Date</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->start_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">End Date</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->end_date->format('M d, Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Reason / Remarks</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->remarks ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Filed On</dt>
                        <dd class="font-medium text-slate-900">{{ $leave->created_at?->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Action History --}}
        @if($leave->actions->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header">Action History</div>
            <div class="card-body">
                <div class="space-y-3">
                    @foreach($leave->actions as $action)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full mt-1.5 {{ $action->action === 'approved' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                        <div>
                            <span class="font-medium text-slate-900">{{ ucfirst($action->action) }}</span>
                            by <span class="font-medium">{{ $action->actor->full_name ?? 'Unknown' }}</span>
                            <span class="text-slate-400">{{ $action->acted_at?->format('M d, Y h:i A') }}</span>
                            @if($action->reason)
                                <p class="text-slate-500 mt-0.5">{{ $action->reason }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Actions Sidebar --}}
    @if($canApprove && $leave->status === 'pending')
    <div>
        <div class="card">
            <div class="card-header">Actions</div>
            <div class="card-body space-y-3">
                <form method="POST" action="{{ route('leave.approve', $leave) }}" data-confirm="Approve this leave request?">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">Approve</button>
                </form>

                <form method="POST" action="{{ route('leave.reject', $leave) }}">
                    @csrf
                    <div class="space-y-2">
                        <textarea name="reason" rows="3" class="input-text w-full" placeholder="Rejection reason..." required></textarea>
                        <button type="submit" class="btn btn-danger w-full">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
