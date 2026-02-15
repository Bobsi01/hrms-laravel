@extends('layouts.app')
@section('title', $memo->header)

@section('content')
<div class="mb-6">
    <a href="{{ route('memos.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Memos</a>
</div>

<div class="card max-w-4xl">
    <div class="card-header flex items-center justify-between">
        <span class="text-sm text-slate-500">{{ $memo->memo_code }}</span>
        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $memo->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($memo->status) }}</span>
    </div>
    <div class="card-body">
        <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $memo->header }}</h1>
        <div class="flex items-center gap-3 text-sm text-slate-500 mb-6">
            <span>Issued by <strong>{{ $memo->issued_by_name }}</strong></span>
            @if($memo->issued_by_position)
                <span>&bull;</span>
                <span>{{ $memo->issued_by_position }}</span>
            @endif
            @if($memo->published_at)
                <span>&bull;</span>
                <span>{{ $memo->published_at->format('M d, Y h:i A') }}</span>
            @endif
        </div>

        {{-- Recipients --}}
        @if($memo->recipients->isNotEmpty())
        <div class="mb-4 pb-4 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Recipients:</span>
            <div class="flex flex-wrap gap-2 mt-1">
                @foreach($memo->recipients as $r)
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">{{ $r->audience_label }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Body --}}
        <div class="prose prose-sm max-w-none text-slate-700">
            {!! nl2br(e($memo->body)) !!}
        </div>

        {{-- Attachments --}}
        @if($memo->attachments->isNotEmpty())
        <div class="mt-6 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-medium text-slate-700 mb-2">Attachments</h3>
            <div class="space-y-2">
                @foreach($memo->attachments as $att)
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span class="text-indigo-600">{{ $att->original_name }}</span>
                    <span class="text-slate-400 text-xs">({{ number_format(($att->file_size ?? 0) / 1024, 1) }} KB)</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
