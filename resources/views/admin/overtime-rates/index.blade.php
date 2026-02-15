@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Overtime Rate Configuration</h1>
        <p class="text-sm text-slate-500 mt-0.5">Set multiplier rates for various overtime types used in payroll computation.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.overtime-rates.update') }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($rates as $code => $rate)
        <div class="card card-body">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-slate-800 text-sm">{{ $rate['label'] }}</div>
                    <div class="text-xs text-slate-400">{{ $code }}</div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Multiplier Rate</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="rates[{{ $code }}]" class="input-text" value="{{ $rate['value'] }}" step="0.01" min="0" required>
                    <span class="text-sm text-slate-400">×</span>
                </div>
                @if($rate['description'] ?? false)
                <p class="text-xs text-slate-400 mt-1">{{ $rate['description'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="btn btn-primary">Save All Rates</button>
    </div>
</form>
@endsection
