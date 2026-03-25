@extends('layouts.wholesale')
@section('title', 'Performance — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Performance</h1>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Total Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Delivered</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ number_format($deliveredOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Fulfilment Rate</p>
            <p class="text-3xl font-bold {{ $fulfilmentRate >= 90 ? 'text-green-700' : ($fulfilmentRate >= 70 ? 'text-amber-600' : 'text-red-600') }} mt-1">
                {{ $fulfilmentRate }}%
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
                {{ $currency['symbol'] }} {{ number_format($totalRevenue, 0) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
        <h3 class="text-sm font-semibold text-gray-700">Catalogue Health</h3>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Active price list entries</span>
            <span class="font-semibold text-gray-800">{{ number_format($activePriceLists) }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Fulfilment rate</span>
            <div class="flex items-center gap-2">
                <div class="w-32 h-2 bg-gray-200 rounded-full">
                    <div class="h-2 rounded-full {{ $fulfilmentRate >= 90 ? 'bg-green-500' : ($fulfilmentRate >= 70 ? 'bg-amber-400' : 'bg-red-500') }}"
                         style="width: {{ $fulfilmentRate }}%"></div>
                </div>
                <span class="font-semibold text-gray-800">{{ $fulfilmentRate }}%</span>
            </div>
        </div>
    </div>

</div>
@endsection
