@extends('layouts.app')

@section('title', 'Add Applicant')

@section('content')
<div class="mb-6">
    <a href="{{ route('recruitment.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Pipeline</a>
</div>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Add New Applicant</h1>
        <p class="text-sm text-slate-500 mt-0.5">Enter applicant information to add them to the recruitment pipeline.</p>
    </div>
</div>

<div class="card max-w-3xl">
    <div class="card-header">Applicant Information</div>
    <div class="card-body">
        <form method="POST" action="{{ route('recruitment.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Recruitment Template</label>
                <select name="template_id" class="input-text w-full">
                    <option value="">No template</option>
                    @foreach($templates as $tmpl)
                        <option value="{{ $tmpl->id }}" {{ old('template_id') == $tmpl->id ? 'selected' : '' }}>{{ $tmpl->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Selecting a template determines which fields and files are required.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Full Name</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" class="input-text w-full @error('full_name') input-error @enderror" required>
                    @error('full_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-text w-full @error('email') input-error @enderror">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="input-text w-full @error('phone') input-error @enderror">
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Position Applied</label>
                    <input type="text" name="position_applied" value="{{ old('position_applied') }}" class="input-text w-full @error('position_applied') input-error @enderror">
                    @error('position_applied') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="input-text w-full @error('notes') input-error @enderror">{{ old('notes') }}</textarea>
                @error('notes') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="btn btn-primary">Add Applicant</button>
                <a href="{{ route('recruitment.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
