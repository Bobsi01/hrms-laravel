@extends('layouts.app')

@section('title', 'My Documents')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Documents</h1>
        <p class="text-sm text-slate-500 mt-0.5">Documents assigned to you, your department, or company-wide.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('documents.export-csv') }}" class="btn btn-outline text-sm" data-no-loader>
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Export CSV
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('documents.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or type..." class="input-text w-full">
            </div>
            <div>
                <select name="type" class="input-text">
                    <option value="">All Types</option>
                    <option value="memo" {{ request('type') === 'memo' ? 'selected' : '' }}>Memo</option>
                    <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="policy" {{ request('type') === 'policy' ? 'selected' : '' }}>Policy</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Documents Table --}}
<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>Documents ({{ $documents->total() }})</span>
    </div>
    <div class="card-body overflow-x-auto">
        @if($documents->isEmpty())
            <div class="text-center py-8 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <p class="text-sm">No documents found.</p>
            </div>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $document->title }}</td>
                        <td>
                            @php
                                $typeColors = [
                                    'memo' => 'bg-indigo-100 text-indigo-700',
                                    'contract' => 'bg-emerald-100 text-emerald-700',
                                    'policy' => 'bg-amber-100 text-amber-700',
                                    'other' => 'bg-slate-100 text-slate-700',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$document->doc_type] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($document->doc_type) }}
                            </span>
                        </td>
                        <td class="text-sm text-slate-500">{{ $document->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="action-links">
                                <a href="{{ route('documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Open
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@if($documents->hasPages())
    <div class="mt-4">{{ $documents->links() }}</div>
@endif
@endsection
