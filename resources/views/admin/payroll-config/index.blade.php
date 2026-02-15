@extends('layouts.app')

@section('title', 'Payroll Configuration')

@section('content')
{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Payroll Configuration</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage overtime rates, allowances, contributions, taxes, and deductions.</p>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline text-sm">← Back to Admin</a>
</div>

{{-- Tabs --}}
<div class="flex flex-wrap gap-1 border-b border-slate-200 mb-6">
    <a href="{{ route('admin.payroll-config.index', ['tab' => 'overtime-rates']) }}"
       class="px-4 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === 'overtime-rates' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        <span class="inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Overtime & Holiday Rates
        </span>
    </a>
    @foreach(['allowances' => 'Allowances', 'contributions' => 'Contributions', 'taxes' => 'Taxes', 'deductions' => 'Deductions'] as $key => $label)
    <a href="{{ route('admin.payroll-config.index', ['tab' => $key]) }}"
       class="px-4 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        {{ $label }}
        @if(($compensationStats[$key] ?? 0) > 0)
            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold rounded-full {{ $tab === $key ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">{{ $compensationStats[$key] }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ═══ Tab Content ═══ --}}

@if($tab === 'overtime-rates')
    {{-- ─── Overtime & Holiday Rates ─── --}}
    <form method="POST" action="{{ route('admin.payroll-config.update-overtime-rates') }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($rates as $code => $rate)
            <div class="card card-body">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800 text-sm">{{ $rate['label'] }}</div>
                        <div class="text-xs text-slate-400">{{ $code }}</div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Multiplier Rate</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="rates[{{ $code }}]" class="input-text" value="{{ $rate['current'] }}" step="0.01" min="0" max="99" required>
                        <span class="text-sm text-slate-400">&times;</span>
                    </div>
                    @if($rate['description'])
                    <p class="text-xs text-slate-400 mt-1">{{ $rate['description'] }} &mdash; Default: {{ $rate['default'] }}&times;</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Save All Rates</button>
        </div>
    </form>

@else
    {{-- ─── Compensation Templates (Allowances / Contributions / Taxes / Deductions) ─── --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <span>{{ ucfirst($tab) }}</span>
            <button onclick="document.getElementById('templateModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Template</button>
        </div>
        <div class="card-body">
            @if($templates->isEmpty())
                <p class="text-sm text-slate-500 py-4 text-center">No templates in this category.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table-basic">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Amount</th>
                                <th>Modifiable</th>
                                <th>Effectivity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $t)
                            <tr>
                                <td class="font-medium">{{ $t->name }}</td>
                                <td><span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $t->code }}</span></td>
                                <td>
                                    @if($t->amount_type === 'static')
                                        ₱{{ number_format($t->static_amount, 2) }}
                                    @else
                                        {{ number_format($t->percentage, 2) }}%
                                    @endif
                                </td>
                                <td>{!! $t->is_modifiable ? '<span class="text-emerald-600 text-xs font-medium">Yes</span>' : '<span class="text-slate-400 text-xs">No</span>' !!}</td>
                                <td class="text-sm text-slate-500">{{ $t->effectivity_until ? $t->effectivity_until->format('M d, Y') : 'Ongoing' }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-500">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-links">
                                        @if($t->is_active)
                                        <form method="POST" action="{{ route('admin.payroll-config.destroy-template', $t) }}" class="inline" data-confirm="Deactivate this template?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Deactivate</button>
                                        </form>
                                        @else
                                        <span class="text-slate-400 text-xs">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Add Template Modal --}}
    <div id="templateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Add {{ ucfirst(rtrim($tab, 's')) }} Template</h3>
            <form method="POST" action="{{ route('admin.payroll-config.store-template') }}">
                @csrf
                <input type="hidden" name="category" value="{{ ['allowances' => 'allowance', 'contributions' => 'contribution', 'taxes' => 'tax', 'deductions' => 'deduction'][$tab] ?? 'allowance' }}">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Name</label>
                        <input type="text" name="name" class="input-text mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Code</label>
                        <input type="text" name="code" class="input-text mt-1" required maxlength="50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Amount Type</label>
                        <select name="amount_type" class="input-text mt-1" required>
                            <option value="static">Static Amount (₱)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Static Amount</label>
                            <input type="number" name="static_amount" class="input-text mt-1" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Percentage</label>
                            <input type="number" name="percentage" class="input-text mt-1" step="0.01" min="0" max="100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Effectivity Until</label>
                        <input type="date" name="effectivity_until" class="input-text mt-1">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_modifiable" value="1" id="is_modifiable" class="rounded">
                        <label for="is_modifiable" class="text-sm text-slate-700">Modifiable per employee</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" class="input-text mt-1" rows="2"></textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <button type="submit" class="btn btn-primary">Create Template</button>
                        <button type="button" onclick="document.getElementById('templateModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
