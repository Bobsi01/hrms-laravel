@extends('layouts.app')
@section('title', 'Create Memo')

@section('content')
<div class="mb-6">
    <a href="{{ route('memos.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Memo Management</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create Memo</h1>
    <p class="text-sm text-slate-500 mt-0.5">Create a new company memo or announcement</p>
</div>

<div class="card max-w-3xl">
    <div class="card-header">Memo Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('memos.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="header" class="block text-sm font-medium text-slate-700 required">Subject / Header</label>
                    <input type="text" name="header" id="header" value="{{ old('header') }}" class="input-text mt-1 @error('header') input-error @enderror" required>
                    @error('header') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 required">Content</label>
                    <textarea name="body" id="body" rows="10" class="input-text mt-1 @error('body') input-error @enderror" required>{{ old('body') }}</textarea>
                    @error('body') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="audience_type" class="block text-sm font-medium text-slate-700 required">Recipients</label>
                    <select name="audience_type" id="audience_type" class="input-text mt-1 @error('audience_type') input-error @enderror" required onchange="toggleDeptSelect(this.value)">
                        <option value="all" {{ old('audience_type') === 'all' ? 'selected' : '' }}>All Employees</option>
                        <option value="department" {{ old('audience_type') === 'department' ? 'selected' : '' }}>Specific Departments</option>
                    </select>
                    @error('audience_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div id="deptSelectWrapper" class="{{ old('audience_type') === 'department' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700">Select Departments</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-lg">
                        @foreach($departments as $dept)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                {{ in_array($dept->id, old('department_ids', [])) ? 'checked' : '' }}>
                            {{ $dept->name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="allow_downloads" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('allow_downloads') ? 'checked' : '' }}>
                        Allow attachment downloads
                    </label>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 required">Status</label>
                    <select name="status" id="status" class="input-text mt-1">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Publish Now</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Create Memo</button>
                <a href="{{ route('memos.admin') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleDeptSelect(val) {
    document.getElementById('deptSelectWrapper').classList.toggle('hidden', val !== 'department');
}
</script>
@endpush
@endsection
