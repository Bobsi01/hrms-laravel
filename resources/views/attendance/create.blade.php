@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('attendance.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Attendance</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Add Attendance Entry</h1>
    <p class="text-sm text-slate-500 mt-0.5">Manually add an attendance record for an employee</p>
</div>

<div class="card max-w-2xl">
    <div class="card-header"><span>Attendance Details</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Employee</label>
                    <select name="employee_id" class="input-text w-full @error('employee_id') input-error @enderror" required>
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->last_name }}, {{ $emp->first_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Date</label>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="input-text w-full @error('date') input-error @enderror" required>
                    @error('date') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time In</label>
                        <input type="time" name="time_in" value="{{ old('time_in') }}" class="input-text w-full @error('time_in') input-error @enderror">
                        @error('time_in') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time Out</label>
                        <input type="time" name="time_out" value="{{ old('time_out') }}" class="input-text w-full @error('time_out') input-error @enderror">
                        @error('time_out') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Overtime (minutes)</label>
                    <input type="number" name="overtime_minutes" value="{{ old('overtime_minutes', 0) }}" min="0" class="input-text w-full @error('overtime_minutes') input-error @enderror">
                    @error('overtime_minutes') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Status</label>
                    <select name="status" class="input-text w-full @error('status') input-error @enderror" required>
                        <option value="present" {{ old('status') === 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="on-leave" {{ old('status') === 'on-leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="holiday" {{ old('status') === 'holiday' ? 'selected' : '' }}>Holiday</option>
                    </select>
                    @error('status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Save Entry</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
