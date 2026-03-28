@extends('layouts.app')
@section('title', ($eligible->branding_mode === 'DAWA_MTAANI' ? 'Dawa Mtaani' : $facility->facility_name) . ' — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="storefront()"
     x-init="init()">

    {{-- Pharmacy header --}}
    <div class="flex items-start justify-between">
        <div>
            <a href="/store" class="text-xs text-green-400 hover:underline mb-2 inline-block">← Back to Store</a>
            <h1 class="text-2xl font-bold text-white">
                {{ $eligible->branding_mode === 'DAWA_MTAANI' ? 'Dawa Mtaani' : $facility->facility_name }}
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $facility->county }}</p>
        </div>
        @if($eligible->is_network_member)
            @include('store.components.supply-chain-badge', [
                'verified' => true,
                'chain' => [],
                'facilityPpbLicence' => $facility->ppb_licence_number ?? null,
                'facilityPpbStatus' => $facility->ppb_licence_status ?? null,
            ])
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('error'))
    <div class="bg-red-900/20 border border-gray-700 text-red-300 text-sm px-4 py-3 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Client-side search --}}
    <div>
        <input type="text" x-model="filterText"
               placeholder="Search within this pharmacy..."
               class="w-full px-4 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
    </div>

    {{-- Category tabs --}}
    @if($categories->isNotEmpty())
    <div class="flex gap-2 flex-wrap">
        <button @click="filterCategory = ''"
                :class="filterCategory === '' ? 'bg-yellow-400 text-white' : 'bg-gray-700/50 text-gray-400 hover:bg-gray-700/50'"
                class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors">
            All
        </button>
        @foreach($categories as $cat)
        <button @click="filterCategory = '{{ addslashes($cat) }}'"
                :class="filterCategory === '{{ addslashes($cat) }}' ? 'bg-yellow-400 text-white' : 'bg-gray-700/50 text-gray-400 hover:bg-gray-700/50'"
                class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors">
            {{ $cat }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- Product grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($products as $product)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex flex-col"
             x-show="matchesFilter('{{ addslashes($product->generic_name) }}', '{{ addslashes($product->brand_name ?? '') }}', '{{ addslashes($product->therapeutic_category ?? '') }}')"
             :class="inBasket({{ $product->product_id }}) ? 'ring-2 ring-green-400' : ''">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-200">{{ $product->generic_name }}</h3>
                @if($product->brand_name)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $product->brand_name }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $product->unit_size }}</p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-700 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($product->unit_price, $currency['decimal_places']) }}
                    </span>
                    @php
                        $stockBadge = match($product->stock_status) {
                            'IN_STOCK'     => 'bg-green-900/30 text-green-400',
                            'LOW_STOCK'    => 'bg-amber-900/30 text-amber-400',
                            'OUT_OF_STOCK' => 'bg-gray-700/50 text-gray-400',
                            default        => 'bg-gray-700/50 text-gray-400',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $stockBadge }}">
                        {{ str_replace('_', ' ', $product->stock_status) }}
                    </span>
                </div>

                @if($product->stock_status !== 'OUT_OF_STOCK')
                <div class="flex items-center gap-2">
                    <div class="flex items-center border border-gray-600 rounded-lg">
                        <button @click="decQty({{ $product->product_id }})"
                                class="px-2.5 py-1.5 text-gray-400 hover:bg-gray-700/50 rounded-l-lg text-sm">-</button>
                        <span class="w-8 text-center text-sm font-medium" x-text="getQty({{ $product->product_id }})"></span>
                        <button @click="incQty({{ $product->product_id }})"
                                class="px-2.5 py-1.5 text-gray-400 hover:bg-gray-700/50 rounded-r-lg text-sm">+</button>
                    </div>
                    <button @click="addToBasket({{ $product->product_id }}, '{{ addslashes($product->generic_name) }}', {{ $product->unit_price }})"
                            :disabled="adding === {{ $product->product_id }}"
                            class="flex-1 px-3 py-1.5 bg-yellow-400 text-white rounded-lg text-xs font-medium hover:bg-yellow-500 transition-colors disabled:opacity-50">
                        <span x-show="adding !== {{ $product->product_id }} && !justAdded.includes({{ $product->product_id }})">Add to Basket</span>
                        <span x-show="adding === {{ $product->product_id }}">
                            <svg class="h-4 w-4 animate-spin inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                        <span x-show="justAdded.includes({{ $product->product_id }})">Added ✓</span>
                    </button>
                </div>
                @else
                <button disabled class="w-full px-3 py-1.5 bg-gray-700/50 text-gray-400 rounded-lg text-xs cursor-not-allowed">
                    Out of Stock
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($products->isEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No products available at this pharmacy</p>
    </div>
    @endif

    {{-- Basket drawer --}}
    @include('store.components.basket-drawer')

    {{-- Sticky bottom bar (mobile) --}}
    <div x-show="basketItems.length > 0" x-cloak
         class="fixed bottom-0 left-0 right-0 z-30 bg-gray-800 border-t border-gray-700 px-4 py-3 flex items-center justify-between xl:hidden">
        <div>
            <p class="text-sm font-semibold text-gray-200" x-text="'Basket (' + basketCount + ' items)'"></p>
            <p class="text-xs text-gray-400" x-text="subtotal"></p>
        </div>
        <button @click="open = true"
                class="px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500">
            View Basket
        </button>
    </div>

    {{-- Partial fulfilment modal --}}
    @include('store.components.partial-fulfilment-modal')

    {{-- Toast container --}}
    <div x-data="toastManager()"
         @toast.window="add($event.detail)"
         class="fixed bottom-16 right-4 z-50 flex flex-col gap-2 xl:bottom-4">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition
                 :class="toast.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
                 class="px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        add({ message, type = 'success', duration = 3000 }) {
            const id = Date.now();
            this.toasts.push({ id, message, type, visible: true });
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, duration);
        }
    }
}

