@extends('layouts.app')
@section('title', 'Place Order — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         basket: [],
         addItem(id, name, price, qty) {
             const existing = this.basket.find(i => i.id === id);
             if (existing) { existing.qty = parseInt(qty); }
             else { this.basket.push({ id, name, price: parseFloat(price), qty: parseInt(qty) }); }
             this.basket = this.basket.filter(i => i.qty > 0);
         },
         total() {
             return this.basket.reduce((sum, i) => sum + (i.price * i.qty), 0).toFixed(2);
         }
     }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Place Order</h1>
            @if($selectedOutlet)
            <p class="text-sm text-gray-400 mt-1">
                Ordering for: <strong>{{ $selectedOutlet->facility_name }}</strong>
                <a href="/group/order" class="ml-3 text-xs text-green-400 hover:underline">Change outlet</a>
            </p>
            @endif
        </div>
        <a href="/group/dashboard"
           class="px-4 py-2 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
            ← Dashboard
        </a>
    </div>

    @if($authorisedOutlets->isEmpty())
    {{-- No authorised outlets --}}
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        You have no authorised outlets. Contact Network Admin to be added as an authorised placer for an outlet.
    </div>

    @elseif(! $selectedOutlet)
    {{-- Step 1: Select outlet --}}
    <div>
        <h2 class="text-base font-semibold text-gray-300 mb-4">Select an Outlet to Order For</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($authorisedOutlets as $outlet)
            <a href="/group/order?outlet={{ $outlet->ulid }}"
               class="bg-gray-800 rounded-xl border border-gray-700 p-5 hover:border-gray-600 transition-colors block">
                <p class="font-semibold text-gray-200">{{ $outlet->facility_name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $outlet->county }}</p>
                <div class="flex gap-2 mt-3">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                        {{ $outlet->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                        {{ $outlet->network_membership === 'NETWORK' ? 'Network' : 'Off-Network' }}
                    </span>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                        {{ $outlet->facility_status === 'ACTIVE' ? 'bg-green-900/30 text-green-400' : 'bg-amber-900/30 text-amber-400' }}">
                        {{ $outlet->facility_status }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    @else
    {{-- Step 2: Browse and order --}}

    @if($selectedOutlet->facility_status !== 'ACTIVE')
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        ⚠ This outlet's status is <strong>{{ $selectedOutlet->facility_status }}</strong>.
        Orders may not be processed until the facility is ACTIVE.
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Product catalogue --}}
        <div class="lg:col-span-2 space-y-6">
            @forelse($products as $category => $items)
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-700 bg-gray-900/50">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $category }}</h3>
                </div>
                <div class="divide-y divide-gray-700">
                    @foreach($items as $item)
                    <div class="px-5 py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->pack_size }}</p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $item->stock_status === 'IN_STOCK' ? 'bg-green-900/30 text-green-400' : 'bg-amber-900/30 text-amber-400' }}">
                                {{ str_replace('_', ' ', $item->stock_status) }}
                            </span>
                            <p class="text-sm font-semibold text-gray-200 w-24 text-right">
                                {{ $currency['symbol'] }} {{ number_format($item->unit_price, $currency['decimal_places']) }}
                            </p>
                            <input type="number" min="0" value="0"
                                   @change="addItem({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $item->unit_price }}, $event.target.value)"
                                   class="w-16 px-2 py-1.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm text-center focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
                <p class="text-gray-400 text-sm">No products available in the catalogue</p>
            </div>
            @endforelse
        </div>

        {{-- Basket --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 sticky top-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Order Summary</h3>

                <div x-show="basket.length === 0" class="text-xs text-gray-400 py-4 text-center">
                    Add items from the catalogue
                </div>

                <ul x-show="basket.length > 0" class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="item in basket" :key="item.id">
                        <li class="flex justify-between text-xs">
                            <span class="text-gray-400 truncate flex-1" x-text="item.name + ' × ' + item.qty"></span>
                            <span class="text-gray-200 font-medium ml-2"
                                  x-text="'{{ $currency['symbol'] }} ' + (item.price * item.qty).toFixed({{ $currency['decimal_places'] }})"></span>
                        </li>
                    </template>
                </ul>

                <div x-show="basket.length > 0" class="border-t border-gray-700 pt-3">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>Total</span>
                        <span x-text="'{{ $currency['symbol'] }} ' + total()"></span>
                    </div>
                </div>

                <form method="POST" action="/api/v1/orders" x-show="basket.length > 0">
                    @csrf
                    <input type="hidden" name="facility_id" value="{{ $selectedOutlet->id }}">
                    <template x-for="item in basket" :key="item.id">
                        <span>
                            <input type="hidden" :name="'items[' + item.id + '][product_id]'" :value="item.id">
                            <input type="hidden" :name="'items[' + item.id + '][quantity]'"   :value="item.qty">
                        </span>
                    </template>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-300">Payment Type</label>
                        <select name="payment_type"
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                            @if($selectedOutlet->network_membership === 'NETWORK')
                            <option value="CREDIT">Credit</option>
                            <option value="MIXED">Mixed</option>
                            @endif
                            <option value="CASH">Cash</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full mt-3 px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">
                        Submit Order
                    </button>
                </form>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection