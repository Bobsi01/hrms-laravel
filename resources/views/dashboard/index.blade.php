@extends('layouts.app')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @if(isset($stats['total_employees']))
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0m6 0a4 4 0 11-6 0"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_employees']) }}</div>
            <div class="text-xs text-slate-500">Total Employees</div>
        </div>
    </div>
    @endif

    @if(isset($stats['total_departments']))
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 8h6M8 21V5a2 2 0 012-2h4a2 2 0 012 2v16"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_departments']) }}</div>
            <div class="text-xs text-slate-500">Departments</div>
        </div>
    </div>
    @endif

    @if(isset($stats['pending_leaves']))
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5V21l3-3m-3-10.5V3"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['pending_leaves']) }}</div>
            <div class="text-xs text-slate-500">Pending Leaves</div>
        </div>
    </div>
    @endif

    @if(isset($stats['today_attendance']))
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7l2 2 4-4"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['today_attendance']) }}</div>
            <div class="text-xs text-slate-500">Today's Attendance</div>
        </div>
    </div>
    @endif
</div>

{{-- Quick Actions / Welcome Card --}}
<div class="card">
    <div class="card-header">
        <span>Welcome back, {{ Auth::user()->full_name }}</span>
    </div>
    <div class="card-body">
        <p class="text-sm text-slate-600">Use the sidebar to navigate through the HRMS modules. Your dashboard will display relevant statistics and quick actions based on your access level.</p>
    </div>
</div>
@endsection
