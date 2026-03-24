@extends('layouts.admin')
@section('title', 'Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Orders</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Today</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_today']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Disputed</p>
            <p class="text-2xl font-bold {{ $stats['disputed'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($stats['disputed']) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All statuses</option>
                @foreach(['PENDING','CONFIRMED','PICKING','PACKED','DISPATCHED','DELIVERED','DISPUTED','CANCELLED'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="membership" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All membership</option>
                <option value="NETWORK" {{ request('membership') == 'NETWORK' ? 'selected' : '' }}>Network</option>
                <option value="OFF_NETWORK" {{ request('membership') == 'OFF_NETWORK' ? 'selected' : '' }}>Off-network</option>
            </select>
            <select name="channel" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All channels</option>
                @foreach(['WEB','WHATSAPP','OFFLINE_QR'] as $c)
                    <option value="{{ $c }}" {{ request('channel') == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="submit" class="px-4 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $order->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->county }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED' => 'bg-green-100 text-green-700',
                                'CANCELLED' => 'bg-red-100 text-red-700',
                                'DISPUTED'  => 'bg-red-100 text-red-700',
                                'DISPATCHED'=> 'bg-blue-100 text-blue-700',
                                'PENDING'   => 'bg-amber-100 text-amber-700',
                                default     => 'bg-gray-100 text-gray-600'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $order->source_channel }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $order->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $order->network_membership }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
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
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
