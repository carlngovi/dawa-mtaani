@extends('layouts.wholesale')
@section('title', 'Price Lists — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Price Lists</h1>
        @if($expiredCount > 0)
            <span class="text-sm text-amber-400 bg-amber-900/20 border border-amber-800 px-3 py-1 rounded-lg">
                {{ $expiredCount }} expired in last 30 days
            </span>
        @endif
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-left">Stock</th>
                    <th class="px-5 py-3 text-left">Effective</th>
                    <th class="px-5 py-3 text-left">Expires</th>
                    <th class="px-5 py-3 text-left">Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($priceLists as $pl)
                <tr class="hover:bg-gray-900 {{ ! $pl->is_active ? 'opacity-50' : '' }}">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $pl->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $pl->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $pl->therapeutic_category }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($pl->unit_price, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $pl->stock_status === 'IN_STOCK' ? 'bg-green-900/30 text-green-400' :
                               ($pl->stock_status === 'LOW_STOCK' ? 'bg-amber-900/30 text-amber-400' :
                               'bg-red-900/30 text-red-400') }}">
                            {{ str_replace('_', ' ', $pl->stock_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $pl->effective_from }}</td>
                    <td class="px-5 py-3 text-xs {{ $pl->expires_at && $pl->expires_at < now()->toDateString() ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $pl->expires_at ?? '—' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="{{ $pl->is_active ? 'text-green-500' : 'text-gray-300' }} text-sm">●</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No price lists yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $priceLists->links() }}</div>
    </div>

</div>
@endsection
