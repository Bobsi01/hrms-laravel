@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('inventory.purchase-orders') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Purchase Orders</a>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $po->po_number }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $po->supplier_name ?? 'Unknown Supplier' }} · Created {{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @php $s = $po->status ?? 'draft'; @endphp
            @if($s === 'draft')
                <form method="POST" action="{{ route('inventory.purchase-orders.update-status', $po->id) }}" class="inline">
                    @csrf <input type="hidden" name="status" value="ordered">
                    <button type="submit" class="btn btn-primary text-sm">Mark as Ordered</button>
                </form>
                <form method="POST" action="{{ route('inventory.purchase-orders.update-status', $po->id) }}" class="inline" data-confirm="Cancel this PO?">
                    @csrf <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger text-sm">Cancel PO</button>
                </form>
            @elseif(in_array($s, ['ordered', 'partial']))
                <button onclick="document.getElementById('receiveModal').classList.remove('hidden')" class="btn btn-primary text-sm">Receive Items</button>
            @endif
        </div>
    </div>
</div>

{{-- Status Badge --}}
<div class="mb-6">
    @if($s === 'draft')
        <span class="px-3 py-1 text-sm font-medium rounded-full bg-slate-100 text-slate-600">Draft</span>
    @elseif($s === 'ordered')
        <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-700">Ordered</span>
    @elseif($s === 'partial')
        <span class="px-3 py-1 text-sm font-medium rounded-full bg-amber-100 text-amber-700">Partially Received</span>
    @elseif($s === 'received')
        <span class="px-3 py-1 text-sm font-medium rounded-full bg-emerald-100 text-emerald-700">Fully Received</span>
    @elseif($s === 'cancelled')
        <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-700">Cancelled</span>
    @endif
    @if($po->expected_date)
    <span class="ml-2 text-sm text-slate-500">Expected: {{ \Carbon\Carbon::parse($po->expected_date)->format('M d, Y') }}</span>
    @endif
</div>

{{-- Line Items --}}
<div class="card mb-6">
    <div class="card-header"><span>Line Items</span></div>
    <div class="card-body">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty Ordered</th>
                    <th>Qty Received</th>
                    <th>Unit Cost</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($lineItems as $li)
                @php $subtotal = ($li->quantity ?? 0) * ($li->unit_cost ?? 0); $grandTotal += $subtotal; @endphp
                <tr>
                    <td class="font-medium">{{ $li->item_name ?? 'Item #' . $li->item_id }}</td>
                    <td>{{ $li->quantity ?? 0 }}</td>
                    <td>
                        <span class="{{ ($li->quantity_received ?? 0) >= ($li->quantity ?? 0) ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $li->quantity_received ?? 0 }}
                        </span>
                    </td>
                    <td>₱{{ number_format($li->unit_cost ?? 0, 2) }}</td>
                    <td class="font-medium">₱{{ number_format($subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right font-bold">Grand Total</td>
                    <td class="font-bold">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($po->notes)
<div class="card">
    <div class="card-header"><span>Notes</span></div>
    <div class="card-body text-sm text-slate-600">{{ $po->notes }}</div>
</div>
@endif

{{-- Receive Modal --}}
@if(in_array($s, ['ordered', 'partial']))
<div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Receive Items</h3>
        <form method="POST" action="{{ route('inventory.purchase-orders.receive', $po->id) }}">
            @csrf
            <div class="space-y-3">
                @foreach($lineItems as $li)
                @if(($li->quantity_received ?? 0) < ($li->quantity ?? 0))
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-800">{{ $li->item_name ?? 'Item #' . $li->item_id }}</div>
                        <div class="text-xs text-slate-400">Ordered: {{ $li->quantity }}, Received so far: {{ $li->quantity_received ?? 0 }}</div>
                    </div>
                    <div>
                        <input type="hidden" name="line_items[{{ $li->id }}][id]" value="{{ $li->id }}">
                        <input type="number" name="line_items[{{ $li->id }}][qty]" class="input-text text-sm w-20"
                            min="0" max="{{ ($li->quantity ?? 0) - ($li->quantity_received ?? 0) }}" value="0">
                    </div>
                </div>
                @endif
                @endforeach
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Confirm Receipt</button>
                    <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
