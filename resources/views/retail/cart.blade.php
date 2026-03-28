@extends('layouts.retail')
@section('title', 'My Basket — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="cartPage()" x-init="load()">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-white">My Basket</h1>
            <p class="text-sm text-gray-400 mt-1">Review items and add more before checkout</p>
        </div>
        <a href="/retail/catalogue" class="text-sm text-yellow-400 hover:text-yellow-300">← Browse Catalogue</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Basket items --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-white">Basket Items</h2>
                    <span class="text-xs text-gray-400" x-text="count + ' item' + (count === 1 ? '' : 's')"></span>
                </div>

                <div x-show="items.length === 0" class="px-5 py-12 text-center">
                    <p class="text-gray-500 text-sm">Your basket is empty</p>
                    <a href="/retail/catalogue" class="mt-3 inline-block text-sm text-yellow-400 hover:text-yellow-300">Start shopping →</a>
                </div>

                <div x-show="items.length > 0" class="divide-y divide-gray-700">
                    <template x-for="(item, idx) in items" :key="item.price_list_id || item.product_id || idx">
                        <div class="px-5 py-4 flex items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate" x-text="item.name"></p>
                                <p class="text-yellow-400 text-xs font-mono mt-0.5" x-text="'{{ $currency['symbol'] }} ' + Number(item.unit_price || 0).toFixed({{ $currency['decimal_places'] }}) + ' / unit'"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="changeQty(idx, -1)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center text-lg">−</button>
                                <span class="text-white text-sm w-8 text-center font-mono" x-text="item.quantity"></span>
                                <button @click="changeQty(idx, 1)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center text-lg">+</button>
                            </div>
                            <p class="text-white text-sm font-mono font-semibold min-w-[90px] text-right" x-text="'{{ $currency['symbol'] }} ' + (Number(item.unit_price || 0) * item.quantity).toFixed({{ $currency['decimal_places'] }})"></p>
                            <button @click="removeItem(idx)" class="text-gray-500 hover:text-red-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="items.length > 0" class="px-5 py-3 border-t border-gray-700">
                    <button @click="clearAll()" class="text-xs text-gray-500 hover:text-red-400 transition-colors">Clear entire basket</button>
                </div>
            </div>

            {{-- Add more products --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700">
                    <h2 class="font-semibold text-white">Add More Products</h2>
                    <input type="text" x-model="search" placeholder="Search products..."
                           class="mt-3 w-full text-sm bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2 placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                </div>
                <div class="divide-y divide-gray-700 max-h-[400px] overflow-y-auto">
                    @foreach($allProducts as $p)
                    <div class="px-5 py-3 flex items-center gap-3"
                         x-show="!search || '{{ strtolower($p->generic_name . ' ' . ($p->brand_name ?? '') . ' ' . ($p->sku_code ?? '')) }}'.includes(search.toLowerCase())">
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $p->generic_name }}</p>
                            <p class="text-gray-400 text-xs">{{ $p->brand_name ?? '' }} @if($p->sku_code) · {{ $p->sku_code }} @endif</p>
                            <p class="text-yellow-400 text-xs font-mono">{{ $currency['symbol'] }} {{ number_format($p->unit_price, $currency['decimal_places']) }} / {{ $p->unit_size }}</p>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $p->stock_status === 'IN_STOCK' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-yellow-900/30 text-yellow-400 border border-yellow-800' }}">
                            {{ str_replace('_', ' ', $p->stock_status) }}
                        </span>
                        <button onclick="addProduct({{ $p->id }}, {{ $p->price_list_id }}, '{{ addslashes($p->generic_name) }}', {{ $p->unit_price }}, event)"
                                class="px-3 py-1.5 bg-yellow-400 hover:bg-yellow-500 text-white text-xs font-medium rounded-lg transition-colors flex-shrink-0">
                            + Add
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Order summary --}}
        <div>
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-4 sticky top-6">
                <h2 class="font-semibold text-white">Order Summary</h2>
                <div class="flex justify-between text-sm"><span class="text-gray-400">Items</span><span class="text-white" x-text="count"></span></div>
                <div class="border-t border-gray-700 pt-3 flex justify-between">
                    <span class="text-white font-semibold">Total</span>
                    <span class="text-white font-bold text-lg font-mono" x-text="'{{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }})"></span>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">M-Pesa Phone Number</label>
                    <input type="tel" x-model="phone" placeholder="07XXXXXXXX"
                           class="w-full text-sm bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2 placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                    <p class="text-xs text-gray-500 mt-1">You will receive an M-Pesa prompt on this number</p>
                </div>

                <button @click="checkout()" :disabled="items.length === 0 || !phone || placing"
                        class="w-full py-3.5 bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors text-sm">
                    <span x-show="!placing">Pay via M-Pesa</span>
                    <span x-show="placing">Placing order...</span>
                </button>
                <div x-show="error" class="rounded-lg bg-red-900/20 border border-gray-700 px-3 py-2 text-xs text-red-300" x-text="error"></div>
                <p class="text-xs text-gray-500 text-center">An STK push will be sent after clicking Pay</p>
            </div>
        </div>
    </div>
