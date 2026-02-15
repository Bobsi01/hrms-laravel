@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="mb-6">
    <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back</a>
</div>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $document->title }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Document details and assignment info.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Details --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">Document Information</div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">Title</dt>
                        <dd class="mt-1 text-sm text-slate-900 font-medium">{{ $document->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">Type</dt>
                        <dd class="mt-1">
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
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">File</dt>
                        <dd class="mt-1 text-sm text-slate-700 font-mono">{{ basename($document->file_path) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">Uploaded By</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $document->creator?->username ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">Created</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $document->created_at?->format('M d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase">Updated</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $document->updated_at?->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Assignments --}}
    <div>
        <div class="card">
            <div class="card-header">Visibility / Assignments</div>
            <div class="card-body">
                @if($document->assignments->isEmpty())
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Global — visible to all employees</span>
                    </div>
                @else
                    <ul class="space-y-2">
                        @foreach($document->assignments as $assignment)
                        <li class="flex items-center gap-2 text-sm">
                            @if($assignment->employee)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}
                                </span>
                            @elseif($assignment->department)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 8h6M8 21V5a2 2 0 012-2h4a2 2 0 012 2v16"/></svg>
                                    {{ $assignment->department->name }}
                                </span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
