@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Privacy Consent Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">RA 10173 consent tracking across all users ({{ $totalUsers }} total users)</p>
    </div>
</div>

{{-- Stat cards per consent type --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @foreach($stats as $type => $data)
    <div class="card card-body">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">{{ $data['label'] }}</h3>
            @if($data['required'])
            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Required</span>
            @else
            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Optional</span>
            @endif
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <div class="text-lg font-bold text-emerald-600">{{ $data['consented'] }}</div>
                <div class="text-xs text-slate-500">Consented</div>
            </div>
            <div>
                <div class="text-lg font-bold text-red-600">{{ $data['declined'] }}</div>
                <div class="text-xs text-slate-500">Declined</div>
            </div>
            <div>
                <div class="text-lg font-bold text-amber-600">{{ $data['pending'] }}</div>
                <div class="text-xs text-slate-500">No Response</div>
            </div>
        </div>
        @if($totalUsers > 0)
        <div class="mt-3">
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden flex">
                @php $pctConsented = round($data['consented'] / $totalUsers * 100); @endphp
                @php $pctDeclined = round($data['declined'] / $totalUsers * 100); @endphp
                <div class="bg-emerald-500 h-full" style="width: {{ $pctConsented }}%"></div>
                <div class="bg-red-400 h-full" style="width: {{ $pctDeclined }}%"></div>
            </div>
            <div class="text-xs text-slate-400 mt-1">{{ $pctConsented }}% compliance rate</div>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Recent Activity --}}
<div class="card">
    <div class="card-header">
        <span>Recent Consent Activity</span>
    </div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Consent Type</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity as $consent)
                <tr>
                    <td class="font-medium text-slate-900">{{ $consent->user?->name ?? 'Unknown' }}</td>
                    <td>{{ $consentTypes[$consent->consent_type]['label'] ?? ucfirst($consent->consent_type) }}</td>
                    <td>
                        @if($consent->consented)
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Consented</span>
                        @else
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Withdrawn</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-500">{{ $consent->ip_address ?? '—' }}</td>
                    <td class="text-sm text-slate-500 whitespace-nowrap">{{ $consent->updated_at->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-8">No consent activity recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
