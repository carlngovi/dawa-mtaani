@extends('layouts.wholesale')
@section('title', 'Stock Management — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Stock Management</h1>

    <form method="GET" action="/wholesale/stock">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by name or SKU..."
               class="w-full sm:w-80 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">In Stock</p>
            <p class="text-2xl font-bold text-green-400">{{ $summary['in_stock'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Low Stock</p>
            <p class="text-2xl font-bold text-amber-400">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Out of Stock</p>
            <p class="text-2xl font-bold text-red-400">{{ $summary['out_of_stock'] }}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Quantity</th>
                    <th class="px-5 py-3 text-left">Last Updated</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($stockItems as $item)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $item->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $item->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $item->therapeutic_category }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $item->stock_status === 'IN_STOCK' ? 'bg-green-900/30 text-green-400' :
                               ($item->stock_status === 'LOW_STOCK' ? 'bg-amber-900/30 text-amber-400' :
                               'bg-red-900/30 text-red-400') }}">
                            {{ str_replace('_', ' ', $item->stock_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-300">
                        {{ $item->stock_quantity !== null ? number_format($item->stock_quantity) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        <button onclick="quickUpdate({{ $item->product_id }}, '{{ $item->stock_status }}')"
                                class="text-xs text-green-400 hover:underline">Update</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No stock records yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $stockItems->links() }}</div>
    </div>

</div>

<script>
function quickUpdate(productId, currentStatus) {
    const statuses = ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'];
    const next = statuses[(statuses.indexOf(currentStatus) + 1) % statuses.length];
    if (!confirm('Change status to ' + next + '?')) return;

    fetch('/api/v1/wholesale/stock-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ updates: [{ product_id: productId, stock_status: next }] })
    }).then(r => r.json()).then(() => location.reload());
}
</script>
@endsection
