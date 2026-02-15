@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('inventory.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Inventory</a>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $item->name }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">SKU: {{ $item->sku ?? '—' }} · {{ $item->category_name ?? 'Uncategorized' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-outline text-sm">Edit Item</a>
            <button onclick="document.getElementById('adjustModal').classList.remove('hidden')" class="btn btn-primary text-sm">Adjust Stock</button>
        </div>
    </div>
</div>

{{-- Item Details --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card card-body">
        <div class="text-sm text-slate-500 mb-1">Current Stock</div>
        @php $qty = $item->qty_on_hand ?? 0; $reorder = $item->reorder_level ?? 0; @endphp
        <div class="text-3xl font-bold {{ $qty <= 0 ? 'text-red-600' : ($qty <= $reorder ? 'text-amber-600' : 'text-emerald-600') }}">
            {{ number_format($qty) }}
        </div>
        <div class="text-xs text-slate-400">{{ $item->unit ?? 'pcs' }} · Reorder at {{ $reorder }}</div>
    </div>
    <div class="card card-body">
        <div class="text-sm text-slate-500 mb-1">Unit Price</div>
        <div class="text-3xl font-bold text-slate-900">₱{{ number_format($item->unit_price ?? 0, 2) }}</div>
        <div class="text-xs text-slate-400">Cost: ₱{{ number_format($item->cost_price ?? 0, 2) }}</div>
    </div>
    <div class="card card-body">
        <div class="text-sm text-slate-500 mb-1">Stock Value</div>
        <div class="text-3xl font-bold text-slate-900">₱{{ number_format(($item->qty_on_hand ?? 0) * ($item->cost_price ?? 0), 2) }}</div>
        <div class="text-xs text-slate-400">Based on cost price</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Details Card --}}
    <div class="card">
        <div class="card-header"><span>Item Details</span></div>
        <div class="card-body text-sm space-y-2">
            <div class="flex justify-between"><span class="text-slate-500">Category</span><span>{{ $item->category_name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Supplier</span><span>{{ $item->supplier_name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Location</span><span>{{ $item->location_name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Barcode</span><span class="font-mono">{{ $item->barcode ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Expiry</span><span>{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') : '—' }}</span></div>
            @if($item->description)
            <div class="pt-2 border-t border-slate-100">
                <span class="text-slate-500">Description</span>
                <p class="mt-1 text-slate-700">{{ $item->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Stock Movement History --}}
    <div class="card lg:col-span-2">
        <div class="card-header"><span>Stock Movement History</span></div>
        <div class="card-body">
            @if($movements->isEmpty())
                <p class="text-sm text-slate-400 py-4 text-center">No stock movements recorded.</p>
            @else
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $mv)
                        <tr>
                            <td class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($mv->created_at)->format('M d, h:i A') }}</td>
                            <td>
                                @php $type = $mv->movement_type ?? $mv->type ?? ''; @endphp
                                @if(in_array($type, ['in', 'purchase', 'received', 'adjustment_in']))
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">{{ ucfirst($type) }}</span>
                                @elseif(in_array($type, ['out', 'sale', 'adjustment_out']))
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ ucfirst($type) }}</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">{{ ucfirst($type) }}</span>
                                @endif
                            </td>
                            <td class="font-medium {{ ($mv->quantity ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ($mv->quantity ?? 0) >= 0 ? '+' : '' }}{{ $mv->quantity ?? 0 }}
                            </td>
                            <td class="text-xs text-slate-400">{{ $mv->reference ?? '—' }}</td>
                            <td class="text-xs text-slate-400">{{ Str::limit($mv->notes ?? '', 40) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Stock Adjustment Modal --}}
<div id="adjustModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Adjust Stock</h3>
        <form method="POST" action="{{ route('inventory.adjust', $item->id) }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Adjustment Type</label>
                    <select name="type" class="input-text mt-1" required>
                        <option value="adjustment_in">Stock In (add)</option>
                        <option value="adjustment_out">Stock Out (remove)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Quantity</label>
                    <input type="number" name="quantity" class="input-text mt-1" required min="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference</label>
                    <input type="text" name="reference" class="input-text mt-1" placeholder="PO#, receipt, etc.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea name="notes" class="input-text mt-1" rows="2"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Submit Adjustment</button>
                    <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
