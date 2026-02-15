@extends('layouts.app')
@section('title', 'Edit Memo')

@section('content')
<div class="mb-6">
    <a href="{{ route('memos.admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Memo Management</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Edit Memo</h1>
    <p class="text-sm text-slate-500 mt-0.5">{{ $memo->memo_code }}</p>
</div>

<div class="card max-w-3xl">
    <div class="card-header">Memo Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('memos.update', $memo) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="header" class="block text-sm font-medium text-slate-700 required">Subject / Header</label>
                    <input type="text" name="header" id="header" value="{{ old('header', $memo->header) }}" class="input-text mt-1 @error('header') input-error @enderror" required>
                    @error('header') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 required">Content</label>
                    <textarea name="body" id="body" rows="10" class="input-text mt-1 @error('body') input-error @enderror" required>{{ old('body', $memo->body) }}</textarea>
                    @error('body') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 required">Status</label>
                    <select name="status" id="status" class="input-text mt-1">
                        <option value="draft" {{ old('status', $memo->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $memo->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Update Memo</button>
                <a href="{{ route('memos.admin') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
