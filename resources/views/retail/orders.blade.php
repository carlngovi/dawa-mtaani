@extends('layouts.retail')
@section('title', 'My Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">My Orders</h1>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED' => 'bg-green-900/30 text-green-400',
                                'CANCELLED' => 'bg-red-900/30 text-red-400',
                                'DISPATCHED' => 'bg-blue-900/30 text-blue-400',
                                'PENDING' => 'bg-amber-900/30 text-amber-400',
                                'CONFIRMED' => 'bg-blue-900/30 text-yellow-400',
                                default => 'bg-gray-700 text-gray-400'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->order_type }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="/retail/orders/{{ $order->ulid }}" class="text-green-400 text-xs hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $orders->links() }}</div>
    </div>

</div>
@endsection
