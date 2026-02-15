@extends('layouts.app')
@section('title', 'Memo Management')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Memo Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create and manage company memos</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('memos.create') }}" class="btn btn-primary">+ Create Memo</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('memos.admin') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search memos..." class="input-text flex-1">
            <select name="status" class="input-text w-full sm:w-40">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('memos.admin') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">All Memos ({{ $memos->total() }})</div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Issued By</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($memos as $memo)
                <tr>
                    <td class="text-sm text-slate-500">{{ $memo->memo_code }}</td>
                    <td class="font-medium text-slate-900">{{ Str::limit($memo->header, 50) }}</td>
                    <td class="text-sm text-slate-500">{{ $memo->issued_by_name ?? $memo->issuer?->full_name ?? '—' }}</td>
                    <td>
                        @if($memo->status === 'published')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Published</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Draft</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-500">{{ $memo->published_at?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('memos.show', $memo) }}">View</a>
                            <a href="{{ route('memos.edit', $memo) }}">Edit</a>
                            <form method="POST" action="{{ route('memos.destroy', $memo) }}" class="inline" data-confirm="Delete this memo?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No memos found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($memos->hasPages())
<div class="mt-4">{{ $memos->withQueryString()->links() }}</div>
@endif
@endsection
