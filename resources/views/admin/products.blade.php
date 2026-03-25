@extends('layouts.admin')
@section('title', 'Products — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Products</h1>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">SKU</th>
                    <th class="px-5 py-3 text-left">Generic Name</th>
                    <th class="px-5 py-3 text-left">Brand</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-left">Unit Size</th>
                    <th class="px-5 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $product->sku_code }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $product->generic_name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $product->brand_name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                            {{ $product->therapeutic_category }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $product->unit_size }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No products</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection
