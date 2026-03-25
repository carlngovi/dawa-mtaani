@extends('layouts.app')
@section('title', 'Favourites — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Favourite Products</h1><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your saved products for quick ordering</p></div>
        <a href="/retail/catalogue" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">Browse Catalogue</a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 uppercase"><tr><th class="px-5 py-3 text-left">Product</th><th class="px-5 py-3 text-left">Category</th><th class="px-5 py-3 text-left">Unit Size</th><th class="px-5 py-3 text-right">Lowest Price</th><th class="px-5 py-3 text-center">Suppliers</th><th class="px-5 py-3 text-left">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($favourites as $fav)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-5 py-3"><p class="font-medium text-gray-800 dark:text-white">{{ $fav->generic_name }}</p><p class="text-xs text-gray-400">{{ $fav->brand_name ?? '' }} · {{ $fav->sku_code }}</p></td>
                    <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $fav->therapeutic_category }}</span></td>
                    <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $fav->unit_size }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-white">@if($fav->lowest_price){{ $currency['symbol'] }} {{ number_format($fav->lowest_price, 2) }}@else<span class="text-gray-400 font-normal text-xs">No stock</span>@endif</td>
                    <td class="px-5 py-3 text-center"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full text-xs font-bold {{ $fav->supplier_count > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">{{ $fav->supplier_count }}</span></td>
                    <td class="px-5 py-3"><a href="/retail/catalogue?search={{ urlencode($fav->generic_name) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 text-xs font-medium rounded-lg transition-colors">Order</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center"><p class="text-gray-400 text-sm">No favourites yet</p><a href="/retail/catalogue" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">Browse catalogue →</a></td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">{{ $favourites->links() }}</div>
    </div>
</div>
@endsection
