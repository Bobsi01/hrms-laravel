@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">System Monitoring</h1>
        <p class="text-sm text-slate-500 mt-0.5">Database statistics, connections, and system health.</p>
    </div>
</div>

{{-- Database Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['db_size'] ?? '—' }}</div>
            <div class="text-xs text-slate-500">Database Size</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['active_connections'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Active Connections</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0v-4a2 2 0 00-2-2h-2a2 2 0 00-2 2v4"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['cache_hit_ratio'] ?? 0, 1) }}%</div>
            <div class="text-xs text-slate-500">Cache Hit Ratio</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['active_users'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Active Users (15 min)</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Connection Details --}}
    <div class="card">
        <div class="card-header"><span>Connection Details</span></div>
        <div class="card-body">
            <table class="table-basic">
                <tbody>
                    <tr><td class="font-medium">Active Connections</td><td>{{ $stats['active_connections'] ?? 0 }}</td></tr>
                    <tr><td class="font-medium">Idle Connections</td><td>{{ $stats['idle_connections'] ?? 0 }}</td></tr>
                    <tr><td class="font-medium">Max Connections</td><td>{{ $stats['max_connections'] ?? '—' }}</td></tr>
                    <tr><td class="font-medium">Database Size</td><td>{{ $stats['db_size'] ?? '—' }}</td></tr>
                    <tr><td class="font-medium">Cache Hit Ratio</td><td>{{ number_format($stats['cache_hit_ratio'] ?? 0, 2) }}%</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- PHP Info --}}
    <div class="card">
        <div class="card-header"><span>PHP Environment</span></div>
        <div class="card-body">
            <table class="table-basic">
                <tbody>
                    <tr><td class="font-medium">PHP Version</td><td>{{ $stats['php_version'] ?? phpversion() }}</td></tr>
                    <tr><td class="font-medium">Laravel Version</td><td>{{ app()->version() }}</td></tr>
                    <tr><td class="font-medium">Memory Limit</td><td>{{ ini_get('memory_limit') }}</td></tr>
                    <tr><td class="font-medium">Max Execution Time</td><td>{{ ini_get('max_execution_time') }}s</td></tr>
                    <tr><td class="font-medium">Upload Max Size</td><td>{{ ini_get('upload_max_filesize') }}</td></tr>
                    <tr><td class="font-medium">Timezone</td><td>{{ config('app.timezone') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent System Logs --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <span>Recent System Activity</span>
            <span class="text-xs text-slate-400">Last 7 days: {{ $stats['logs_week'] ?? 0 }} entries</span>
        </div>
        <div class="card-body">
            @if(!empty($recentLogs))
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, h:i A') }}</td>
                            <td class="font-medium text-sm">{{ $log->action ?? $log->action_type ?? '—' }}</td>
                            <td class="text-sm">{{ $log->user_name ?? '—' }}</td>
                            <td class="text-xs text-slate-400">{{ $log->module ?? '—' }}</td>
                            <td>
                                @if(($log->status ?? '') === 'success')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Success</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-500">{{ $log->status ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400 py-4 text-center">No recent activity logged.</p>
            @endif
        </div>
    </div>
</div>
@endsection
