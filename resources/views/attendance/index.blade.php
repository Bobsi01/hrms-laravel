@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Attendance Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">View and manage employee attendance records</p>
    </div>
    @if($canWrite)
    <div class="flex items-center gap-2">
        <a href="{{ route('attendance.create') }}" class="btn btn-primary">+ Add Entry</a>
        <a href="{{ route('attendance.import') }}" class="btn btn-outline">CSV Import</a>
    </div>
    @endif
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Total Records</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['present'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Present</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['late'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Late</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['absent'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Absent</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['on_leave'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">On Leave</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search Employee</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or employee code..." class="input-text w-full">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="input-text">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="input-text">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th class="hidden sm:table-cell">Time In</th>
                    <th class="hidden sm:table-cell">Time Out</th>
                    <th class="hidden md:table-cell">Overtime</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $record)
                <tr>
                    <td class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                    <td>
                        <div class="font-medium text-slate-900">{{ $record->employee->full_name ?? '—' }}</div>
                        <div class="text-xs text-slate-400">{{ $record->employee->employee_code ?? '' }}</div>
                    </td>
                    <td class="hidden sm:table-cell">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '—' }}</td>
                    <td class="hidden sm:table-cell">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '—' }}</td>
                    <td class="hidden md:table-cell">{{ $record->overtime_minutes ? $record->overtime_minutes . ' min' : '—' }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'present' => 'bg-emerald-100 text-emerald-700',
                                'late' => 'bg-amber-100 text-amber-700',
                                'absent' => 'bg-red-100 text-red-700',
                                'on-leave' => 'bg-blue-100 text-blue-700',
                                'holiday' => 'bg-indigo-100 text-indigo-700',
                                'submitted' => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$record->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($record->status ?? 'N/A') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No attendance records found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($attendances->hasPages())
<div class="mt-4">{{ $attendances->withQueryString()->links() }}</div>
@endif
@endsection
