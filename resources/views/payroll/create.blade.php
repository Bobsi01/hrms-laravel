@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('payroll.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Payroll Runs</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create Payroll Run</h1>
    <p class="text-sm text-slate-500 mt-0.5">Set up a new payroll processing run</p>
</div>

<div class="card max-w-3xl">
    <div class="card-header"><span>Run Configuration</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('payroll.store') }}">
            @csrf

            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 required">Period Start</label>
                        <input type="date" name="period_start" value="{{ old('period_start') }}" class="input-text w-full @error('period_start') input-error @enderror" required>
                        @error('period_start') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 required">Period End</label>
                        <input type="date" name="period_end" value="{{ old('period_end') }}" class="input-text w-full @error('period_end') input-error @enderror" required>
                        @error('period_end') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($cutoffPeriods->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                    <p class="font-medium mb-1">Available Cutoff Periods:</p>
                    <ul class="space-y-1 text-blue-700">
                        @foreach($cutoffPeriods->take(5) as $cp)
                        <li>{{ $cp->period_name }}: {{ \Carbon\Carbon::parse($cp->start_date)->format('M d') }} — {{ \Carbon\Carbon::parse($cp->end_date)->format('M d, Y') }}
                            @if($cp->pay_date) (Pay: {{ \Carbon\Carbon::parse($cp->pay_date)->format('M d') }}) @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Run Mode</label>
                    <select name="run_mode" class="input-text w-full @error('run_mode') input-error @enderror" required>
                        <option value="automatic" {{ old('run_mode') === 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="manual" {{ old('run_mode') === 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                    @error('run_mode') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2 required">Select Branches</label>
                    @error('branches') <p class="field-error mb-2">{{ $message }}</p> @enderror
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-3 bg-slate-50 rounded-lg border border-slate-200">
                        @foreach($branches as $branch)
                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white rounded p-1.5">
                            <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                {{ is_array(old('branches')) && in_array($branch->id, old('branches')) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-slate-700">{{ $branch->name }} <span class="text-slate-400">({{ $branch->code }})</span></span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="input-text w-full @error('notes') input-error @enderror" placeholder="Optional notes for this run...">{{ old('notes') }}</textarea>
                    @error('notes') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Create Payroll Run</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
