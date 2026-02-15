@extends('layouts.app')
@section('title', 'Memos & Announcements')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Memos & Announcements</h1>
        <p class="text-sm text-slate-500 mt-0.5">Company-wide memos and announcements</p>
    </div>
</div>

<div class="space-y-4">
    @forelse($memos as $memo)
    <div class="card">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div class="flex-1">
                    <a href="{{ route('memos.show', $memo) }}" class="text-lg font-semibold text-indigo-600 hover:text-indigo-800">{{ $memo->header }}</a>
                    <div class="flex items-center gap-3 mt-1 text-sm text-slate-500">
                        <span>{{ $memo->memo_code }}</span>
                        <span>&bull;</span>
                        <span>By {{ $memo->issued_by_name ?? 'Unknown' }}</span>
                        @if($memo->published_at)
                            <span>&bull;</span>
                            <span>{{ $memo->published_at->format('M d, Y h:i A') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <p class="text-sm text-slate-600 mt-3">{{ Str::limit(strip_tags($memo->body), 200) }}</p>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-12">
            <p class="text-slate-500">No memos published yet.</p>
        </div>
    </div>
    @endforelse
</div>

@if($memos->hasPages())
<div class="mt-4">{{ $memos->links() }}</div>
@endif
@endsection
