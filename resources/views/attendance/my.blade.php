@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Attendance</h1>
        <p class="text-sm text-slate-500 mt-0.5">View your attendance records</p>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Hours Worked</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendance as $record)
                <tr>
                    <td class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                    <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : '—' }}</td>
                    <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : '—' }}</td>
                    <td>{{ $record->hours_worked ? number_format($record->hours_worked, 1) . 'h' : '—' }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ ($record->status ?? '') === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
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

@if(method_exists($attendance, 'links'))
<div class="mt-4">{{ $attendance->withQueryString()->links() }}</div>
@endif
@endsection
