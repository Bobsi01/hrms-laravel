@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center py-16">
    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    </div>
    <h1 class="text-xl font-bold text-slate-900 mb-2">Access Denied</h1>
    <p class="text-sm text-slate-500 mb-6 text-center max-w-md">You don't have the required permissions to access this page. Contact your administrator if you believe this is an error.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
</div>
@endsection
