@extends('layouts.admin')
@section('title', 'Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Orders</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Today</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total_today']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-2xl font-bold text-amber-400">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Disputed</p>
            <p class="text-2xl font-bold {{ $stats['disputed'] > 0 ? 'text-red-400' : 'text-white' }}">{{ number_format($stats['disputed']) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <select name="status" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">All statuses</option>
                @foreach(['PENDING','CONFIRMED','PICKING','PACKED','DISPATCHED','DELIVERED','DISPUTED','CANCELLED'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="membership" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">All membership</option>
                <option value="NETWORK" {{ request('membership') == 'NETWORK' ? 'selected' : '' }}>Network</option>
                <option value="OFF_NETWORK" {{ request('membership') == 'OFF_NETWORK' ? 'selected' : '' }}>Off-network</option>
            </select>
            <select name="channel" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">All channels</option>
                @foreach(['WEB','WHATSAPP','OFFLINE_QR'] as $c)
                    <option value="{{ $c }}" {{ request('channel') == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <button type="submit" class="px-4 py-2 bg-yellow-400 text-gray-900 font-medium text-sm rounded-lg hover:bg-yellow-500">Filter</button>
        </div>
    </form>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Channel</th>
                    <th class="px-5 py-3 text-left">Membership</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $order->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->county }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED' => 'bg-green-900/30 text-green-400 border border-green-800',
                                'CANCELLED' => 'bg-red-900/30 text-red-400 border border-red-800',
                                'DISPUTED'  => 'bg-red-900/30 text-red-400 border border-red-800',
                                'DISPATCHED'=> 'bg-blue-900/30 text-blue-400 border border-blue-800',
                                'PENDING'   => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                default     => 'bg-gray-700 text-gray-400'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $order->source_channel }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $order->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-gray-700 text-gray-400' }}">
                            {{ $order->network_membership }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No orders found</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
