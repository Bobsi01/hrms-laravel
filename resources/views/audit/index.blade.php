@extends('layouts.app')
@section('title', 'Audit Trail')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Audit Trail</h1>
        <p class="text-sm text-slate-500 mt-0.5">System-wide audit log of user actions</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search action or details..." class="input-text">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action type..." class="input-text">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-text" placeholder="From date">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-text" placeholder="To date">
            <div class="flex gap-2">
                <button type="submit" class="btn btn-secondary flex-1">Filter</button>
                @if(request()->hasAny(['search', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('audit.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Audit Logs ({{ $auditLogs->total() }})</div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td class="text-sm text-slate-500 whitespace-nowrap">{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                    <td class="text-sm font-medium text-slate-900">{{ $log->user->full_name ?? 'System' }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">{{ $log->action }}</span>
                    </td>
                    <td class="text-sm text-slate-500 max-w-md truncate">{{ Str::limit($log->details, 120) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-slate-500 py-8">No audit logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($auditLogs->hasPages())
<div class="mt-4">{{ $auditLogs->withQueryString()->links() }}</div>
@endif
@endsection
