@extends('layouts.app')

@section('content')
{{-- Hero Header --}}
<div class="rounded-xl bg-gradient-to-br from-slate-900 via-indigo-900 to-blue-900 p-6 text-white shadow-lg mb-6">
    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/75">
        HR Admin
    </div>
    <h1 class="text-2xl font-semibold">Administration Hub</h1>
    <p class="mt-1 text-sm text-white/70">System configuration, management tools, and compliance settings.</p>
</div>

{{-- Module Quick Access --}}
<section class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Module Quick Access</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('employees.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0m6 0a4 4 0 11-6 0"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Employee Management</h3>
            <p class="mt-2 text-sm text-slate-600">Manage employee records, profiles, and employment details.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('departments.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 8h6M8 21V5a2 2 0 012-2h4a2 2 0 012 2v16"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Departments</h3>
            <p class="mt-2 text-sm text-slate-600">Manage departments, sections, and supervisor assignments.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('positions.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Positions & Permissions</h3>
            <p class="mt-2 text-sm text-slate-600">Configure positions and manage access permission assignments.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('payroll.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Payroll Management</h3>
            <p class="mt-2 text-sm text-slate-600">Payroll runs, batches, payslips, and complaint resolution.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('leave.admin') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Leave Management</h3>
            <p class="mt-2 text-sm text-slate-600">Review and manage employee leave requests and balances.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('attendance.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 3"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Attendance & DTR</h3>
            <p class="mt-2 text-sm text-slate-600">Monitor attendance records, manage DTR uploads and imports.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('memos.admin') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 text-violet-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Memos & Announcements</h3>
            <p class="mt-2 text-sm text-slate-600">Create and manage company memos and announcements.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('audit.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Audit Trail</h3>
            <p class="mt-2 text-sm text-slate-600">View system activity, user actions, and change history.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
    </div>
</section>

{{-- System Configuration --}}
<section class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">System Configuration</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.users.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">User Management</h3>
            <p class="mt-2 text-sm text-slate-600">Create, edit, and manage system user accounts and access.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.branches.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Branches</h3>
            <p class="mt-2 text-sm text-slate-600">Manage company branches and office locations.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.payroll-config.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Payroll Configuration</h3>
            <p class="mt-2 text-sm text-slate-600">Overtime rates, allowances, contributions, taxes, and deductions.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open tool</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.cutoff-periods.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Cutoff Periods</h3>
            <p class="mt-2 text-sm text-slate-600">Manage payroll cutoff periods, locking, and scheduling.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.leave-defaults') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5V21l3-3m-3-10.5V3"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Leave Settings</h3>
            <p class="mt-2 text-sm text-slate-600">Configure leave defaults, entitlements, and filing policies.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.work-schedules.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Work Schedules</h3>
            <p class="mt-2 text-sm text-slate-600">Manage schedule templates and employee assignments.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.approval-workflow.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Approval Workflow</h3>
            <p class="mt-2 text-sm text-slate-600">Configure payroll approval chains and approver roles.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.bir-reports.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">BIR Reports</h3>
            <p class="mt-2 text-sm text-slate-600">Form 2316, 1604-C Alphalist, and statutory remittance reports.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.corrections.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Data Corrections</h3>
            <p class="mt-2 text-sm text-slate-600">Review employee data correction requests (RA 10173 compliance).</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.privacy-consents.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">Privacy Consents</h3>
            <p class="mt-2 text-sm text-slate-600">RA 10173 consent tracking dashboard across all users.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
        <a href="{{ route('admin.system.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-lg">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-indigo-600">System Monitor</h3>
            <p class="mt-2 text-sm text-slate-600">Database health, connections, PHP info, and system logs.</p>
            <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600"><span>Open module</span><span class="transition group-hover:translate-x-0.5">&rarr;</span></div>
        </a>
    </div>
</section>
@endsection
