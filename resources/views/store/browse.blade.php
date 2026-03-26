@extends('layouts.app')
@section('title', 'Browse Medicines — Dawa Mtaani')
@section('content')
<div class="space-y-6"
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
             } catch (e) {
                 this.results = [];
             }
             this.searching = false;
         }
     }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Browse Medicines</h1>
            <p class="text-sm text-gray-500 mt-1">Search for medicines and find pharmacies near you</p>
        </div>
    </div>

    {{-- Live search --}}
    <div class="relative">
        <input type="text" x-model="searchQuery"
               @input.debounce.300ms="liveSearch()"
               placeholder="Search for a medicine (e.g. Paracetamol, Amoxicillin)..."
               class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

        {{-- Live results dropdown --}}
        <div x-show="results.length > 0" x-cloak
             @click.outside="results = []"
             class="absolute z-30 mt-1 w-full bg-white rounded-xl border border-gray-200 shadow-lg max-h-96 overflow-y-auto">
            <template x-for="item in results" :key="item.product_ulid + item.facility_ulid">
                <a :href="'/store/' + item.facility_ulid"
                   class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800" x-text="item.generic_name"></p>
                        <p class="text-xs text-gray-400" x-text="item.brand_name + ' — ' + item.unit_size"></p>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="item.display_name + (item.distance_km ? ' · ' + item.distance_km + ' km' : '')"></p>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <p class="text-sm font-semibold text-green-700" x-text="item.unit_price"></p>
                        <template x-if="item.verified_badge_eligible">
                            <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 1.382 3.09.476.952 3.008L18.856 8.5l-.954 3.01-.95 3.007-3.09.478L10 16.377l-2.862-1.382-3.09-.478-.95-3.008L2.144 8.5l.954-3.009.95-3.008 3.09-.476L10 1z" clip-rule="evenodd"/></svg>
                                Verified
                            </span>
                        </template>
                    </div>
                </a>
            </template>
        </div>

        <div x-show="searching" class="absolute right-3 top-3">
            <svg class="h-5 w-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
    </div>

    {{-- Pharmacies grid --}}
    @if($eligibleFacilities->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-gray-700 mb-3">Available Pharmacies</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($eligibleFacilities as $ef)
            <a href="/store/{{ $ef->ulid }}"
               class="bg-white rounded-xl border border-gray-200 p-5 hover:border-green-400 hover:shadow-md transition-all block">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ $ef->branding_mode === 'DAWA_MTAANI' ? 'Dawa Mtaani' : $ef->facility_name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $ef->county }}</p>
                    </div>
                    @if($ef->network_membership === 'NETWORK')
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 1.382 3.09.476.952 3.008L18.856 8.5l-.954 3.01-.95 3.007-3.09.478L10 16.377l-2.862-1.382-3.09-.478-.95-3.008L2.144 8.5l.954-3.009.95-3.008 3.09-.476L10 1z" clip-rule="evenodd"/></svg>
                        Verified
                    </span>
                    @endif
                </div>
                <p class="text-xs text-green-700 mt-3 font-medium">View stock →</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Fallback: product catalogue --}}
    @if($products->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-gray-700 mb-3">Medicine Catalogue</h2>
        <form method="GET" class="flex flex-wrap gap-3 items-center mb-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Filter catalogue..."
                   class="flex-1 min-w-[200px] px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <select name="category"
                    class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category']))
                <a href="/store" class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col">
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-800">{{ $product->generic_name }}</h3>
                    @if($product->brand_name)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $product->brand_name }}</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-2">{{ $product->unit_size }}</p>
                    @if($product->therapeutic_category)
                        <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-600">
                            {{ $product->therapeutic_category }}
                        </span>
                    @endif
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400">Available at participating pharmacies</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
    @endif

</div>
@endsection