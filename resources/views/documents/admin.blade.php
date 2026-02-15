@extends('layouts.app')

@section('title', 'Document Management')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Document Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage company documents and assignments.</p>
    </div>
    @if($canWrite)
    <div class="flex items-center gap-2">
        <a href="{{ route('documents.create') }}" class="btn btn-primary">+ Upload Document</a>
    </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['memo'] }}</div>
            <div class="text-xs text-slate-500">Memos</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['policy'] }}</div>
            <div class="text-xs text-slate-500">Policies</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['contract'] }}</div>
            <div class="text-xs text-slate-500">Contracts</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['other'] }}</div>
            <div class="text-xs text-slate-500">Other</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('documents.admin') }}" class="flex flex-col sm:flex-row gap-3">
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
                <a href="{{ route('documents.admin') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Documents Table --}}
<div class="card">
    <div class="card-header">
        <span>All Documents ({{ $documents->total() }})</span>
    </div>
    <div class="card-body overflow-x-auto">
        @if($documents->isEmpty())
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No documents found.</p>
            </div>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Assigned To</th>
                        <th>Uploaded By</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
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
                        <td class="text-sm text-slate-500">
                            @if($document->assignments->isEmpty())
                                <span class="text-slate-400">Global (All)</span>
                            @else
                                @foreach($document->assignments->take(3) as $a)
                                    @if($a->employee)
                                        <span class="inline-block px-1.5 py-0.5 text-xs bg-blue-50 text-blue-700 rounded mr-1">{{ $a->employee->first_name }} {{ $a->employee->last_name }}</span>
                                    @elseif($a->department)
                                        <span class="inline-block px-1.5 py-0.5 text-xs bg-emerald-50 text-emerald-700 rounded mr-1">{{ $a->department->name }}</span>
                                    @endif
                                @endforeach
                                @if($document->assignments->count() > 3)
                                    <span class="text-xs text-slate-400">+{{ $document->assignments->count() - 3 }} more</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-sm text-slate-500">{{ $document->creator?->username ?? '—' }}</td>
                        <td class="text-sm text-slate-500">{{ $document->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="action-links">
                                <a href="{{ route('documents.show', $document) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                <a href="{{ route('documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-800">Download</a>
                                @if($canWrite)
                                    <a href="{{ route('documents.edit', $document) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                @endif
                                @if($canManage)
                                    <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline" data-confirm="Are you sure you want to archive this document?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Archive</button>
                                    </form>
                                @endif
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
