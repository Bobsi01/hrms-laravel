@extends('layouts.app')
@section('title', 'File Overtime Request')

@section('content')
<div class="mb-6">
    <a href="{{ route('overtime.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Overtime Requests</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">File Overtime Request</h1>
    <p class="text-sm text-slate-500 mt-0.5">Submit a new overtime request for approval</p>
</div>

<div class="card max-w-2xl">
    <div class="card-header">Overtime Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('overtime.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="overtime_date" class="block text-sm font-medium text-slate-700 required">Overtime Date</label>
                    <input type="date" name="overtime_date" id="overtime_date" value="{{ old('overtime_date') }}" class="input-text mt-1 @error('overtime_date') input-error @enderror" required>
                    @error('overtime_date') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="hours" class="block text-sm font-medium text-slate-700 required">Number of Hours</label>
                    <input type="number" name="hours" id="hours" value="{{ old('hours') }}" step="0.5" min="0.5" max="24" class="input-text mt-1 @error('hours') input-error @enderror" required>
                    @error('hours') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-slate-700 required">Reason / Justification</label>
                    <textarea name="reason" id="reason" rows="4" class="input-text mt-1 @error('reason') input-error @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="{{ route('overtime.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
