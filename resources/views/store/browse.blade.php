@extends('layouts.app')
@section('title', 'Browse Medicines — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-init="$store.customerCart.load()"
     x-data>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Browse Medicines</h1>
            <p class="text-sm text-gray-400 mt-1">Search for medicines and find pharmacies near you</p>
        </div>
    </div>

    {{-- Live search --}}
    <div class="relative"
         x-data="{
             searchQuery: '',
             searching: false,
             results: [],
             async liveSearch() {
                 if (this.searchQuery.length < 2) { this.results = []; return; }
                 this.searching = true;
                 try {
                     const res = await fetch('/api/store/search?q=' + encodeURIComponent(this.searchQuery));
                     const json = await res.json();
                     this.results = json.data || [];
                 } catch (e) { this.results = []; }
                 this.searching = false;
             }
         }">
        <div class="relative">
            <input type="text"
                   x-model="searchQuery"
                   @input.debounce.300ms="liveSearch()"
                   placeholder="Search for a medicine (e.g. Paracetamol, Amoxicillin)..."
                   class="w-full px-5 py-4 bg-gray-800 border border-gray-600 text-white rounded-xl text-base placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
            <div x-show="searching" class="absolute right-4 top-4">
                <svg class="h-5 w-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        </div>

        {{-- Search results as product cards --}}
        <div x-show="results.length > 0" x-cloak @click.outside="results = []; searchQuery = ''"
             class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="item in results" :key="item.product_ulid + '-' + item.facility_ulid">
                <div class="bg-gray-800 rounded-xl border border-yellow-600 p-4 flex flex-col gap-3"
                     x-data="{ added: false, qty: 1 }">
                    <div class="flex-1">
                        <h3 class="text-white font-medium text-sm leading-tight" x-text="item.generic_name"></h3>
                        <p class="text-gray-400 text-xs mt-0.5" x-text="item.brand_name"></p>
                        <p class="text-gray-500 text-xs mt-1" x-text="item.unit_size"></p>
                        <div class="mt-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800"
                                  x-text="item.therapeutic_category"></span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2" x-text="item.display_name"></p>
                    </div>
                    <div class="border-t border-gray-700 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-white font-mono text-sm font-semibold" x-text="item.unit_price"></span>
                            <span class="text-xs text-green-400">IN STOCK</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center rounded-lg overflow-hidden border border-gray-600">
                                <button type="button" @click="qty = Math.max(1, qty - 1)"
                                        class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 transition-colors font-bold text-lg">−</button>
                                <span class="w-8 text-center text-white text-sm font-semibold" x-text="qty"></span>
                                <button type="button" @click="qty = Math.min(999, qty + 1)"
                                        class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 transition-colors font-bold text-lg">+</button>
                            </div>
                            <button @click="
                                        $store.customerCart.add(
                                            item.product_id,
                                            item.generic_name,
                                            parseFloat(item.unit_price),
                                            item.unit_size,
                                            qty
                                        );
                                        added = true;
                                        qty = 1;
                                        setTimeout(() => added = false, 1500)"
                                    class="flex-1 py-2 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-medium rounded-lg transition-colors">
                                <span x-show="!added">+ Add to Cart</span>
                                <span x-show="added" x-cloak>Added ✓</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- No results --}}
        <div x-show="!searching && searchQuery.length >= 2 && results.length === 0" x-cloak
             class="mt-4 text-center py-8 text-gray-500 text-sm">
            No medicines found for "<span x-text="searchQuery" class="text-gray-300"></span>"
        </div>
    </div>

    {{-- Pharmacies grid --}}
    @if($eligibleFacilities->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-gray-300 mb-3">Available Pharmacies</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($eligibleFacilities as $ef)
            <a href="/store/{{ $ef->ulid }}"
               class="bg-gray-800 rounded-xl border border-gray-700 p-5 hover:border-gray-600 hover:shadow-md transition-all block">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-200">
                            {{ $ef->branding_mode === 'DAWA_MTAANI' ? 'Dawa Mtaani' : $ef->facility_name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $ef->county }}</p>
                    </div>
                    @if($ef->network_membership === 'NETWORK')
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-900/20 px-2 py-0.5 text-xs font-medium text-green-400 border border-gray-700">
                        Verified
                    </span>
                    @endif
                </div>
                <p class="text-xs text-yellow-400 mt-3 font-medium">View stock →</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Product catalogue with Add to Cart --}}
    @if($products->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-gray-300 mb-3">Medicine Catalogue</h2>
        <form method="GET" class="flex flex-wrap gap-3 items-center mb-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Filter catalogue..."
                   class="flex-1 min-w-[200px] px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <select name="category"
                    class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'category']))
                <a href="/store" class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">Clear</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex flex-col gap-3 hover:border-gray-600 transition-colors"
                 x-data="{ added: false, qty: 1 }">
                <div class="flex-1">
                    <h3 class="text-white font-medium text-sm leading-tight">{{ $product->generic_name }}</h3>
                    @if($product->brand_name)
                        <p class="text-gray-400 text-xs mt-0.5">{{ $product->brand_name }}</p>
                    @endif
                    <p class="text-gray-500 text-xs mt-1">{{ $product->unit_size }}</p>
                    @if($product->therapeutic_category)
                        <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
                            {{ $product->therapeutic_category }}
                        </span>
                    @endif
                </div>
                <div class="border-t border-gray-700 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-white font-mono text-sm font-semibold">
                            {{ $currency['symbol'] }} {{ number_format($product->unit_price, $currency['decimal_places']) }}
                        </span>
                        @if($product->stock_status !== 'OUT_OF_STOCK')
                        <span class="text-xs {{ $product->stock_status === 'IN_STOCK' ? 'text-green-400' : 'text-yellow-400' }}">
                            {{ str_replace('_', ' ', $product->stock_status) }}
                        </span>
                        @else
                        <span class="text-xs text-gray-500">Out of Stock</span>
                        @endif
                    </div>
                    @if($product->stock_status !== 'OUT_OF_STOCK')
                    <div class="flex items-center gap-2">
                        <div class="flex items-center rounded-lg overflow-hidden border border-gray-600">
                            <button type="button" @click="qty = Math.max(1, qty - 1)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 transition-colors font-bold text-lg">−</button>
                            <span class="w-8 text-center text-white text-sm font-semibold" x-text="qty"></span>
                            <button type="button" @click="qty = Math.min(999, qty + 1)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 transition-colors font-bold text-lg">+</button>
                        </div>
                        <button @click="
                                    $store.customerCart.add(
                                        {{ $product->id }},
                                        '{{ addslashes($product->generic_name) }}',
                                        {{ $product->unit_price }},
                                        '{{ $product->unit_size }}',
                                        qty
                                    );
                                    added = true;
                                    qty = 1;
                                    setTimeout(() => added = false, 1500)"
                                class="flex-1 py-2 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span x-show="!added">+ Add to Cart</span>
                            <span x-show="added" x-cloak>Added ✓</span>
                        </button>
                    </div>
                    @else
                    <button disabled class="w-full py-2 bg-gray-700 text-gray-500 text-sm rounded-lg cursor-not-allowed">Out of Stock</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
    @endif

</div>

{{-- Floating cart button → goes to /store/basket --}}
<a href="/store/basket" x-data
   class="fixed bottom-6 right-6 z-[99990] w-14 h-14 bg-yellow-400 hover:bg-yellow-500 text-white rounded-full shadow-xl flex items-center justify-center transition-all hover:scale-105 active:scale-95">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-4H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <span x-show="$store.customerCart.count > 0"
          x-text="$store.customerCart.count"
          class="absolute -top-2 -right-2 min-w-[22px] h-[22px] bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold px-1 shadow-lg"
          x-cloak></span>
</a>

{{-- Toast notifications --}}
<div x-data="{ toasts: [] }"
     @toast.window="toasts.push({ id: Date.now(), ...($event.detail) }); setTimeout(() => toasts.shift(), 3000)"
     class="fixed bottom-20 right-6 z-[99999] flex flex-col gap-2 pointer-events-none">
    <template x-for="t in toasts" :key="t.id">
        <div x-show="true" x-transition
             :class="t.type === 'error' ? 'bg-red-600' : 'bg-gray-800 border border-gray-600'"
             class="px-4 py-3 rounded-lg shadow-xl text-sm font-medium text-white pointer-events-auto max-w-xs">
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

@endsection