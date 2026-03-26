@extends('layouts.admin')
@section('title', 'Inventory — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Inventory</h1>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">In Stock</p>
            <p class="text-2xl font-bold text-green-400">{{ number_format($summary['in_stock']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Low Stock</p>
            <p class="text-2xl font-bold text-amber-400">{{ number_format($summary['low_stock']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Out of Stock</p>
            <p class="text-2xl font-bold text-red-400">{{ number_format($summary['out_of_stock']) }}</p>
        </div>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Supplier</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Quantity</th>
                    <th class="px-5 py-3 text-left">Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($inventory as $item)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $item->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $item->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $item->facility_name }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $item->therapeutic_category }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $item->stock_status === 'IN_STOCK' ? 'bg-green-900/30 text-green-400 border border-green-800' :
                               ($item->stock_status === 'LOW_STOCK' ? 'bg-amber-900/30 text-amber-400 border border-amber-800' :
                               'bg-red-900/30 text-red-400 border border-red-800') }}">
                            {{ str_replace('_',' ',$item->stock_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-400">
                        {{ $item->stock_quantity !== null ? number_format($item->stock_quantity) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No inventory records</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $inventory->links() }}</div>
    </div>
</div>
@endsection
