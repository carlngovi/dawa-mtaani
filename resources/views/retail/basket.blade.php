@extends('layouts.app')
@section('title', 'Review Your Order — Dawa Mtaani')
@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="retailBasketPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-white">Review Your Order</h1>
            <p class="text-sm text-gray-400 mt-1"
               x-text="count > 0 ? count + ' item' + (count === 1 ? '' : 's') + ' ready to order' : 'Your basket is empty'"></p>
        </div>
        <a href="/retail/catalogue" class="text-sm text-yellow-400 hover:text-yellow-300 transition-colors">← Continue Shopping</a>
    </div>

    {{-- EMPTY STATE --}}
    <template x-if="items.length === 0">
        <div class="rounded-xl bg-gray-800 border border-gray-700 px-5 py-16 text-center space-y-4">
            <p class="text-4xl">🧺</p>
            <p class="text-white font-medium">Your basket is empty</p>
            <p class="text-gray-500 text-sm">Add medicines from the catalogue to place a B2B order</p>
            <a href="/retail/catalogue"
               class="inline-block px-6 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-semibold rounded-xl transition-colors">
                Browse Catalogue →
            </a>
        </div>
    </template>

    {{-- ORDER WITH ITEMS --}}
    <template x-if="items.length > 0">
        <div class="space-y-4">

            {{-- Items card --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-white">Order Items</h2>
                    <button @click="clearCart()" class="text-xs text-gray-500 hover:text-red-400 transition-colors">Clear all</button>
                </div>
                <div class="divide-y divide-gray-700">
                    <template x-for="(item, idx) in items" :key="item.price_list_id || item.product_id || idx">
                        <div class="px-5 py-4 flex items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate" x-text="item.name || item.product_name"></p>
                                <p class="text-yellow-400 text-xs font-mono mt-0.5"
                                   x-text="'{{ $currency['symbol'] }} ' + Number(item.unit_price || item.price || 0).toFixed({{ $currency['decimal_places'] }}) + ' / unit'"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decrease(idx)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-red-900/30 text-white flex items-center justify-center text-lg leading-none transition-colors">−</button>
                                <span class="w-8 text-center text-white font-mono text-sm font-semibold" x-text="item.quantity || item.qty || 1"></span>
                                <button @click="increase(idx)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center text-lg leading-none transition-colors">+</button>
                            </div>
                            <p class="text-white font-mono font-semibold text-sm min-w-[90px] text-right"
                               x-text="'{{ $currency['symbol'] }} ' + ((item.unit_price || item.price || 0) * (item.quantity || item.qty || 1)).toFixed({{ $currency['decimal_places'] }})"></p>
                            <button @click="removeItem(idx)" class="text-gray-500 hover:text-red-400 flex-shrink-0 p-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Order total --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 px-5 py-4 flex items-center justify-between">
                <span class="text-white font-semibold">Order Total</span>
                <span class="text-white font-bold text-xl font-mono" x-text="'{{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }})"></span>
            </div>

            {{-- Delivery Details --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-4">
                <div>
                    <h2 class="font-semibold text-white">Delivery Details</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Required for order delivery</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">First Name *</label>
                        <input type="text" x-model="firstName" placeholder="John"
                               class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Last Name *</label>
                        <input type="text" x-model="lastName" placeholder="Doe"
                               class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Email Address *</label>
                    <input type="email" x-model="email" placeholder="pharmacy@example.com"
                           class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Delivery Address *</label>
                    <x-address-picker placeholder="Search for your pharmacy delivery location..." />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Delivery Instructions <span class="text-gray-500">(optional)</span></label>
                    <textarea x-model="deliveryInstructions" rows="2" placeholder="e.g. Deliver to dispensary entrance..."
                              class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none resize-none"></textarea>
                </div>
            </div>

            {{-- Payment --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-4">
                <h2 class="font-semibold text-white">Payment Method</h2>

                @if($isNetworkMember && $creditAvailable > 0)
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="paymentType === 'CREDIT' ? 'border-yellow-400 bg-yellow-400/5' : 'border-gray-600 hover:border-gray-700'">
                    <input type="radio" value="CREDIT" x-model="paymentType" class="mt-0.5 accent-yellow-400">
                    <div>
                        <p class="text-white text-sm font-medium">Credit Facility</p>
                        <p class="text-gray-400 text-xs mt-0.5">Available: {{ $currency['symbol'] }} {{ number_format($creditAvailable, $currency['decimal_places']) }}</p>
                    </div>
                </label>
                @endif

                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="paymentType === 'CASH' ? 'border-yellow-400 bg-yellow-400/5' : 'border-gray-600 hover:border-gray-700'">
                    <input type="radio" value="CASH" x-model="paymentType" class="mt-0.5 accent-yellow-400">
                    <div class="flex-1">
                        <p class="text-white text-sm font-medium">M-Pesa STK Push</p>
                        <p class="text-gray-400 text-xs mt-0.5">Pay via M-Pesa. You will receive a prompt on your phone.</p>
                        <div class="mt-3">
                            <label class="block text-xs text-gray-400 mb-1">M-Pesa Phone Number</label>
                            <input type="tel" x-model="mpesaPhone" placeholder="07XXXXXXXX or +2547XXXXXXXX"
                                   class="w-full text-sm bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2 placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                        </div>
                    </div>
                </label>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Order Notes <span class="text-gray-500">(optional)</span></label>
                    <textarea x-model="notes" rows="2" placeholder="Any special instructions..."
                              class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none resize-none"></textarea>
                </div>

                <div x-show="error" x-transition class="rounded-lg bg-red-900/20 border border-gray-700 px-4 py-3 text-sm text-red-300" x-text="error"></div>

                <button @click="placeOrder()" :disabled="placing || items.length === 0"
                        class="w-full py-4 bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-base rounded-xl transition-colors">
                    <span x-show="!placing" x-text="paymentType === 'CASH' ? 'Pay {{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }}) + ' via M-Pesa' : 'Place Order — {{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }})"></span>
                    <span x-show="placing" class="flex items-center justify-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Placing order...
                    </span>
                </button>
            </div>

        </div>
    </template>
</div>

<script>
function retailBasketPage() {
    return {
        items: JSON.parse(localStorage.getItem('dm_cart') || '[]'),
        count: 0, total: 0,
        firstName: '', lastName: '', email: '',
        deliveryAddress: '', deliveryInstructions: '',
        deliveryLat: '', deliveryLng: '', deliveryPlaceId: '',
        paymentType: '{{ $isNetworkMember && $creditAvailable > 0 ? 'CREDIT' : 'CASH' }}',
        mpesaPhone: '{{ auth()->user()->phone ?? '' }}',
        notes: '',
        placing: false, error: null,

        init() {
            this.recalc();
            window.addEventListener('cart-updated', () => {
                this.items = JSON.parse(localStorage.getItem('dm_cart') || '[]');
                this.recalc();
            });
            window.addEventListener('address-selected', (e) => {
                this.deliveryAddress = e.detail.address;
                this.deliveryLat = e.detail.lat;
                this.deliveryLng = e.detail.lng;
                this.deliveryPlaceId = e.detail.place_id;
            });
            window.addEventListener('address-typed', (e) => {
                if (!this.deliveryLat) this.deliveryAddress = e.detail.address;
            });
        },

        recalc() {
            this.count = this.items.reduce((s, i) => s + Number(i.quantity || i.qty || 1), 0);
            this.total = this.items.reduce((s, i) => s + (Number(i.unit_price || i.price || 0) * Number(i.quantity || i.qty || 1)), 0);
        },

        saveCart() {
            localStorage.setItem('dm_cart', JSON.stringify(this.items));
            localStorage.setItem('dm_cart_count', this.items.length);
            this.recalc();
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },

        increase(idx) {
            const k = 'quantity' in this.items[idx] ? 'quantity' : 'qty';
            this.items[idx][k] = (this.items[idx][k] || 1) + 1;
            this.saveCart();
        },

        decrease(idx) {
            const k = 'quantity' in this.items[idx] ? 'quantity' : 'qty';
            if ((this.items[idx][k] || 1) <= 1) { this.items.splice(idx, 1); } else { this.items[idx][k]--; }
            this.saveCart();
        },

        removeItem(idx) { this.items.splice(idx, 1); this.saveCart(); },

        clearCart() {
            if (!confirm('Clear entire basket?')) return;
            this.items = [];
            this.saveCart();
        },

        async placeOrder() {
            this.error = null;
            if (!this.firstName.trim()) { this.error = 'Please enter your first name'; return; }
            if (!this.lastName.trim()) { this.error = 'Please enter your last name'; return; }
            if (!this.email.trim() || !this.email.includes('@')) { this.error = 'Please enter a valid email'; return; }
            if (!this.deliveryAddress.trim()) { this.error = 'Please enter delivery address'; return; }
            if (this.paymentType === 'CASH' && !this.mpesaPhone.trim()) { this.error = 'Please enter M-Pesa phone number'; return; }
            if (this.items.length === 0) { this.error = 'Your basket is empty'; return; }

            this.placing = true;
            const orderItems = this.items.map(i => ({
                product_id: i.product_id,
                price_list_id: i.price_list_id,
                quantity: i.quantity || i.qty || 1,
                payment_type: this.paymentType === 'CREDIT' ? 'CREDIT' : 'CASH',
            }));

            try {
                const res = await fetch('/retail/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({
                        items: orderItems,
                        order_type: 'STANDARD',
                        notes: this.notes || null,
                        mpesa_phone: this.mpesaPhone || null,
                        first_name: this.firstName,
                        last_name: this.lastName,
                        email: this.email,
                        delivery_address: this.deliveryAddress,
                        delivery_instructions: this.deliveryInstructions || null,
                        delivery_lat: this.deliveryLat || null,
                        delivery_lng: this.deliveryLng || null,
                        delivery_place_id: this.deliveryPlaceId || null,
                    })
                });

                if (res.redirected) {
                    localStorage.removeItem('dm_cart');
                    localStorage.setItem('dm_cart_count', '0');
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    window.location.href = res.url;
                    return;
                }

                const data = await res.json();
                if (res.ok && (data.redirect || data.redirect_url)) {
                    localStorage.removeItem('dm_cart');
                    localStorage.setItem('dm_cart_count', '0');
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    window.location.href = data.redirect || data.redirect_url;
                } else {
                    this.error = data.message || 'Order failed. Please try again.';
                }
            } catch(e) {
                this.error = 'Network error. Please try again.';
            }
            this.placing = false;
        }
    };
}
</script>
@endsection
