@extends('layouts.app')
@section('title', 'My Cart — Dawa Mtaani')
@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="patientBasket()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-white">My Cart</h1>
            <p class="text-sm text-gray-400 mt-1"
               x-text="count > 0 ? count + ' item' + (count === 1 ? '' : 's') + ' in your cart' : 'Your cart is empty'"></p>
        </div>
        <a href="/store" class="text-sm text-yellow-400 hover:text-yellow-300 transition-colors">← Continue Shopping</a>
    </div>

    {{-- EMPTY STATE --}}
    <template x-if="items.length === 0">
        <div class="rounded-xl bg-gray-800 border border-gray-700 px-5 py-16 text-center space-y-4">
            <p class="text-4xl">🛒</p>
            <p class="text-white font-medium">Your cart is empty</p>
            <p class="text-gray-500 text-sm">Browse medicines and add items to get started</p>
            <a href="/store"
               class="inline-block px-6 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-semibold rounded-xl transition-colors">
                Browse Medicines →
            </a>
        </div>
    </template>

    {{-- CART WITH ITEMS --}}
    <template x-if="items.length > 0">
        <div class="space-y-4">

            {{-- Items card --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-white">Cart Items</h2>
                    <button @click="clearCart()" class="text-xs text-gray-500 hover:text-red-400 transition-colors">Clear all</button>
                </div>
                <div class="divide-y divide-gray-700">
                    <template x-for="item in items" :key="item.product_id">
                        <div class="px-5 py-4 flex items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate" x-text="item.name"></p>
                                <p class="text-yellow-400 text-xs font-mono mt-0.5" x-text="'{{ $currency['symbol'] }} ' + Number(item.price).toFixed({{ $currency['decimal_places'] }}) + ' each'"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decrease(item.product_id)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-red-900/30 text-white flex items-center justify-center text-lg leading-none transition-colors">−</button>
                                <span class="w-8 text-center text-white font-mono text-sm font-semibold" x-text="item.qty"></span>
                                <button @click="increase(item.product_id)" class="w-8 h-8 rounded-lg bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center text-lg leading-none transition-colors">+</button>
                            </div>
                            <p class="text-white font-mono font-semibold text-sm min-w-[90px] text-right" x-text="'{{ $currency['symbol'] }} ' + (item.price * item.qty).toFixed({{ $currency['decimal_places'] }})"></p>
                            <button @click="remove(item.product_id)" class="text-gray-500 hover:text-red-400 flex-shrink-0 p-1 transition-colors">
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

            {{-- Customer Details --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-4">
                <h2 class="font-semibold text-white">Your Details</h2>
                <p class="text-xs text-gray-400 -mt-2">Required for delivery</p>

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
                    <input type="email" x-model="email" placeholder="john@example.com"
                           class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Delivery Address *</label>
                    <textarea x-model="deliveryAddress" rows="2" placeholder="House/Apartment No, Street, Area, Town"
                              class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Include building name, floor, or landmark to help delivery</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Delivery Instructions <span class="text-gray-500">(optional)</span></label>
                    <textarea x-model="deliveryInstructions" rows="2" placeholder="e.g. Call before delivery, gate code 1234..."
                              class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none resize-none"></textarea>
                </div>
            </div>

            {{-- Payment --}}
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-4">
                <h2 class="font-semibold text-white">Pay via M-Pesa</h2>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">M-Pesa Phone Number *</label>
                    <input type="tel" x-model="phone" placeholder="07XXXXXXXX"
                           class="w-full bg-gray-900 border border-gray-600 text-white rounded-xl px-4 py-3 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition-colors">
                    <p class="text-xs text-gray-500 mt-1.5">You'll receive an M-Pesa prompt. Enter your PIN to complete payment.</p>
                </div>

                <div x-show="error" x-transition class="rounded-lg bg-red-900/20 border border-red-800 px-4 py-3 text-sm text-red-300" x-text="error"></div>

                <button @click="checkout()" :disabled="placing"
                        class="w-full py-4 bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-bold text-base rounded-xl transition-colors">
                    <span x-show="!placing" x-text="'Pay {{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }}) + ' via M-Pesa'"></span>
                    <span x-show="placing">Sending M-Pesa prompt...</span>
                </button>

                <div class="rounded-lg bg-blue-900/20 border border-blue-800 px-4 py-3 text-xs text-blue-300">
                    An STK push will be sent to your phone. Check your phone and enter your M-Pesa PIN to complete payment.
                </div>
            </div>

        </div>
    </template>

</div>

<script>
function patientBasket() {
    return {
        items: [], count: 0, total: 0,
        firstName: '', lastName: '', email: '',
        deliveryAddress: '', deliveryInstructions: '',
        phone: '', placing: false, error: null,
        KEY: 'dm_patient_cart',

        init() {
            this.load();
            window.addEventListener('cart-updated', () => this.load());
        },

        load() {
            try { this.items = JSON.parse(localStorage.getItem(this.KEY) || '[]'); } catch(e) { this.items = []; }
            if (!Array.isArray(this.items)) this.items = [];
            this.recalc();
        },

        recalc() {
            this.count = this.items.reduce((s, i) => s + Number(i.qty || 1), 0);
            this.total = this.items.reduce((s, i) => s + (Number(i.price || 0) * Number(i.qty || 1)), 0);
        },

        save() {
            localStorage.setItem(this.KEY, JSON.stringify(this.items));
            this.recalc();
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },

        increase(id) {
            const i = this.items.find(x => x.product_id === id);
            if (i) { i.qty++; this.save(); }
        },

        decrease(id) {
            const idx = this.items.findIndex(x => x.product_id === id);
            if (idx === -1) return;
            if (this.items[idx].qty <= 1) { this.items.splice(idx, 1); } else { this.items[idx].qty--; }
            this.save();
        },

        remove(id) {
            this.items = this.items.filter(i => i.product_id !== id);
            this.save();
        },

        clearCart() {
            if (!confirm('Clear all items?')) return;
            this.items = [];
            this.save();
        },

        async checkout() {
            this.error = null;

            if (!this.firstName.trim()) { this.error = 'Please enter your first name'; return; }
            if (!this.lastName.trim()) { this.error = 'Please enter your last name'; return; }
            if (!this.email.trim() || !this.email.includes('@')) { this.error = 'Please enter a valid email address'; return; }
            if (!this.deliveryAddress.trim()) { this.error = 'Please enter your delivery address'; return; }
            if (!this.phone.trim()) { this.error = 'Please enter your M-Pesa phone number'; return; }
            if (this.items.length === 0) { this.error = 'Your cart is empty'; return; }

            this.placing = true;

            let ph = this.phone.replace(/\s/g, '');
            if (ph.startsWith('0')) ph = '254' + ph.substring(1);
            if (ph.startsWith('+')) ph = ph.substring(1);

            try {
                const res = await fetch('/store/basket/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({
                        phone: ph,
                        items: this.items,
                        total: this.total,
                        first_name: this.firstName,
                        last_name: this.lastName,
                        email: this.email,
                        delivery_address: this.deliveryAddress,
                        delivery_instructions: this.deliveryInstructions || null,
                    })
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem(this.KEY);
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    window.location.href = data.redirect_url;
                } else {
                    this.error = data.message || 'Something went wrong.';
                }
            } catch(e) { this.error = 'Network error. Try again.'; }
            this.placing = false;
        }
    }
}
</script>
@endsection
