@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Leave Entitlements &amp; Policies</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage individual employee leave balances and filing policies.</p>
    </div>
    <div>
        <a href="{{ route('admin.leave-defaults') }}" class="btn btn-outline text-sm">← Default Settings</a>
    </div>
</div>

{{-- Tabs --}}
<div class="flex border-b border-slate-200 mb-6">
    <button onclick="switchEntitlementTab('balances')" id="tabBalances" class="px-4 py-2 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">Employee Balances</button>
    <button onclick="switchEntitlementTab('policies')" id="tabPolicies" class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700">Filing Policies</button>
</div>

{{-- Balances Tab --}}
<div id="panelBalances">
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <span>Employee Leave Balances ({{ now()->year }})</span>
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee..." class="input-text text-sm w-48">
                <button type="submit" class="btn btn-outline text-sm">Search</button>
            </form>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Employee</th>
                        @foreach($leaveTypes as $type => $label)
                        <th class="text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                            <div class="text-xs text-slate-400">{{ $emp->department_name ?? '—' }}</div>
                        </td>
                        @foreach($leaveTypes as $type => $label)
                        @php
                            $bal = $balances[$emp->id][$type] ?? null;
                            $entitled = $bal['entitled'] ?? 0;
                            $used = $bal['used'] ?? 0;
                            $remaining = $entitled - $used;
                        @endphp
                        <td class="text-center">
                            <div class="text-sm font-medium {{ $remaining <= 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $remaining }}</div>
                            <div class="text-xs text-slate-400">{{ $used }}/{{ $entitled }}</div>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ count($leaveTypes) + 1 }}" class="text-center text-slate-400 py-4">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($employees instanceof \Illuminate\Pagination\AbstractPaginator && $employees->hasPages())
            <div class="mt-4">{{ $employees->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Policies Tab --}}
<div id="panelPolicies" class="hidden">
    <div class="card">
        <div class="card-header"><span>Leave Filing Policies</span></div>
        <div class="card-body">
            <p class="text-sm text-slate-500 mb-4">Set minimum advance filing days and required documents per leave type.</p>
            @forelse($policies as $policy)
            <div class="border border-slate-200 rounded-lg p-4 mb-4">
                <form method="POST" action="{{ route('admin.leave-entitlements.update-policy', $policy->id) }}">
                    @csrf @method('PUT')
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-slate-800">{{ $policy->leave_type_label ?? ucwords(str_replace('_', ' ', $policy->leave_type)) }}</h4>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $policy->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $policy->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Min. Advance Days</label>
                            <input type="number" name="min_advance_days" class="input-text text-sm" value="{{ $policy->min_advance_days ?? 0 }}" min="0">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Max Consecutive Days</label>
                            <input type="number" name="max_consecutive_days" class="input-text text-sm" value="{{ $policy->max_consecutive_days ?? '' }}" min="0">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Requires Attachment</label>
                            <select name="requires_attachment" class="input-text text-sm">
                                <option value="0" {{ !$policy->requires_attachment ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $policy->requires_attachment ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline text-xs">Update Policy</button>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-sm text-slate-400 py-4 text-center">No filing policies configured.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchEntitlementTab(tab) {
    document.getElementById('panelBalances').classList.toggle('hidden', tab !== 'balances');
    document.getElementById('panelPolicies').classList.toggle('hidden', tab !== 'policies');
    document.getElementById('tabBalances').classList.toggle('border-indigo-600', tab === 'balances');
    document.getElementById('tabBalances').classList.toggle('text-indigo-600', tab === 'balances');
    document.getElementById('tabBalances').classList.toggle('border-transparent', tab !== 'balances');
    document.getElementById('tabBalances').classList.toggle('text-slate-500', tab !== 'balances');
    document.getElementById('tabPolicies').classList.toggle('border-indigo-600', tab === 'policies');
    document.getElementById('tabPolicies').classList.toggle('text-indigo-600', tab === 'policies');
    document.getElementById('tabPolicies').classList.toggle('border-transparent', tab !== 'policies');
    document.getElementById('tabPolicies').classList.toggle('text-slate-500', tab !== 'policies');
}
</script>
@endpush
@endsection
