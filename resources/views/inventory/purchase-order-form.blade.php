@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('inventory.purchase-orders') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Purchase Orders</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create Purchase Order</h1>
</div>

<form method="POST" action="{{ route('inventory.purchase-orders.store') }}" x-data="poForm()">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- PO Details --}}
        <div class="lg:col-span-2">
            <div class="card mb-6">
                <div class="card-header"><span>Order Details</span></div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Supplier</label>
                            <select name="supplier_id" class="input-text mt-1" required>
                                <option value="">Select supplier…</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Expected Delivery</label>
                            <input type="date" name="expected_date" class="input-text mt-1">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" class="input-text mt-1" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <span>Line Items</span>
                    <button type="button" @click="addLine()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Line</button>
                </div>
                <div class="card-body">
                    <table class="table-basic">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit Cost</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, idx) in lines" :key="idx">
                                <tr>
                                    <td>
                                        <select :name="'items['+idx+'][item_id]'" class="input-text text-sm" required>
                                            <option value="">Select…</option>
                                            @foreach($items as $itm)
                                            <option value="{{ $itm->id }}">{{ $itm->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" :name="'items['+idx+'][quantity]'" class="input-text text-sm w-20" min="1" x-model.number="line.qty" required></td>
                                    <td><input type="number" :name="'items['+idx+'][unit_cost]'" class="input-text text-sm w-24" step="0.01" min="0" x-model.number="line.cost" required></td>
                                    <td class="font-medium text-sm" x-text="'₱' + (line.qty * line.cost).toFixed(2)"></td>
                                    <td>
                                        <button type="button" @click="removeLine(idx)" class="text-red-400 hover:text-red-600" x-show="lines.length > 1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div>
            <div class="card sticky top-20">
                <div class="card-header"><span>Summary</span></div>
                <div class="card-body">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500">Line Items</span>
                        <span class="font-medium" x-text="lines.length"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t border-slate-200 pt-2">
                        <span>Total</span>
                        <span class="text-indigo-600" x-text="'₱' + grandTotal.toFixed(2)"></span>
                    </div>
                    <div class="mt-4 space-y-2">
                        <button type="submit" class="btn btn-primary w-full">Create Purchase Order</button>
                        <a href="{{ route('inventory.purchase-orders') }}" class="btn btn-outline w-full text-center">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function poForm() {
    return {
        lines: [{ qty: 1, cost: 0 }],
        get grandTotal() { return this.lines.reduce((s, l) => s + l.qty * l.cost, 0); },
        addLine() { this.lines.push({ qty: 1, cost: 0 }); },
        removeLine(idx) { this.lines.splice(idx, 1); }
    };
}
</script>
@endpush
@endsection
