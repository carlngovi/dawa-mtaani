@extends('layouts.app')
@section('title', 'Point of Sale — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Point of Sale</h1>
            <p class="text-sm text-gray-400 mt-1">Record dispensing entries for {{ $facility->facility_name ?? 'your pharmacy' }}</p>
        </div>
    </div>

    @if(!$facility)
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        No facility linked to your account. Contact your administrator.
    </div>
    @else

    @if(session('success'))
    <div class="rounded-lg bg-green-900/20 border border-green-800 px-4 py-3 text-sm text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Dispensed Today</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $todayCount }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Products Available</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $products->count() }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Pharmacy</p>
            <p class="text-lg font-semibold text-white mt-1 truncate">{{ $facility->facility_name }}</p>
        </div>
    </div>

    {{-- Record Dispensing --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h2 class="font-semibold text-white">Record Dispensing</h2>
        </div>
        <form method="POST" action="/api/v1/pos/dispense" class="p-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Product</label>
                    <select name="product_id" required
                            class="w-full text-sm bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                        <option value="">Select product...</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->generic_name }}
                            @if($product->brand_name) ({{ $product->brand_name }}) @endif
                            — {{ $product->unit_size }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Quantity</label>
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full text-sm bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors">
                        Record
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Recent Dispensing --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h2 class="font-semibold text-white">Recent Dispensing</h2>
        </div>
        @if($recentSales->isEmpty())
        <div class="px-5 py-12 text-center">
            <p class="text-gray-400 text-sm">No dispensing entries recorded yet.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left hidden sm:table-cell">SKU</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-left">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($recentSales as $sale)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 text-gray-200">
                            {{ $sale->generic_name }}
                            <span class="text-xs text-gray-400 ml-1">{{ $sale->unit_size }}</span>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400 hidden sm:table-cell">{{ $sale->sku_code }}</td>
                        <td class="px-5 py-3 text-right text-white font-medium">{{ $sale->quantity }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($sale->dispensed_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @endif
</div>
@endsection
