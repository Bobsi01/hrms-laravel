@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Inventory Items</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage stock items, quantities, and pricing.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.categories') }}" class="btn btn-outline text-sm">Categories</a>
        <a href="{{ route('inventory.suppliers') }}" class="btn btn-outline text-sm">Suppliers</a>
        <a href="{{ route('inventory.locations') }}" class="btn btn-outline text-sm">Locations</a>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary text-sm">+ Add Item</a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total Items</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['in_stock'] }}</div>
            <div class="text-xs text-slate-500">In Stock</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['low_stock'] }}</div>
            <div class="text-xs text-slate-500">Low Stock</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['out_of_stock'] }}</div>
            <div class="text-xs text-slate-500">Out of Stock</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." class="input-text text-sm flex-1">
            <select name="category" class="input-text text-sm w-40">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="stock_status" class="input-text text-sm w-36">
                <option value="">All Status</option>
                <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <button type="submit" class="btn btn-outline text-sm">Filter</button>
            @if(request()->hasAny(['search', 'category', 'stock_status']))
            <a href="{{ route('inventory.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header"><span>Items</span></div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Cost Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="text-xs font-mono text-slate-500">{{ $item->sku ?? '—' }}</td>
                    <td class="font-medium">
                        <a href="{{ route('inventory.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-800">{{ $item->name }}</a>
                        @if($item->description)<div class="text-xs text-slate-400">{{ Str::limit($item->description, 40) }}</div>@endif
                    </td>
                    <td class="text-sm">{{ $item->category_name ?? '—' }}</td>
                    <td class="font-medium">{{ number_format($item->quantity_on_hand ?? 0) }}</td>
                    <td class="text-sm">₱{{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="text-sm text-slate-500">₱{{ number_format($item->cost_price ?? 0, 2) }}</td>
                    <td>
                        @php
                            $qty = $item->quantity_on_hand ?? 0;
                            $reorder = $item->reorder_level ?? 0;
                        @endphp
                        @if($qty <= 0)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Out of Stock</span>
                        @elseif($qty <= $reorder)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Low Stock</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">In Stock</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('inventory.show', $item->id) }}">View</a>
                            <a href="{{ route('inventory.edit', $item->id) }}">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-slate-400 py-4">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
