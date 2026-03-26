@extends('layouts.retail')
@section('title', 'Dashboard — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $facility)
        <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
            ⚠ Your account is not linked to a facility yet. Contact your network administrator.
        </div>
    @else

    <div>
        <h1 class="text-2xl font-bold text-white">{{ $facility->facility_name }}</h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ $facility->county }} · {{ $facility->network_membership }}
            @if($facility->network_membership === 'NETWORK')
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400 ml-2">Network Member</span>
            @endif
        </p>
    </div>

    {{-- PPB warning --}}
    @if($facility->ppb_licence_status !== 'VALID')
        <div class="bg-red-900/20 border border-red-800 text-red-300 text-sm px-4 py-3 rounded-lg">
            ⚠ Your PPB licence status is <strong>{{ $facility->ppb_licence_status }}</strong>.
            Contact your network administrator.
        </div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Orders this month</p>
            <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">GMV this month</p>
            <p class="text-3xl font-bold text-white mt-1">
                {{ $currency['symbol'] }} {{ number_format($monthGmv, $currency['decimal_places']) }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Open disputes</p>
            <p class="text-3xl font-bold {{ $pendingDisputes > 0 ? 'text-red-400' : 'text-white' }} mt-1">
                {{ $pendingDisputes }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Low stock items</p>
            <p class="text-3xl font-bold {{ $lowStockCount > 0 ? 'text-amber-400' : 'text-white' }} mt-1">
                {{ $lowStockCount }}
            </p>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/retail/catalogue" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">🛒</p>
            <p class="text-sm font-medium">Order Medicines</p>
        </a>
        <a href="/retail/orders" class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">📦</p>
            <p class="text-sm font-medium text-gray-300">My Orders</p>
        </a>
        <a href="/retail/pos" class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">💊</p>
            <p class="text-sm font-medium text-gray-300">Point of Sale</p>
        </a>
        <a href="/retail/credit" class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">💳</p>
            <p class="text-sm font-medium text-gray-300">Credit</p>
        </a>
    </div>

    {{-- Recent orders --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-300">Recent Orders</h3>
            <a href="/retail/orders" class="text-xs text-green-400 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED'  => 'bg-green-900/30 text-green-400',
                                'CANCELLED'  => 'bg-red-900/30 text-red-400',
                                'DISPATCHED' => 'bg-blue-900/30 text-blue-400',
                                'PENDING'    => 'bg-amber-900/30 text-amber-400',
                                default      => 'bg-gray-700 text-gray-400'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    @endif

</div>
@endsection
