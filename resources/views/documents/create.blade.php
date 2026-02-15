@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="mb-6">
    <a href="{{ route('documents.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Documents</a>
</div>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Upload Document</h1>
        <p class="text-sm text-slate-500 mt-0.5">Upload a new document and assign visibility.</p>
    </div>
</div>

<div class="card max-w-3xl">
    <div class="card-header">Document Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="input-text w-full @error('title') input-error @enderror" required>
                    @error('title') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 required">Document Type</label>
                    <select name="doc_type" class="input-text w-full @error('doc_type') input-error @enderror" required>
                        <option value="">Select type...</option>
                        <option value="memo" {{ old('doc_type') === 'memo' ? 'selected' : '' }}>Memo</option>
                        <option value="contract" {{ old('doc_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                        <option value="policy" {{ old('doc_type') === 'policy' ? 'selected' : '' }}>Policy</option>
                        <option value="other" {{ old('doc_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('doc_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1 required">File</label>
                <input type="file" name="file" class="input-text w-full @error('file') input-error @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.gif,.webp,.zip" required>
                <p class="text-xs text-slate-400 mt-1">Max 10 MB. Formats: PDF, DOC, DOCX, XLS, XLSX, CSV, TXT, images, ZIP.</p>
                @error('file') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2 required">Assign To</label>
                <div class="flex flex-wrap gap-4" x-data="{ assignType: '{{ old('assign_type', 'global') }}' }">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="assign_type" value="global" x-model="assignType" class="accent-indigo-600">
                        <span class="text-sm text-slate-700">Global (Everyone)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="assign_type" value="employees" x-model="assignType" class="accent-indigo-600">
                        <span class="text-sm text-slate-700">Specific Employees</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="assign_type" value="departments" x-model="assignType" class="accent-indigo-600">
                        <span class="text-sm text-slate-700">Departments</span>
                    </label>

                    {{-- Employee selection --}}
                    <div x-show="assignType === 'employees'" x-cloak class="w-full mt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Employees</label>
                        <div class="border border-slate-200 rounded-lg max-h-48 overflow-y-auto p-2 space-y-1">
                            @foreach($employees as $emp)
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="accent-indigo-600"
                                    {{ in_array($emp->id, old('employee_ids', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">{{ $emp->last_name }}, {{ $emp->first_name }} ({{ $emp->employee_code }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Department selection --}}
                    <div x-show="assignType === 'departments'" x-cloak class="w-full mt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Departments</label>
                        <div class="border border-slate-200 rounded-lg max-h-48 overflow-y-auto p-2 space-y-1">
                            @foreach($departments as $dept)
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}" class="accent-indigo-600"
                                    {{ in_array($dept->id, old('department_ids', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">{{ $dept->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @error('assign_type') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="btn btn-primary">Upload Document</button>
                <a href="{{ route('documents.admin') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
