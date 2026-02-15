@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('payroll.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Payroll</a>
        <h1 class="text-xl font-bold text-slate-900 mt-2">Payroll Complaints</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage employee payroll complaints</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</div>
            <div class="text-xs text-slate-500">Pending</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['in_review'] }}</div>
            <div class="text-xs text-slate-500">In Review</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['resolved'] }}</div>
            <div class="text-xs text-slate-500">Resolved</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total</div>
        </div>
    </div>
</div>

{{-- Filter tabs --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="flex flex-wrap gap-2">
            @foreach(['open' => 'Open', 'pending' => 'Pending', 'in_review' => 'In Review', 'resolved' => 'Resolved', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('payroll.complaints', ['status' => $key]) }}"
               class="px-3 py-1.5 text-sm rounded-lg font-medium transition {{ $statusFilter === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Employee</th>
                    <th>Subject</th>
                    <th class="hidden md:table-cell">Period</th>
                    <th>Status</th>
                    <th class="hidden md:table-cell">Filed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr>
                    <td class="font-medium text-slate-900">{{ $complaint->ticket_code ?? '—' }}</td>
                    <td>{{ $complaint->employee->full_name ?? '—' }}</td>
                    <td>
                        <div class="font-medium text-slate-900">{{ $complaint->subject ?? $complaint->issue_type }}</div>
                        <div class="text-xs text-slate-400 line-clamp-1">{{ Str::limit($complaint->description, 60) }}</div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-500">
                        @if($complaint->payrollRun)
                        {{ \Carbon\Carbon::parse($complaint->payrollRun->period_start)->format('M d') }} — {{ \Carbon\Carbon::parse($complaint->payrollRun->period_end)->format('M d') }}
                        @else —
                        @endif
                    </td>
                    <td>
                        @php
                            $colors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'in_review' => 'bg-blue-100 text-blue-700',
                                'resolved' => 'bg-emerald-100 text-emerald-700',
                                'confirmed' => 'bg-indigo-100 text-indigo-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $colors[$complaint->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-500">{{ $complaint->submitted_at ? \Carbon\Carbon::parse($complaint->submitted_at)->format('M d, Y') : '—' }}</td>
                    <td>
                        @if(in_array($complaint->status, ['pending', 'in_review']))
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Update</button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 z-10 mt-2 w-72 bg-white rounded-xl shadow-lg border border-slate-200 p-4">
                                <form method="POST" action="{{ route('payroll.complaint-update', $complaint) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">New Status</label>
                                            <select name="status" class="input-text w-full text-sm">
                                                @if($complaint->status === 'pending')
                                                <option value="in_review">In Review</option>
                                                @endif
                                                <option value="resolved">Resolved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Notes</label>
                                            <textarea name="resolution_notes" rows="2" class="input-text w-full text-sm" placeholder="Resolution notes..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary text-sm w-full">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @elseif($complaint->status === 'resolved')
                        <form method="POST" action="{{ route('payroll.complaint-update', $complaint) }}" class="inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium" data-confirm="Confirm this complaint resolution?">Confirm</button>
                        </form>
                        @else
                        <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No complaints found for this filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($complaints->hasPages())
<div class="mt-4">{{ $complaints->withQueryString()->links() }}</div>
@endif
@endsection