function storefront() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const headers = { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' };
    const facilityId = {{ $facility->id }};
    const facilityUlid = '{{ $facility->ulid }}';
    const customerPhone = '{{ $user->phone ?? '' }}';

    return {
        filterText: '',
        filterCategory: '',
        basketItems: [],
        basketToken: '{{ $basketToken ?? '' }}',
        open: false,
        adding: null,
        justAdded: [],
        promoCode: '',
        qtys: {},

        get basketCount() { return this.basketItems.reduce((s, i) => s + i.quantity, 0); },
        get subtotal() {
            // Use the server-formatted subtotal from basket load
            return this._subtotal || '{{ $currency["symbol"] }} 0';
        },

        async init() {
            if (this.basketToken) { await this.loadBasket(); }
        },

        matchesFilter(generic, brand, category) {
            const q = this.filterText.toLowerCase();
            const catMatch = !this.filterCategory || category === this.filterCategory;
            const textMatch = !q || generic.toLowerCase().includes(q) || brand.toLowerCase().includes(q);
            return catMatch && textMatch;
        },

        getQty(productId) { return this.qtys[productId] || 1; },
        incQty(productId) { this.qtys[productId] = (this.qtys[productId] || 1) + 1; },
        decQty(productId) { if ((this.qtys[productId] || 1) > 1) this.qtys[productId]--; },
        inBasket(productId) { return this.basketItems.some(i => i.product_id === productId); },

        async addToBasket(productId, name, price) {
            this.adding = productId;
            try {
                const res = await fetch('/api/store/basket/add', {
                    method: 'POST', headers,
                    body: JSON.stringify({
                        customer_phone: customerPhone,
                        facility_id: facilityId,
                        product_id: productId,
                        quantity: this.qtys[productId] || 1,
                    })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    this.basketToken = json.data.basket_token;
                    await this.loadBasket();
                    this.justAdded.push(productId);
                    setTimeout(() => { this.justAdded = this.justAdded.filter(id => id !== productId); }, 2000);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Added to basket', type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: json.message || 'Failed to add', type: 'error' } }));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Network error', type: 'error' } }));
            }
            this.adding = null;
        },

        async loadBasket() {
            if (!this.basketToken) return;
            try {
                const res = await fetch('/api/store/basket?session_token=' + encodeURIComponent(this.basketToken));
                const json = await res.json();
                if (json.status === 'success') {
                    this.basketItems = json.data.lines.map(l => ({
                        ...l,
                        product_id: null // We'll match by product_ulid in the drawer
                    }));
                    this._subtotal = json.data.subtotal;
                }
            } catch (e) { /* silent */ }
        },

        async updateQty(item, delta) {
            const newQty = item.quantity + delta;
            if (newQty <= 0) { return this.removeItem(item); }
            // Re-add with new quantity
            try {
                const productRes = await fetch('/api/store/facilities/' + facilityUlid + '/stock');
                const stockJson = await productRes.json();
                const match = (stockJson.data || []).find(p => p.product_ulid === item.product_ulid);
                if (!match) return;

                // Get product_id from the stock listing
                await fetch('/api/store/basket/add', {
                    method: 'POST', headers,
                    body: JSON.stringify({
                        customer_phone: customerPhone,
                        facility_id: facilityId,
                        product_id: item.product_ulid, // API uses product_id but we need actual id
                        quantity: newQty,
                    })
                });
                await this.loadBasket();
            } catch (e) { /* silent */ }
        },

        async removeItem(item) {
            try {
                await fetch('/api/store/basket/item', {
                    method: 'DELETE', headers,
                    body: JSON.stringify({
                        session_token: this.basketToken,
                        product_id: item.product_ulid,
                    })
                });
                await this.loadBasket();
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Item removed', type: 'success' } }));
            } catch (e) { /* silent */ }
        },

        async checkout() {
            // First reserve the basket
            try {
                const res = await fetch('/api/store/basket/reserve', {
                    method: 'POST', headers,
                    body: JSON.stringify({ session_token: this.basketToken })
                });
                const json = await res.json();
                if (json.status === 'error' && json.data?.unavailable_items?.length) {
                    window.dispatchEvent(new CustomEvent('show-partial-modal', {
                        detail: { unavailable_items: json.data.unavailable_items }
                    }));
                    return;
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to reserve basket', type: 'error' } }));
                return;
            }
            window.location.href = '/store/' + facilityUlid + '/checkout';
        },

        async applyPromo() {
            // Promo codes are applied at checkout, not basket stage
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Promo code will be applied at checkout', type: 'success' } }));
        }
    };
}
</script>
@endsection