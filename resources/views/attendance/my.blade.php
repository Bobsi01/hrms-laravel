@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Attendance</h1>
        <p class="text-sm text-slate-500 mt-0.5">View your attendance records</p>
    </div>
</div>

@if(!$employee)
<div class="card card-body text-center py-12">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-slate-500">No employee profile linked to your account.</p>
</div>
@else
{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.my') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="input-text w-full">
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="input-text w-full">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('attendance.my') }}" class="btn btn-outline">Clear</a>
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
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th class="hidden sm:table-cell">Overtime</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $record)
                <tr>
                    <td class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                    <td>{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '—' }}</td>
                    <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '—' }}</td>
                    <td class="hidden sm:table-cell">{{ $record->overtime_minutes ? $record->overtime_minutes . ' min' : '—' }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'present' => 'bg-emerald-100 text-emerald-700',
                                'late' => 'bg-amber-100 text-amber-700',
                                'absent' => 'bg-red-100 text-red-700',
                                'on-leave' => 'bg-blue-100 text-blue-700',
                                'holiday' => 'bg-indigo-100 text-indigo-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$record->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($record->status ?? 'N/A') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-8">No attendance records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->hasPages())
<div class="mt-4">{{ $attendances->withQueryString()->links() }}</div>
@endif
@endif
@endsection
