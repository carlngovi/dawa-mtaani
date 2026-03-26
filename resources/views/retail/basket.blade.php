@extends('layouts.retail')
@section('title', 'Review Order — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="basketReview()">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Review Your Order</h1>
        <a href="/retail/catalogue" class="text-sm text-green-700 hover:underline">← Continue Shopping</a>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Empty state --}}
    <div x-show="items.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">Your order basket is empty.</p>
        <a href="/retail/catalogue"
           class="inline-block mt-4 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
            Browse Catalogue
        </a>
    </div>

    <template x-if="items.length > 0">
        <div>
            {{-- Items table --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Product</th>
                            <th class="px-5 py-3 text-right">Unit Price</th>
                            <th class="px-5 py-3 text-center">Qty</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, idx) in items" :key="item.price_list_id">
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-800" x-text="item.name"></td>
                                <td class="px-5 py-3 text-right text-gray-700" x-text="'{{ $currency['symbol'] }} ' + parseFloat(item.unit_price || 0).toFixed({{ $currency['decimal_places'] }})"></td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="updateQty(idx, -1)" class="px-2 py-0.5 bg-gray-100 rounded text-xs hover:bg-gray-200">-</button>
                                        <span class="w-8 text-center text-sm" x-text="item.quantity"></span>
                                        <button @click="updateQty(idx, 1)" class="px-2 py-0.5 bg-gray-100 rounded text-xs hover:bg-gray-200">+</button>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-gray-800" x-text="'{{ $currency['symbol'] }} ' + (parseFloat(item.unit_price || 0) * item.quantity).toFixed({{ $currency['decimal_places'] }})"></td>
                                <td class="px-5 py-3 text-center">
                                    <button @click="removeItem(idx)" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-5 py-3 text-right font-semibold text-gray-700">Order Total</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 text-base" x-text="'{{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }})"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Payment type --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 mt-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Payment Method</h3>

                @if($isNetworkMember && $creditAvailable > 0)
                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer" :class="paymentType === 'CREDIT' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                    <input type="radio" x-model="paymentType" value="CREDIT" class="mt-1">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Credit Facility</p>
                        <p class="text-xs text-gray-500">Available: {{ $currency['symbol'] }} {{ number_format($creditAvailable, $currency['decimal_places']) }}</p>
                        <p x-show="total > {{ $creditAvailable }}" class="text-xs text-red-500 mt-1">Insufficient credit for this order</p>
                    </div>
                </label>
                @endif

                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer" :class="paymentType === 'CASH' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                    <input type="radio" x-model="paymentType" value="CASH" class="mt-1">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Cash on Delivery</p>
                        <p class="text-xs text-gray-500">Pay when order is delivered</p>
                    </div>
                </label>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions (optional)</label>
                <textarea x-model="notes" rows="2" maxlength="1000" placeholder="Any special instructions..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            {{-- Submit --}}
            <div class="mt-6">
                <button @click="placeOrder()"
                        :disabled="submitting || items.length === 0"
                        class="w-full py-3 bg-green-700 text-white rounded-xl font-semibold text-sm hover:bg-green-800 transition-colors disabled:opacity-50">
                    <span x-show="!submitting">Place Order — <span x-text="'{{ $currency['symbol'] }} ' + total.toFixed({{ $currency['decimal_places'] }})"></span></span>
                    <span x-show="submitting" class="flex items-center justify-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Placing order...
                    </span>
                </button>
                <p x-show="error" x-text="error" class="text-sm text-red-600 mt-2 text-center"></p>
            </div>

            <button @click="clearCart()" class="text-xs text-red-500 hover:underline mt-2">Clear entire basket</button>
        </div>
    </template>
</div>

<script>
function basketReview() {
    return {
        items: JSON.parse(localStorage.getItem('dm_cart') || '[]'),
        paymentType: '{{ $isNetworkMember && $creditAvailable > 0 ? 'CREDIT' : 'CASH' }}',
        notes: '',
        submitting: false,
        error: null,

        get total() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.unit_price || 0) * i.quantity), 0);
        },

        updateQty(idx, delta) {
            this.items[idx].quantity = Math.max(1, this.items[idx].quantity + delta);
            this.saveCart();
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.saveCart();
        },

        clearCart() {
            this.items = [];
            this.saveCart();
        },

        saveCart() {
            localStorage.setItem('dm_cart', JSON.stringify(this.items));
            localStorage.setItem('dm_cart_count', this.items.length);
        },

        async placeOrder() {
            this.submitting = true;
            this.error = null;

            const orderItems = this.items.map(i => ({
                product_id: i.product_id,
                price_list_id: i.price_list_id,
                quantity: i.quantity,
                payment_type: this.paymentType,
            }));

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const res = await fetch('/retail/orders', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: orderItems,
                        order_type: 'STANDARD',
                        notes: this.notes || null,
                    })
                });

                if (res.redirected) {
                    localStorage.removeItem('dm_cart');
                    localStorage.setItem('dm_cart_count', '0');
                    window.location.href = res.url;
                    return;
                }

                const json = await res.json();
                if (!res.ok) {
                    this.error = json.message || json.errors?.order?.[0] || 'Order failed. Please try again.';
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            }
            this.submitting = false;
        }
    };
}
</script>
@endsection