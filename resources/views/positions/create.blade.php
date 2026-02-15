@extends('layouts.app')
@section('title', 'Create Position')

@section('content')
<div class="mb-6">
    <a href="{{ route('positions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Positions</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create Position</h1>
    <p class="text-sm text-slate-500 mt-0.5">Add a new job position</p>
</div>

<div class="card max-w-2xl">
    <div class="card-header">Position Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('positions.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 required">Position Title</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="input-text mt-1 @error('name') input-error @enderror" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-slate-700 required">Department</label>
                    <select name="department_id" id="department_id" class="input-text mt-1 @error('department_id') input-error @enderror" required>
                        <option value="">Select department…</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="base_salary" class="block text-sm font-medium text-slate-700">Base Salary</label>
                    <input type="number" name="base_salary" id="base_salary" value="{{ old('base_salary') }}" step="0.01" min="0" class="input-text mt-1 @error('base_salary') input-error @enderror">
                    @error('base_salary') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="input-text mt-1 @error('description') input-error @enderror">{{ old('description') }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Create Position</button>
                <a href="{{ route('positions.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
