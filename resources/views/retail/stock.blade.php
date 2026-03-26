@extends('layouts.app')
@section('title', 'Network Stock — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Network Stock</h1>
            <p class="text-sm text-gray-400 mt-1">Real-time stock availability across all network wholesalers</p>
        </div>
        <a href="/retail/catalogue" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Place Order
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-green-400">{{ number_format($summary['in_stock']) }}</p><p class="text-xs text-gray-400">In Stock</p></div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-amber-400">{{ number_format($summary['low_stock']) }}</p><p class="text-xs text-gray-400">Low Stock</p></div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-red-400">{{ number_format($summary['out_of_stock']) }}</p><p class="text-xs text-gray-400">Out of Stock</p></div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search product name or SKU..."
                   class="flex-1 h-10 rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white placeholder-gray-500 focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400"/>
            <select name="category" class="h-10 w-full sm:w-48 rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400">
                <option value="">All categories</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>@endforeach
            </select>
            <select name="status" class="h-10 w-full sm:w-40 rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400">
                <option value="">All statuses</option>
                <option value="IN_STOCK" {{ $status == 'IN_STOCK' ? 'selected' : '' }}>In Stock</option>
                <option value="LOW_STOCK" {{ $status == 'LOW_STOCK' ? 'selected' : '' }}>Low Stock</option>
                <option value="OUT_OF_STOCK" {{ $status == 'OUT_OF_STOCK' ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <button type="submit" class="h-10 px-5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">Filter</button>
            @if($search || $category || $status)<a href="/retail/stock" class="h-10 px-4 flex items-center border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900 whitespace-nowrap">Clear</a>@endif
        </form>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-left">Supplier</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Price</th>
                        <th class="px-5 py-3 text-left">Updated</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($stockItems as $item)
                    <tr class="hover:bg-gray-900/50 {{ $item->stock_status === 'OUT_OF_STOCK' ? 'opacity-60' : '' }}">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-200">{{ $item->generic_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->brand_name ? $item->brand_name . ' · ' : '' }}{{ $item->sku_code }}</p>
                        </td>
                        <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400">{{ $item->therapeutic_category }}</span></td>
                        <td class="px-5 py-3">
                            <p class="text-sm text-gray-300">{{ $item->supplier_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->supplier_county }}</p>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ match($item->stock_status) { 'IN_STOCK'=>'bg-green-900/30 text-green-400','LOW_STOCK'=>'bg-amber-900/30 text-amber-400','OUT_OF_STOCK'=>'bg-red-900/30 text-red-400',default=>'bg-gray-700 text-gray-400' } }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ match($item->stock_status) { 'IN_STOCK'=>'bg-green-500','LOW_STOCK'=>'bg-amber-500','OUT_OF_STOCK'=>'bg-red-500',default=>'bg-gray-400' } }}"></span>
                                {{ str_replace('_', ' ', $item->stock_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ $item->stock_quantity !== null ? number_format($item->stock_quantity) : '—' }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            @php $priceEntry = isset($prices[$item->product_id]) ? $prices[$item->product_id]->where('wholesale_facility_id', $item->supplier_id)->first() : null; @endphp
                            @if($priceEntry){{ $currency['symbol'] }} {{ number_format($priceEntry->unit_price, 2) }}@else<span class="text-gray-400 font-normal text-xs">—</span>@endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            @if($item->stock_status !== 'OUT_OF_STOCK')
                            <a href="/retail/catalogue?search={{ urlencode($item->generic_name) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-900/20 hover:bg-blue-900/30 text-blue-400 text-xs font-medium rounded-lg transition-colors">Order</a>
                            @else<span class="text-xs text-gray-300">Unavailable</span>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-16 text-center">
                        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-gray-400 text-sm">No stock records found</p>
                        @if($search || $category || $status)<a href="/retail/stock" class="text-yellow-400 text-sm mt-2 inline-block">Clear filters</a>@endif
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-700 flex items-center justify-between gap-4">
            <p class="text-xs text-gray-400">{{ $stockItems->total() }} product-supplier combinations</p>
            {{ $stockItems->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
