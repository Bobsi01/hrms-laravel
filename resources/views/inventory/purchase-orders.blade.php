@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Purchase Orders</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage purchase orders from suppliers.</p>
    </div>
    <div>
        <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-primary text-sm">+ Create PO</a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Total POs</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['pending'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Pending</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['ordered'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Ordered</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['received'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Received</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
                    <td class="font-mono text-sm font-medium">
                        <a href="{{ route('inventory.purchase-orders.show', $po->id) }}" class="text-indigo-600 hover:text-indigo-800">{{ $po->po_number }}</a>
                    </td>
                    <td class="text-sm">{{ $po->supplier_name ?? '—' }}</td>
                    <td class="text-sm">{{ $po->item_count ?? 0 }}</td>
                    <td class="font-medium">₱{{ number_format($po->total_amount ?? 0, 2) }}</td>
                    <td>
                        @php $s = $po->status ?? 'draft'; @endphp
                        @if($s === 'draft')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Draft</span>
                        @elseif($s === 'ordered')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Ordered</span>
                        @elseif($s === 'partial')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Partial</span>
                        @elseif($s === 'received')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Received</span>
                        @elseif($s === 'cancelled')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Cancelled</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('inventory.purchase-orders.show', $po->id) }}">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-slate-400 py-4">No purchase orders.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $purchaseOrders->links() }}</div>
    </div>
</div>
@endsection
