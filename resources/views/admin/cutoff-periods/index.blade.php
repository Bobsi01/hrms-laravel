@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Cutoff Periods</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage payroll cutoff periods and schedules.</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('populateModal').classList.remove('hidden')" class="btn btn-outline text-sm">Auto-Populate</button>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Create Period</button>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total Periods</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</div>
            <div class="text-xs text-slate-500">Active Periods</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $nextCutoff ? \Carbon\Carbon::parse($nextCutoff)->format('M d') : '—' }}</div>
            <div class="text-xs text-slate-500">Next Cutoff</div>
        </div>
    </div>
</div>

{{-- Periods Table --}}
<div class="card">
    <div class="card-header"><span>All Cutoff Periods</span></div>
    <div class="card-body">
        @if($periods->isEmpty())
            <p class="text-sm text-slate-500 py-4 text-center">No cutoff periods defined.</p>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Period Name</th>
                        <th>Date Range</th>
                        <th>Cutoff Date</th>
                        <th>Pay Date</th>
                        <th>Status</th>
                        <th>Lock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periods as $p)
                    <tr>
                        <td class="font-medium">{{ $p->period_name }}</td>
                        <td class="text-sm text-slate-500">{{ $p->start_date->format('M d') }} – {{ $p->end_date->format('M d, Y') }}</td>
                        <td>{{ $p->cutoff_date->format('M d, Y') }}</td>
                        <td>{{ $p->pay_date->format('M d, Y') }}</td>
                        <td>
                            @if($p->status === 'active')
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
                            @elseif($p->status === 'closed')
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Closed</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_locked)
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/></svg>
                            @endif
                        </td>
                        <td>
                            <div class="action-links">
                                @if($p->status === 'active')
                                <form method="POST" action="{{ route('admin.cutoff-periods.close', $p) }}" class="inline" data-confirm="Close this period?">
                                    @csrf
                                    <button type="submit">Close</button>
                                </form>
                                <form method="POST" action="{{ route('admin.cutoff-periods.toggle-lock', $p) }}" class="inline">
                                    @csrf
                                    <button type="submit">{{ $p->is_locked ? 'Unlock' : 'Lock' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.cutoff-periods.cancel', $p) }}" class="inline" data-confirm="Cancel this period?">
                                    @csrf
                                    <button type="submit" class="text-red-600">Cancel</button>
                                </form>
                                @endif
                                @if($p->status !== 'active')
                                <form method="POST" action="{{ route('admin.cutoff-periods.destroy', $p) }}" class="inline" data-confirm="Delete this period permanently?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $periods->links() }}</div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Create Cutoff Period</h3>
        <form method="POST" action="{{ route('admin.cutoff-periods.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Period Name</label>
                    <input type="text" name="period_name" class="input-text mt-1" required placeholder="e.g. February 2026 - Cutoff 1">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Start Date</label>
                        <input type="date" name="start_date" class="input-text mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">End Date</label>
                        <input type="date" name="end_date" class="input-text mt-1" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Cutoff Date</label>
                        <input type="date" name="cutoff_date" class="input-text mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Pay Date</label>
                        <input type="date" name="pay_date" class="input-text mt-1" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea name="notes" class="input-text mt-1" rows="2"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Create Period</button>
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Auto-Populate Modal --}}
<div id="populateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Auto-Populate Cutoff Periods</h3>
        <p class="text-sm text-slate-500 mb-4">Generates bi-monthly cutoff periods (Philippine payroll schedule).</p>
        <form method="POST" action="{{ route('admin.cutoff-periods.populate') }}">
            @csrf
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Start Year</label>
                        <input type="number" name="start_year" class="input-text mt-1" value="{{ now()->year }}" required min="2020" max="2040">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Start Month</label>
                        <select name="start_month" class="input-text mt-1" required>
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m === now()->month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Number of Months</label>
                    <input type="number" name="months_count" class="input-text mt-1" value="6" required min="1" max="24">
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <button type="button" onclick="document.getElementById('populateModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
