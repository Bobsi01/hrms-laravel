@extends('layouts.app')

@section('title', 'Recruitment Pipeline')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Recruitment Pipeline</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage applicants and track hiring progress.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('recruitment.export-csv') }}" class="btn btn-outline text-sm" data-no-loader>
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Export CSV
        </a>
        @if($canWrite)
        <a href="{{ route('recruitment.create') }}" class="btn btn-primary">+ Add Applicant</a>
        @endif
    </div>
</div>

{{-- Pipeline Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0m6 0a4 4 0 11-6 0"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['new'] }}</div>
            <div class="text-xs text-slate-500">Pending</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['shortlist'] }}</div>
            <div class="text-xs text-slate-500">Shortlist</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['interviewed'] }}</div>
            <div class="text-xs text-slate-500">Interviewed</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['hired'] }}</div>
            <div class="text-xs text-slate-500">Hired</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['rejected'] }}</div>
            <div class="text-xs text-slate-500">Rejected</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('recruitment.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or position..." class="input-text w-full">
            </div>
            <div>
                <select name="status" class="input-text">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Pending</option>
                    <option value="shortlist" {{ request('status') === 'shortlist' ? 'selected' : '' }}>Shortlist</option>
                    <option value="interviewed" {{ request('status') === 'interviewed' ? 'selected' : '' }}>Interviewed</option>
                    <option value="hired" {{ request('status') === 'hired' ? 'selected' : '' }}>Hired</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('recruitment.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Applicants Table --}}
<div class="card">
    <div class="card-header">
        <span>Applicants ({{ $applicants->total() }})</span>
    </div>
    <div class="card-body overflow-x-auto">
        @if($applicants->isEmpty())
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No applicants found.</p>
            </div>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicants as $app)
                    @php
                        $statusColors = [
                            'new' => 'bg-blue-100 text-blue-700',
                            'shortlist' => 'bg-amber-100 text-amber-700',
                            'interviewed' => 'bg-indigo-100 text-indigo-700',
                            'hired' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'new' => 'Pending',
                            'shortlist' => 'For Final Interview',
                            'interviewed' => 'Interviewed',
                            'hired' => 'Hired',
                            'rejected' => 'Rejected',
                        ];
                        $isConverted = !empty($app->converted_employee_id) && $app->converted_employee_id > 0;
                    @endphp
                    <tr>
                        <td class="font-medium text-slate-900">{{ $app->full_name }}</td>
                        <td class="text-sm text-slate-500">{{ $app->email ?? '—' }}</td>
                        <td class="text-sm text-slate-700">{{ $app->position_applied ?? '—' }}</td>
                        <td>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$app->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $statusLabels[$app->status] ?? ucfirst($app->status) }}
                            </span>
                        </td>
                        <td class="text-sm text-slate-500">{{ $app->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="action-links">
                                <a href="{{ route('recruitment.show', $app) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                @if($canWrite && !$isConverted)
                                <form method="POST" action="{{ route('recruitment.update-status', $app) }}" class="inline">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="text-xs border-slate-200 rounded py-0.5 px-1">
                                        <option value="" disabled selected>Set status</option>
                                        @foreach(['new', 'shortlist', 'interviewed', 'rejected'] as $s)
                                            @if($s !== $app->status)
                                            <option value="{{ $s }}">{{ $statusLabels[$s] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
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

@if($applicants->hasPages())
    <div class="mt-4">{{ $applicants->links() }}</div>
@endif
@endsection
