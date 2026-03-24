@extends('layouts.wholesale')
@section('title', 'Order Queue — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Order Queue</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-2xl font-bold {{ $stats['pending'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">In Progress</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Dispatched</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['dispatched'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All statuses</option>
            @foreach(['PENDING','CONFIRMED','PICKING','PACKED','DISPATCHED','DELIVERED'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">Filter</button>
        @if(request('status'))
            <a href="/wholesale/orders" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Retail Facility</th>
                    <th class="px-5 py-3 text-left">County</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Channel</th>
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
                        <p class="font-medium text-gray-800">{{ $order->retail_name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->ward }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $order->county }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'PENDING' => 'bg-amber-100 text-amber-700',
                                'CONFIRMED','PICKING' => 'bg-blue-100 text-blue-600',
                                'PACKED' => 'bg-purple-100 text-purple-700',
                                'DISPATCHED' => 'bg-indigo-100 text-indigo-700',
                                'DELIVERED' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $order->source_channel }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        @if($order->status === 'PENDING')
                            <form method="POST" action="/api/v1/wholesale/orders/{{ $order->ulid }}/status" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="CONFIRMED">
                                <button class="text-xs text-blue-600 hover:underline">Confirm</button>
                            </form>
                        @elseif($order->status === 'PACKED')
                            <form method="POST" action="/api/v1/wholesale/orders/{{ $order->ulid }}/dispatch" class="inline">
                                @csrf
                                <button class="text-xs text-green-700 hover:underline">Dispatch</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">No orders</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>

</div>
@endsection
