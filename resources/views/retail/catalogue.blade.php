@extends('layouts.app')
@section('title', 'Order Medicines — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="catalogue()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-white">Order Medicines</h1>
            <p class="text-sm text-gray-400 mt-1">Browse and add medicines to your order</p>
        </div>
        @if($isOffNetwork)
        <span class="text-xs bg-amber-900/30 text-amber-400 px-3 py-1 rounded-full border border-amber-800">Off-network pricing</span>
        @endif
    </div>

    {{-- Search & category filter --}}
    <form method="GET" class="space-y-3">
        <div class="relative">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or SKU..."
                   class="w-full bg-gray-800 border border-gray-600 text-white rounded-xl px-5 py-3.5 text-sm placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-yellow-400 text-gray-900 rounded-lg text-xs font-medium hover:bg-yellow-500 transition-colors">
                Search
            </button>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="/retail/catalogue"
               class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ !request('category') ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 border border-gray-600 text-gray-300 hover:border-gray-500' }}">
                All
            </a>
            @foreach($categories as $cat)
            <a href="/retail/catalogue?category={{ urlencode($cat) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}"
               class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request('category') === $cat ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 border border-gray-600 text-gray-300 hover:border-gray-500' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </form>

    {{-- Product grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
        <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 flex flex-col gap-3 hover:border-gray-600 transition-colors">
            {{-- Name + favourite --}}
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <h3 class="text-white font-medium text-sm leading-snug">{{ $product->generic_name }}</h3>
                    @if($product->brand_name)
                    <p class="text-gray-500 text-xs mt-0.5 truncate">{{ $product->brand_name }}</p>
                    @endif
                    <p class="text-gray-600 text-xs font-mono">{{ $product->sku_code }}</p>
                </div>
                <button onclick="toggleFavourite({{ $product->product_id }})"
                        class="text-lg flex-shrink-0 {{ in_array($product->product_id, $favouriteIds) ? 'text-yellow-400' : 'text-gray-600' }} hover:text-yellow-400 transition-colors">
                    ★
                </button>
            </div>

            {{-- Unit + category --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-gray-400 text-xs">{{ $product->unit_size }}</span>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-800">{{ $product->therapeutic_category }}</span>
            </div>

            <div class="border-t border-gray-700"></div>

            {{-- Price + supplier + stock --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white font-mono text-sm font-semibold">{{ $currency['symbol'] }} {{ number_format($product->unit_price, $currency['decimal_places']) }}</p>
                    <p class="text-gray-500 text-xs">{{ $product->supplier_name }}</p>
                </div>
                <span class="text-xs {{ $product->stock_status === 'IN_STOCK' ? 'text-green-400' : ($product->stock_status === 'LOW_STOCK' ? 'text-yellow-400' : 'text-gray-500') }}">
                    {{ str_replace('_', ' ', $product->stock_status) }}
                </span>
            </div>

            {{-- Add / stepper --}}
            <div x-show="!inCart({{ $product->price_list_id }})">
                <button @click="addToCart({{ $product->product_id }}, {{ $product->price_list_id }}, '{{ addslashes($product->generic_name) }}', {{ $product->unit_price }})"
                        class="w-full py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors">
                    + Add to Order
                </button>
            </div>
            <div x-show="inCart({{ $product->price_list_id }})" class="flex items-center gap-2">
                <button @click="decrease({{ $product->price_list_id }})"
                        class="w-9 h-9 rounded-lg bg-gray-700 hover:bg-red-900/30 text-white flex items-center justify-center text-lg transition-colors flex-shrink-0">−</button>
                <span class="flex-1 text-center text-white font-mono text-sm font-semibold" x-text="qtyOf({{ $product->price_list_id }})"></span>
                <button @click="increase({{ $product->price_list_id }})"
                        class="w-9 h-9 rounded-lg bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center text-lg transition-colors flex-shrink-0">+</button>
                <span class="text-xs text-yellow-400 font-mono min-w-[70px] text-right"
                      x-text="'{{ $currency['symbol'] }} ' + (qtyOf({{ $product->price_list_id }}) * {{ $product->unit_price }}).toFixed({{ $currency['decimal_places'] }})"></span>
            </div>
        </div>
        @empty
        <div class="col-span-full rounded-xl bg-gray-800 border border-gray-700 px-5 py-16 text-center">
            <p class="text-gray-400 text-sm">No products found</p>
            @if(request('search') || request('category'))
            <a href="/retail/catalogue" class="text-yellow-400 text-sm mt-2 inline-block hover:underline">Clear filters</a>
            @endif
        </div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>

    {{-- Floating cart button --}}
    <a href="/retail/basket" x-show="cartCount > 0" x-cloak
       class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-yellow-400 px-5 py-3 text-gray-900 shadow-lg hover:bg-yellow-500 transition-colors font-medium text-sm">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span x-text="cartCount + ' item' + (cartCount === 1 ? '' : 's')"></span>
        <span>→</span>
    </a>
</div>

<script>
function catalogue() {
    const KEY = 'dm_cart';
    return {
        items: [],
        cartCount: 0,

        init() {
            this.load();
            window.addEventListener('cart-updated', () => this.load());
        },

        load() {
            try { this.items = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch(e) { this.items = []; }
            if (!Array.isArray(this.items)) this.items = [];
            this.cartCount = this.items.reduce((s, i) => s + Number(i.quantity || i.qty || 1), 0);
        },

        save() {
            localStorage.setItem(KEY, JSON.stringify(this.items));
            window.dispatchEvent(new CustomEvent('cart-updated'));
            this.load();
        },

        addToCart(productId, priceListId, name, unitPrice) {
            const existing = this.items.find(i => i.price_list_id === priceListId);
            if (existing) {
                existing.quantity = (existing.quantity || 1) + 1;
            } else {
                this.items.push({ product_id: productId, price_list_id: priceListId, name: name, unit_price: unitPrice, quantity: 1, payment_type: 'CASH' });
            }
            this.save();
        },

        increase(plId) {
            const i = this.items.find(x => x.price_list_id === plId);
            if (i) { i.quantity = (i.quantity || 1) + 1; this.save(); }
        },

        decrease(plId) {
            const idx = this.items.findIndex(x => x.price_list_id === plId);
            if (idx === -1) return;
            if ((this.items[idx].quantity || 1) <= 1) { this.items.splice(idx, 1); } else { this.items[idx].quantity--; }
            this.save();
        },

        inCart(plId) { return this.items.some(i => i.price_list_id === plId); },
        qtyOf(plId) { const i = this.items.find(x => x.price_list_id === plId); return i ? (i.quantity || 1) : 0; },
    }
}

function toggleFavourite(productId) {
    fetch('/api/v1/favourites/' + productId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
    }).then(() => location.reload());
}
</script>
@endsection
