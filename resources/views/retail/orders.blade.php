@extends('layouts.retail')
@section('title', 'My Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED' => 'bg-green-100 text-green-700',
                                'CANCELLED' => 'bg-red-100 text-red-700',
                                'DISPATCHED' => 'bg-blue-100 text-blue-700',
                                'PENDING' => 'bg-amber-100 text-amber-700',
                                'CONFIRMED' => 'bg-blue-100 text-blue-600',
                                default => 'bg-gray-100 text-gray-600'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->order_type }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="/retail/orders/{{ $order->ulid }}" class="text-green-700 text-xs hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>

</div>
@endsection
