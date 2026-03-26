@extends('layouts.admin')
@section('title', 'Products — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Products</h1>
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">SKU</th>
                    <th class="px-5 py-3 text-left">Generic Name</th>
                    <th class="px-5 py-3 text-left">Brand</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-left">Unit Size</th>
                    <th class="px-5 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($products as $product)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $product->sku_code }}</td>
                    <td class="px-5 py-3 font-medium text-gray-200">{{ $product->generic_name }}</td>
                    <td class="px-5 py-3 text-gray-400">{{ $product->brand_name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-800">
                            {{ $product->therapeutic_category }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $product->unit_size }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $product->is_active ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-gray-700 text-gray-400' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No products</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $products->links() }}</div>
    </div>
</div>
@endsection
