@extends('layouts.wholesale')
@section('title', 'Stock Management — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Stock Management</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">In Stock</p>
            <p class="text-2xl font-bold text-green-700">{{ $summary['in_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Low Stock</p>
            <p class="text-2xl font-bold text-amber-600">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Out of Stock</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['out_of_stock'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Category</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Quantity</th>
                    <th class="px-5 py-3 text-left">Last Updated</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stockItems as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $item->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $item->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $item->therapeutic_category }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $item->stock_status === 'IN_STOCK' ? 'bg-green-100 text-green-700' :
                               ($item->stock_status === 'LOW_STOCK' ? 'bg-amber-100 text-amber-700' :
                               'bg-red-100 text-red-700') }}">
                            {{ str_replace('_', ' ', $item->stock_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-700">
                        {{ $item->stock_quantity !== null ? number_format($item->stock_quantity) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        <button onclick="quickUpdate({{ $item->product_id }}, '{{ $item->stock_status }}')"
                                class="text-xs text-green-700 hover:underline">Update</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No stock records yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $stockItems->links() }}</div>
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
