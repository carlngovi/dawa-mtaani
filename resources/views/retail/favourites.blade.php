@extends('layouts.app')
@section('title', 'Favourites — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-white">Favourite Products</h1><p class="text-sm text-gray-400 mt-1">Your saved products for quick ordering</p></div>
        <a href="/retail/catalogue" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors">Browse Catalogue</a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-5 py-3 text-left">Product</th><th class="px-5 py-3 text-left">Category</th><th class="px-5 py-3 text-left">Unit Size</th><th class="px-5 py-3 text-right">Lowest Price</th><th class="px-5 py-3 text-center">Suppliers</th><th class="px-5 py-3 text-left">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($favourites as $fav)
                <tr class="hover:bg-gray-900/50">
                    <td class="px-5 py-3"><p class="font-medium text-gray-200">{{ $fav->generic_name }}</p><p class="text-xs text-gray-400">{{ $fav->brand_name ?? '' }} · {{ $fav->sku_code }}</p></td>
                    <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400">{{ $fav->therapeutic_category }}</span></td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $fav->unit_size }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-200">@if($fav->lowest_price){{ $currency['symbol'] }} {{ number_format($fav->lowest_price, 2) }}@else<span class="text-gray-400 font-normal text-xs">No stock</span>@endif</td>
                    <td class="px-5 py-3 text-center"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full text-xs font-bold {{ $fav->supplier_count > 0 ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">{{ $fav->supplier_count }}</span></td>
                    <td class="px-5 py-3"><a href="/retail/catalogue?search={{ urlencode($fav->generic_name) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-900/20 hover:bg-blue-900/30 text-blue-400 text-xs font-medium rounded-lg transition-colors">Order</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center"><p class="text-gray-400 text-sm">No favourites yet</p><a href="/retail/catalogue" class="text-yellow-400 hover:text-blue-400 text-sm font-medium mt-2 inline-block">Browse catalogue →</a></td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $favourites->links() }}</div>
    </div>
</div>
@endsection
