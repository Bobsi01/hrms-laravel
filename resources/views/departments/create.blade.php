@extends('layouts.app')
@section('title', 'Create Department')

@section('content')
<div class="mb-6">
    <a href="{{ route('departments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Departments</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create Department</h1>
    <p class="text-sm text-slate-500 mt-0.5">Add a new organizational department</p>
</div>

<div class="card max-w-2xl">
    <div class="card-header">Department Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('departments.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 required">Department Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="input-text mt-1 @error('name') input-error @enderror" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="input-text mt-1 @error('description') input-error @enderror">{{ old('description') }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Create Department</button>
                <a href="{{ route('departments.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
