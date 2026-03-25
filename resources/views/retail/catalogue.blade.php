@extends('layouts.retail')
@section('title', 'Order Medicines — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="catalogue()">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Order Medicines</h1>
        @if($isOffNetwork)
            <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full">
                Off-network pricing applies
            </span>
        @endif
    </div>

    {{-- Search & filter --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name or SKU..."
               class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                      focus:outline-none focus:ring-2 focus:ring-green-500">
        <select name="category"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                       focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-5 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
            Search
        </button>
    </form>

    {{-- Cart summary bar --}}
    <div x-show="cartCount > 0"
         class="bg-green-700 text-white rounded-xl px-5 py-3 flex items-center justify-between">
        <span class="text-sm font-medium" x-text="cartCount + ' item(s) in cart'"></span>
        <a href="/retail/orders" class="text-sm bg-white text-green-700 px-4 py-1.5 rounded-lg font-medium hover:bg-green-50">
            Review Order
        </a>
    </div>

    {{-- Product grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @forelse($products as $product)
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 text-sm">{{ $product->generic_name }}</p>
                    @if($product->brand_name)
                        <p class="text-xs text-gray-400">{{ $product->brand_name }}</p>
                    @endif
                </div>
                <button onclick="toggleFavourite({{ $product->product_id }})"
                        class="text-lg {{ in_array($product->product_id, $favouriteIds) ? 'text-yellow-400' : 'text-gray-200' }} hover:text-yellow-400 transition-colors">
                    ★
                </button>
            </div>

            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded hidden sm:inline-flex">{{ $product->therapeutic_category }}</span>
                <span class="text-xs text-gray-400 hidden sm:inline">{{ $product->unit_size }}</span>
            </div>

            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $currency['symbol'] }} {{ number_format($product->unit_price, $currency['decimal_places']) }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $product->supplier_name }}</p>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                    {{ $product->stock_status === 'IN_STOCK' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $product->stock_status === 'IN_STOCK' ? 'In Stock' : 'Low Stock' }}
                </span>
            </div>

            <div class="mt-auto flex gap-2">
                <input type="number" min="1" value="1"
                       id="qty_{{ $product->price_list_id }}"
                       class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center
                              focus:outline-none focus:ring-2 focus:ring-green-500">
                <button onclick="addToCart({{ $product->product_id }}, {{ $product->price_list_id }}, '{{ $product->generic_name }}')"
                        class="flex-1 bg-green-700 hover:bg-green-800 text-white text-sm py-2 rounded-lg transition-colors">
                    Add to Order
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400">
            No products found
        </div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>

</div>

<script>
function catalogue() {
    return {
        cartCount: parseInt(localStorage.getItem('dm_cart_count') || '0'),
    }
}

function addToCart(productId, priceListId, name) {
    const qty = parseInt(document.getElementById('qty_' + priceListId).value) || 1;
    let cart = JSON.parse(localStorage.getItem('dm_cart') || '[]');
    const existing = cart.findIndex(i => i.price_list_id === priceListId);
    if (existing >= 0) {
        cart[existing].quantity += qty;
    } else {
        cart.push({ product_id: productId, price_list_id: priceListId, name: name, quantity: qty, payment_type: 'CASH' });
    }
    localStorage.setItem('dm_cart', JSON.stringify(cart));
    localStorage.setItem('dm_cart_count', cart.length);
    window.dispatchEvent(new CustomEvent('cart-updated'));
    alert(name + ' added to order.');
}

function toggleFavourite(productId) {
    fetch('/api/v1/favourites/' + productId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
    }).then(() => location.reload());
}
</script>
@endsection
