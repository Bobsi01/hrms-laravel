@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Notifications</h1>
        <p class="text-sm text-slate-500 mt-0.5">Your notification center</p>
    </div>
</div>

<div class="space-y-2">
    @forelse($notifications as $notif)
    <div class="card card-body flex items-start gap-3 {{ !$notif->is_read ? 'border-l-4 border-l-indigo-400' : '' }}">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
            {{ !$notif->is_read ? 'bg-indigo-100' : 'bg-slate-100' }}">
            <svg class="w-4 h-4 {{ !$notif->is_read ? 'text-indigo-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-slate-900">{{ $notif->title }}</div>
            <div class="text-sm text-slate-600 mt-0.5">{{ $notif->message }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</div>
        </div>
    </div>
    @empty
    <div class="card card-body text-center py-12">
        <div class="text-slate-400 mb-2">
            <svg class="w-12 h-12 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <p class="text-sm text-slate-500">You're all caught up! No notifications yet.</p>
    </div>
    @endforelse
</div>

@if(method_exists($notifications, 'links'))
<div class="mt-4">{{ $notifications->withQueryString()->links() }}</div>
@endif
@endsection
