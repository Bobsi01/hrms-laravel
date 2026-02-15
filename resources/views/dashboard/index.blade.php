@extends('layouts.app')

@section('content')
{{-- Hero Banner (admin) --}}
@if($isAdmin)
<div class="rounded-xl bg-gradient-to-br from-slate-900 via-indigo-900 to-blue-900 p-6 text-white shadow-lg mb-6">
    <div class="mb-4">
        <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/75">
            HR Admin Dashboard
        </div>
        <h1 class="text-2xl font-semibold">Welcome back, {{ Auth::user()->full_name }}</h1>
        <p class="mt-1 text-sm text-white/70">Coordinate HR operations from one hub. Keep payroll, leave, and compliance tasks moving.</p>
    </div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        @if(isset($stats['total_employees']))
        <div class="rounded-lg bg-white/10 p-3">
            <div class="text-xs text-white/60">Active Employees</div>
            <div class="mt-1 text-sm font-semibold">{{ number_format($stats['total_employees']) }}</div>
        </div>
        @endif
        @if(isset($stats['today_attendance']))
        <div class="rounded-lg bg-white/10 p-3">
            <div class="text-xs text-white/60">Today's Attendance</div>
            <div class="mt-1 text-sm font-semibold">{{ number_format($stats['today_attendance']) }}</div>
        </div>
        @endif
        @if(isset($stats['pending_leaves']))
        <div class="rounded-lg bg-white/10 p-3">
            <div class="text-xs text-white/60">Pending Leave</div>
            <div class="mt-1 text-sm font-semibold">{{ number_format($stats['pending_leaves']) }}</div>
        </div>
        @endif
        @if(isset($stats['open_payroll']))
        <div class="rounded-lg bg-white/10 p-3">
            <div class="text-xs text-white/60">Payroll Runs Open</div>
            <div class="mt-1 text-sm font-semibold">{{ number_format($stats['open_payroll']) }}</div>
        </div>
        @endif
        @if(isset($stats['system_events_24h']))
        <div class="rounded-lg bg-white/10 p-3">
            <div class="text-xs text-white/60">System Events (24h)</div>
            <div class="mt-1 text-sm font-semibold">{{ number_format($stats['system_events_24h']) }}</div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Stat Cards (non-admin or additional display) --}}
@if(!$isAdmin)
<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">Welcome, {{ Auth::user()->full_name }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Your HRIS dashboard</p>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @if(isset($stats['total_employees']))
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0m6 0a4 4 0 11-6 0"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_employees']) }}</div>
            <div class="text-xs text-slate-500">Active Employees</div>
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
            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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

@if($isAdmin)
{{-- Operational Snapshot --}}
@if(isset($stats['pending_overtime']) || isset($stats['open_payroll']))
<section class="mb-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">Operational Snapshot</h2>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @if(isset($stats['open_payroll']))
        <a href="{{ route('payroll.index') }}" class="group rounded-lg border border-indigo-200 bg-white p-4 shadow-sm transition hover:shadow-md">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Open Payroll</div>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['open_payroll'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Runs requiring attention</p>
        </a>
        @endif
        @if(isset($stats['pending_leaves']))
        <a href="{{ route('leave.admin') }}" class="group rounded-lg border border-amber-200 bg-white p-4 shadow-sm transition hover:shadow-md">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Pending Leave</div>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['pending_leaves'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Requests awaiting review</p>
        </a>
        @endif
        @if(isset($stats['pending_overtime']))
        <a href="{{ route('overtime.admin') }}" class="group rounded-lg border border-emerald-200 bg-white p-4 shadow-sm transition hover:shadow-md">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Pending Overtime</div>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['pending_overtime'] }}</p>
            <p class="mt-1 text-xs text-slate-500">OT filings to process</p>
        </a>
        @endif
        @if(isset($stats['total_employees']))
        <a href="{{ route('employees.index') }}" class="group rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600">Workforce</div>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['total_employees'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Active employees</p>
        </a>
        @endif
    </div>
</section>
@endif

{{-- 3-Column Section: Action Center, Quick Links, System Pulse --}}
<section class="grid gap-4 lg:grid-cols-3 mb-6">
    {{-- Action Center --}}
    <div class="card p-5">
        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">Action Center</h2>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Prioritize today</span>
        </div>
        <p class="text-xs text-slate-500">Top operational items that benefit from an early look.</p>
        <ul class="mt-3 space-y-2">
            @forelse($actionItems as $item)
            <li class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                <a class="flex items-start justify-between gap-3" href="{{ $item['route'] }}">
                    <div>
                        <p class="text-xs font-semibold text-slate-900">{{ $item['label'] }}</p>
                        <p class="text-[11px] text-slate-500">{{ $item['description'] }}</p>
                    </div>
                    <span class="inline-flex min-w-[2.5rem] justify-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600">{{ number_format($item['count']) }}</span>
                </a>
            </li>
            @empty
            <li class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-xs text-slate-500 text-center">
                All caught up! No pressing items.
            </li>
            @endforelse
        </ul>
    </div>

    {{-- Quick Links --}}
    <div class="card p-5">
        <h2 class="mb-1 text-base font-semibold text-slate-900">Quick Links</h2>
        <p class="text-xs text-slate-500">Shortcuts to modules you use most.</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach($quickLinks as $link)
            <a class="group flex h-full flex-col justify-between rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:border-indigo-300 hover:shadow-md" href="{{ $link['route'] }}">
                <div>
                    <span class="block text-xs font-semibold text-slate-900 transition group-hover:text-indigo-600">{{ $link['label'] }}</span>
                    <span class="mt-0.5 block text-[11px] text-slate-500">{{ $link['description'] }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- System Pulse --}}
    <div class="card p-5">
        <h2 class="mb-1 text-base font-semibold text-slate-900">System Pulse</h2>
        <p class="text-xs text-slate-500">Latest system events in the audit trail.</p>
        <ul class="mt-3 space-y-2">
            @forelse($systemPulse as $log)
            <li class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span class="font-mono text-[10px] text-indigo-600">{{ strtoupper($log->action ?? 'LOG') }}</span>
                    <span>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('h:i A') : '' }}</span>
                </div>
                <p class="mt-1.5 text-xs font-medium text-slate-900">{{ $log->module ?? 'system' }}</p>
                <p class="mt-0.5 text-[11px] text-slate-600">{{ Str::limit($log->details ?? '', 110) }}</p>
            </li>
            @empty
            <li class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-xs text-slate-500 text-center">
                No system events logged recently.
            </li>
            @endforelse
        </ul>
        <div class="mt-3 text-right">
            <a class="inline-flex items-center text-[11px] font-semibold text-indigo-600" href="{{ route('audit.index') }}">View audit trail &rarr;</a>
        </div>
    </div>
</section>
@endif

{{-- Self-service section for regular employees --}}
@if(!$isAdmin && isset($selfService['has_employee']))
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="{{ route('leave.index') }}" class="card card-body hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-900">My Leave</div>
                <div class="text-xs text-slate-500">{{ $selfService['pending_leaves'] ?? 0 }} pending requests</div>
            </div>
        </div>
    </a>
    <a href="{{ route('attendance.my') }}" class="card card-body hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-900">My Attendance</div>
                <div class="text-xs text-slate-500">View your attendance records</div>
            </div>
        </div>
    </a>
    <a href="{{ route('payroll.my-payslips') }}" class="card card-body hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-900">My Payslips</div>
                <div class="text-xs text-slate-500">View your payslip history</div>
            </div>
        </div>
    </a>
</div>
@endif
@endsection
