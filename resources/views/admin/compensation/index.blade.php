@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Benefits & Deductions</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage compensation templates.</p>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline text-sm">← Back to Admin</a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach(['allowances' => 'Allowances', 'contributions' => 'Contributions', 'taxes' => 'Taxes', 'deductions' => 'Deductions'] as $key => $label)
    <div class="card card-body text-center">
        <div class="text-2xl font-bold text-slate-900">{{ $stats[$key] ?? 0 }}</div>
        <div class="text-xs text-slate-500">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- Tabs --}}
<div class="flex gap-1 border-b border-slate-200 mb-6">
    @foreach(['allowances' => 'Allowances', 'contributions' => 'Contributions', 'taxes' => 'Taxes', 'deductions' => 'Deductions'] as $key => $label)
    <a href="{{ route('admin.compensation.index', ['tab' => $key]) }}"
       class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>{{ ucfirst($tab) }}</span>
        <button onclick="document.getElementById('templateModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Template</button>
    </div>
    <div class="card-body">
        @if($templates->isEmpty())
            <p class="text-sm text-slate-500 py-4 text-center">No templates in this category.</p>
        @else
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
                                <form method="POST" action="{{ route('admin.compensation.destroy', $t) }}" class="inline" data-confirm="Deactivate this template?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Deactivate</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Add Template Modal --}}
<div id="templateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Compensation Template</h3>
        <form method="POST" action="{{ route('admin.compensation.store') }}">
            @csrf
            <input type="hidden" name="category" value="{{ $categoryMap[$tab] ?? 'allowance' }}">
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
@endsection
