@extends('layouts.retail')
@section('title', 'Dashboard — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $facility)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
            ⚠ Your account is not linked to a facility yet. Contact your network administrator.
        </div>
    @else

    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $facility->facility_name }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $facility->county }} · {{ $facility->network_membership }}
            @if($facility->network_membership === 'NETWORK')
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 ml-2">Network Member</span>
            @endif
        </p>
    </div>

    {{-- PPB warning --}}
    @if($facility->ppb_licence_status !== 'VALID')
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
            ⚠ Your PPB licence status is <strong>{{ $facility->ppb_licence_status }}</strong>.
            Contact your network administrator.
        </div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Orders this month</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">GMV this month</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
                {{ $currency['symbol'] }} {{ number_format($monthGmv, $currency['decimal_places']) }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Open disputes</p>
            <p class="text-3xl font-bold {{ $pendingDisputes > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                {{ $pendingDisputes }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Low stock items</p>
            <p class="text-3xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                {{ $lowStockCount }}
            </p>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/retail/catalogue" class="bg-green-700 hover:bg-green-800 text-white rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">🛒</p>
            <p class="text-sm font-medium">Order Medicines</p>
        </a>
        <a href="/retail/orders" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">📦</p>
            <p class="text-sm font-medium text-gray-700">My Orders</p>
        </a>
        <a href="/retail/pos" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">💊</p>
            <p class="text-sm font-medium text-gray-700">Point of Sale</p>
        </a>
        <a href="/retail/credit" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="text-2xl mb-1">💳</p>
            <p class="text-sm font-medium text-gray-700">Credit</p>
        </a>
    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Recent Orders</h3>
            <a href="/retail/orders" class="text-xs text-green-700 hover:underline">View all</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ substr($order->ulid, -8) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'DELIVERED'  => 'bg-green-100 text-green-700',
                                'CANCELLED'  => 'bg-red-100 text-red-700',
                                'DISPATCHED' => 'bg-blue-100 text-blue-700',
                                'PENDING'    => 'bg-amber-100 text-amber-700',
                                default      => 'bg-gray-100 text-gray-600'
                            } }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-800">
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
        </table>
    </div>

    @endif

</div>
@endsection
