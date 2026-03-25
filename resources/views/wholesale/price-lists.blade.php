@extends('layouts.wholesale')
@section('title', 'Price Lists — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Price Lists</h1>
        @if($expiredCount > 0)
            <span class="text-sm text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1 rounded-lg">
                {{ $expiredCount }} expired in last 30 days
            </span>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($priceLists as $pl)
                <tr class="hover:bg-gray-50 {{ ! $pl->is_active ? 'opacity-50' : '' }}">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $pl->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $pl->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $pl->therapeutic_category }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($pl->unit_price, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $pl->stock_status === 'IN_STOCK' ? 'bg-green-100 text-green-700' :
                               ($pl->stock_status === 'LOW_STOCK' ? 'bg-amber-100 text-amber-700' :
                               'bg-red-100 text-red-700') }}">
                            {{ str_replace('_', ' ', $pl->stock_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $pl->effective_from }}</td>
                    <td class="px-5 py-3 text-xs {{ $pl->expires_at && $pl->expires_at < now()->toDateString() ? 'text-red-500' : 'text-gray-500' }}">
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
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $priceLists->links() }}</div>
    </div>

</div>
@endsection
