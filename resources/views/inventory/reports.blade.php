@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Inventory Reports</h1>
        <p class="text-sm text-slate-500 mt-0.5">Stock overview, valuation, expiry tracking, and movement analysis.</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_items'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Total SKUs</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">₱{{ number_format($stats['total_value'] ?? 0, 2) }}</div>
            <div class="text-xs text-slate-500">Stock Value</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['expiring_soon'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Expiring (30d)</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_transactions'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Transactions (30d)</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Top Selling Items --}}
    <div class="card">
        <div class="card-header"><span>Top Selling Items (30 days)</span></div>
        <div class="card-body">
            @if(!empty($topSelling))
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSelling as $idx => $ts)
                        <tr>
                            <td class="text-slate-400">{{ $idx + 1 }}</td>
                            <td class="font-medium">{{ $ts->name ?? '—' }}</td>
                            <td>{{ number_format($ts->total_qty ?? 0) }}</td>
                            <td>₱{{ number_format($ts->total_revenue ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400 py-4 text-center">No sales data available.</p>
            @endif
        </div>
    </div>

    {{-- Stock Valuation by Category --}}
    <div class="card">
        <div class="card-header"><span>Stock Valuation by Category</span></div>
        <div class="card-body">
            @if(!empty($categoryValuation))
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Items</th>
                            <th>Total Units</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryValuation as $cv)
                        <tr>
                            <td class="font-medium">{{ $cv->category_name ?? 'Uncategorized' }}</td>
                            <td>{{ $cv->item_count ?? 0 }}</td>
                            <td>{{ number_format($cv->total_units ?? 0) }}</td>
                            <td>₱{{ number_format($cv->total_value ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400 py-4 text-center">No data.</p>
            @endif
        </div>
    </div>

    {{-- Expiring Items --}}
    <div class="card lg:col-span-2">
        <div class="card-header"><span>Items Expiring Within 30 Days</span></div>
        <div class="card-body">
            @if(!empty($expiringItems))
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringItems as $ei)
                        @php $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($ei->expiry_date), false); @endphp
                        <tr>
                            <td class="font-medium">{{ $ei->name }}</td>
                            <td class="text-xs font-mono text-slate-400">{{ $ei->sku ?? '—' }}</td>
                            <td>{{ number_format($ei->qty_on_hand ?? 0) }}</td>
                            <td>{{ \Carbon\Carbon::parse($ei->expiry_date)->format('M d, Y') }}</td>
                            <td>
                                @if($daysLeft <= 0)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Expired</span>
                                @elseif($daysLeft <= 7)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ $daysLeft }}d</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ $daysLeft }}d</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400 py-4 text-center">No items expiring soon.</p>
            @endif
        </div>
    </div>
</div>
@endsection
