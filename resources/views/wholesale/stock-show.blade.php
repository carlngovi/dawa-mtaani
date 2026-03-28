@extends('layouts.wholesale')
@section('title', $item->generic_name . ' — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Back button --}}
    <a href="/wholesale/stock" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-yellow-400 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Stock
    </a>

    {{-- Page heading --}}
    <h1 class="text-2xl font-bold text-white">{{ $item->generic_name }}</h1>

    {{-- Detail card --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">SKU Code</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->sku_code }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Therapeutic Category</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->therapeutic_category }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Dosage Form</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->dosage_form }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Strength</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->strength }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Manufacturer</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->manufacturer }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Stock Status</dt>
                <dd class="mt-1">
                    @if($item->stock_status === 'IN_STOCK')
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-900/40 text-green-400">In Stock</span>
                    @elseif($item->stock_status === 'LOW_STOCK')
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-900/40 text-yellow-400">Low Stock</span>
                    @else
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-900/40 text-red-400">Out of Stock</span>
                    @endif
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Last Updated</dt>
                <dd class="mt-1 text-sm text-white">{{ $item->updated_at }}</dd>
            </div>

        </dl>
    </div>

</div>
@endsection
