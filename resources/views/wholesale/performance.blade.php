@extends('layouts.app')
@section('title', 'Performance — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-white">My Performance</h1><p class="text-sm text-gray-400 mt-1">Sales metrics and top buyers</p></div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Total Orders</p><p class="text-2xl font-bold text-gray-200">{{ number_format($totalOrders) }}</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Total Revenue</p><p class="text-2xl font-bold text-green-400">{{ $currency['symbol'] }} {{ number_format($totalRevenue, 0) }}</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Fulfilment Rate</p><p class="text-2xl font-bold {{ $fulfilmentRate >= 90 ? 'text-green-400' : ($fulfilmentRate >= 70 ? 'text-amber-400' : 'text-red-400') }}">{{ $fulfilmentRate }}%</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Active Price Lists</p><p class="text-2xl font-bold text-yellow-400">{{ number_format($activePriceLists) }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Top Buying Pharmacies</h3></div>
            <div class="overflow-x-auto"><table class="w-full text-sm min-w-[400px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-4 py-3 text-left">Facility</th><th class="px-4 py-3 text-right">Orders</th><th class="px-4 py-3 text-right">Spend</th></tr></thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($topBuyers as $i => $b)
                    <tr class="hover:bg-gray-900/50"><td class="px-4 py-3"><span class="text-xs font-bold text-gray-400 mr-2">{{ $i+1 }}</span><span class="font-medium text-gray-200">{{ $b->facility_name }}</span><span class="text-xs text-gray-400 ml-1">{{ $b->county }}</span></td><td class="px-4 py-3 text-right text-gray-400">{{ $b->order_count }}</td><td class="px-4 py-3 text-right font-semibold text-gray-200">{{ $currency['symbol'] }} {{ number_format($b->total_spend, 0) }}</td></tr>
                    @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No orders yet</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Top Products by Revenue</h3></div>
            <div class="overflow-x-auto"><table class="w-full text-sm min-w-[400px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-right">Units</th><th class="px-4 py-3 text-right">Revenue</th></tr></thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($topProducts as $i => $p)
                    <tr class="hover:bg-gray-900/50"><td class="px-4 py-3"><span class="text-xs font-bold text-gray-400 mr-2">{{ $i+1 }}</span><span class="font-medium text-gray-200">{{ $p->generic_name }}</span><span class="text-xs font-mono text-gray-400 ml-1">{{ $p->sku_code }}</span></td><td class="px-4 py-3 text-right text-gray-400">{{ number_format($p->total_units) }}</td><td class="px-4 py-3 text-right font-semibold text-gray-200">{{ $currency['symbol'] }} {{ number_format($p->total_revenue, 0) }}</td></tr>
                    @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No product data</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