</div>

<script>
const CART_KEY = 'dm_cart';

function cartPage() {
    return {
        items: [], count: 0, total: 0, phone: '', placing: false, error: null, search: '',

        load() {
            try { this.items = JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch(e) { this.items = []; }
            this.recalc();
        },
        recalc() {
            this.count = this.items.reduce((s, i) => s + (i.quantity || 1), 0);
            this.total = this.items.reduce((s, i) => s + ((i.unit_price || 0) * (i.quantity || 1)), 0);
        },
        save() { localStorage.setItem(CART_KEY, JSON.stringify(this.items)); localStorage.setItem('dm_cart_count', this.items.length); this.recalc(); },
        changeQty(idx, delta) {
            this.items[idx].quantity = Math.max(1, (this.items[idx].quantity || 1) + delta);
            this.save();
        },
        removeItem(idx) { this.items.splice(idx, 1); this.save(); },
        clearAll() { if (!confirm('Clear entire basket?')) return; this.items = []; this.save(); },

        async checkout() {
            if (!this.phone || this.items.length === 0) { this.error = 'Enter phone number and add items'; return; }
            this.placing = true; this.error = null;

            // Normalise phone
            let ph = this.phone.replace(/\s/g, '');
            if (ph.startsWith('0')) ph = '254' + ph.substring(1);
            if (ph.startsWith('+')) ph = ph.substring(1);

            try {
                const res = await fetch('/retail/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({
                        items: this.items.map(i => ({
                            product_id: i.product_id, price_list_id: i.price_list_id,
                            quantity: i.quantity || 1, payment_type: i.payment_type || 'CASH',
                        })),
                        order_type: 'STANDARD', mpesa_phone: ph, notes: null,
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    localStorage.removeItem(CART_KEY); localStorage.setItem('dm_cart_count', '0');
                    window.location.href = data.redirect;
                } else {
                    this.error = data.message || 'Order failed. Please try again.';
                }
            } catch(e) { this.error = 'Network error. Please try again.'; }
            this.placing = false;
        }
    }
}

function addProduct(id, priceListId, name, price, evt) {
    try {
        let items = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
        const existing = items.findIndex(i => i.price_list_id === priceListId);
        if (existing >= 0) { items[existing].quantity = (items[existing].quantity || 1) + 1; }
        else { items.push({ product_id: id, price_list_id: priceListId, name: name, unit_price: price, quantity: 1, payment_type: 'CASH' }); }
        localStorage.setItem(CART_KEY, JSON.stringify(items));
        localStorage.setItem('dm_cart_count', items.length);

        // Reload cart page data
        const el = document.querySelector('[x-data="cartPage()"]');
        if (el?.__x) { el.__x.$data.load(); }

        // Button feedback
        const btn = evt?.target;
        if (btn) { const orig = btn.textContent; btn.textContent = '✓ Added'; setTimeout(() => { btn.textContent = orig; }, 1500); }
    } catch(e) { console.error(e); }
}
</script>
@endsection