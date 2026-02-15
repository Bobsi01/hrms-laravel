@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Point of Sale</h1>
        <p class="text-sm text-slate-500 mt-0.5">Process sales transactions.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.transactions') }}" class="btn btn-outline text-sm">View Transactions</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="posApp()">
    {{-- Product Grid --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <span>Products</span>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()" placeholder="Search items..." class="input-text text-sm w-48">
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="productGrid">
                    <template x-for="item in products" :key="item.id">
                        <button @click="addToCart(item)" class="border border-slate-200 rounded-lg p-3 text-left hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors"
                            :class="{'opacity-50 cursor-not-allowed': item.quantity_on_hand <= 0}">
                            <div class="font-medium text-sm text-slate-900 truncate" x-text="item.name"></div>
                            <div class="text-xs text-slate-400 mt-0.5" x-text="'₱' + Number(item.unit_price).toFixed(2)"></div>
                            <div class="text-xs mt-1" :class="item.quantity_on_hand > 0 ? 'text-emerald-600' : 'text-red-500'" x-text="'Stock: ' + item.quantity_on_hand"></div>
                        </button>
                    </template>
                </div>
                <div x-show="products.length === 0" class="text-sm text-slate-400 text-center py-8">No products found.</div>
            </div>
        </div>
    </div>

    {{-- Cart --}}
    <div>
        <div class="card sticky top-20">
            <div class="card-header flex items-center justify-between">
                <span>Cart</span>
                <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700" x-show="cart.length > 0">Clear</button>
            </div>
            <div class="card-body">
                <div x-show="cart.length === 0" class="text-sm text-slate-400 text-center py-4">Cart is empty</div>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="(ci, idx) in cart" :key="ci.id">
                        <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate" x-text="ci.name"></div>
                                <div class="text-xs text-slate-400" x-text="'₱' + Number(ci.unit_price).toFixed(2) + ' × ' + ci.qty"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="updateQty(idx, -1)" class="w-6 h-6 rounded bg-slate-200 text-slate-600 text-xs flex items-center justify-center">−</button>
                                <span class="text-sm font-medium w-6 text-center" x-text="ci.qty"></span>
                                <button @click="updateQty(idx, 1)" class="w-6 h-6 rounded bg-slate-200 text-slate-600 text-xs flex items-center justify-center">+</button>
                            </div>
                            <div class="text-sm font-medium text-right w-16" x-text="'₱' + (ci.unit_price * ci.qty).toFixed(2)"></div>
                            <button @click="removeFromCart(idx)" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="cart.length > 0" class="border-t border-slate-200 mt-3 pt-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-medium" x-text="'₱' + subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Discount</span>
                        <input type="number" x-model.number="discount" class="input-text text-sm w-20 text-right" min="0" step="0.01">
                    </div>
                    <div class="flex justify-between text-lg font-bold mt-2">
                        <span>Total</span>
                        <span class="text-indigo-600" x-text="'₱' + total.toFixed(2)"></span>
                    </div>

                    <div class="mt-3 space-y-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Payment Method</label>
                            <select x-model="paymentMethod" class="input-text text-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                        <div x-show="paymentMethod === 'cash'">
                            <label class="block text-xs text-slate-500 mb-0.5">Amount Tendered</label>
                            <input type="number" x-model.number="amountTendered" class="input-text text-sm" min="0" step="0.01">
                            <div class="text-xs mt-0.5" :class="change >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="'Change: ₱' + change.toFixed(2)" x-show="amountTendered > 0"></div>
                        </div>
                        <button @click="checkout()" class="btn btn-primary w-full" :disabled="processing || cart.length === 0">
                            <span x-show="!processing">Complete Sale</span>
                            <span x-show="processing">Processing…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        products: [],
        cart: [],
        searchQuery: '',
        discount: 0,
        paymentMethod: 'cash',
        amountTendered: 0,
        processing: false,

        get subtotal() { return this.cart.reduce((sum, ci) => sum + ci.unit_price * ci.qty, 0); },
        get total() { return Math.max(0, this.subtotal - this.discount); },
        get change() { return this.amountTendered - this.total; },

        init() { this.searchProducts(); },

        async searchProducts() {
            try {
                const resp = await fetch('{{ route("inventory.pos.items") }}?search=' + encodeURIComponent(this.searchQuery));
                this.products = await resp.json();
            } catch(e) { console.error(e); }
        },

        addToCart(item) {
            if (item.quantity_on_hand <= 0) return;
            const existing = this.cart.find(ci => ci.id === item.id);
            if (existing) {
                if (existing.qty < item.quantity_on_hand) existing.qty++;
            } else {
                this.cart.push({ id: item.id, name: item.name, unit_price: parseFloat(item.unit_price), qty: 1, max: item.quantity_on_hand });
            }
        },

        updateQty(idx, delta) {
            const ci = this.cart[idx];
            ci.qty = Math.max(1, Math.min(ci.max, ci.qty + delta));
        },

        removeFromCart(idx) { this.cart.splice(idx, 1); },
        clearCart() { this.cart = []; this.discount = 0; this.amountTendered = 0; },

        async checkout() {
            if (this.cart.length === 0 || this.processing) return;
            if (this.paymentMethod === 'cash' && this.amountTendered < this.total) {
                alert('Insufficient amount tendered.');
                return;
            }
            this.processing = true;
            try {
                const resp = await fetch('{{ route("inventory.pos.checkout") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        items: this.cart.map(ci => ({ id: ci.id, qty: ci.qty, price: ci.unit_price })),
                        discount: this.discount,
                        payment_method: this.paymentMethod,
                        amount_tendered: this.amountTendered
                    })
                });
                const data = await resp.json();
                if (data.success) {
                    alert('Sale completed! Transaction #' + data.transaction_id);
                    this.clearCart();
                    this.searchProducts();
                } else {
                    alert('Error: ' + (data.error || 'Transaction failed'));
                }
            } catch(e) {
                alert('Network error. Please try again.');
            } finally {
                this.processing = false;
            }
        }
    };
}
</script>
@endpush
@endsection
