@extends('layouts.admin')

@section('title', 'Dashboard — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Network Dashboard</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $today }} &middot; All times EAT</p>
        </div>
        @if($ppbIsStale)
            <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-2 rounded-lg">
                ⚠ PPB registry data is stale. Please upload a fresh export.
            </div>
        @endif
    </div>

    {{-- Active alerts --}}
    @if($activeAlerts->count() > 0)
        <div class="space-y-2">
            @foreach($activeAlerts as $alert)
                <div class="flex items-center justify-between px-4 py-3 rounded-lg border text-sm
                    {{ $alert->severity === 'CRITICAL' ? 'bg-red-900/20 border-red-800 text-red-300' : 'bg-amber-900/20 border-amber-800 text-amber-300' }}">
                    <span><strong>{{ $alert->severity }}</strong> — {{ $alert->metric_name }}
                        (expected {{ number_format($alert->expected_value, 0) }},
                        got {{ number_format($alert->actual_value, 0) }})
                    </span>
                    <span class="text-xs opacity-70">{{ \Carbon\Carbon::parse($alert->created_at)->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Orders today</p>
            <p class="text-3xl font-bold text-white mt-1">{{ number_format($todaySummary?->total_orders ?? 0) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">GMV today</p>
            <p class="text-3xl font-bold text-white mt-1">
                {{ $currency['symbol'] }} {{ number_format($todaySummary?->total_gmv ?? 0, $currency['decimal_places']) }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Active facilities</p>
            <p class="text-3xl font-bold text-white mt-1">{{ number_format($todaySummary?->active_facilities ?? 0) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Open disputes</p>
            <p class="text-3xl font-bold {{ $openDisputes > 0 ? 'text-red-400' : 'text-white' }} mt-1">
                {{ $openDisputes }}
            </p>
        </div>
    </div>

    {{-- Network vs Off-network --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Network facilities</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Orders</p>
                    <p class="text-xl font-bold text-gray-200">{{ number_format($networkSummary?->total_orders ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">GMV</p>
                    <p class="text-xl font-bold text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($networkSummary?->total_gmv ?? 0, 0) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Off-network facilities</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Orders</p>
                    <p class="text-xl font-bold text-gray-200">{{ number_format($offNetworkSummary?->total_orders ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">GMV</p>
                    <p class="text-xl font-bold text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($offNetworkSummary?->total_gmv ?? 0, 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-300">Recent orders</h3>
            <a href="/admin/orders" class="text-xs text-green-400 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">County</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-900">
                            <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($order->ulid, -8) }}</td>
                            <td class="px-5 py-3 text-gray-200">{{ $order->facility_name }}</td>
                            <td class="px-5 py-3 text-gray-400">{{ $order->county }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($order->status) {
                                        'DELIVERED' => 'bg-green-900/30 text-green-400 border border-green-800',
                                        'CANCELLED' => 'bg-red-900/30 text-red-400 border border-red-800',
                                        'DISPATCHED' => 'bg-blue-900/30 text-blue-400 border border-blue-800',
                                        'PENDING' => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                        default => 'bg-gray-700 text-gray-400'
                                    } }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-200">
                                {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">
                                {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">
                                No orders in the last 24 hours
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Warnings row --}}
    @if($gpsPendingCount > 0)
        <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
            {{ $gpsPendingCount }} active {{ Str::plural('facility', $gpsPendingCount) }} have no GPS coordinates.
            <a href="/admin/facilities?gps_pending=1" class="underline ml-1">View GPS pending</a>
        </div>
    @endif

    {{-- Quick links --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/admin/ppb-registry"
           class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-200">PPB Registry</p>
            <p class="text-xs text-gray-400 mt-1">
                Retail · Hospital · Wholesale · Manufacturer
            </p>
            @if($ppbIsStale)
                <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium bg-amber-900/30 text-amber-400 border border-amber-800">
                    Stale — upload needed
                </span>
            @else
                <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400 border border-green-800">
                    Up to date
                </span>
            @endif
        </a>
        <a href="/admin/facilities?gps_pending=1"
           class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-200">GPS Pending</p>
            <p class="text-xs text-gray-400 mt-1">Facilities without coordinates</p>
            <p class="text-2xl font-bold {{ $gpsPendingCount > 0 ? 'text-amber-400' : 'text-green-400' }} mt-2">
                {{ $gpsPendingCount }}
            </p>
        </a>
        <a href="/admin/disputes"
           class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-200">Open Disputes</p>
            <p class="text-xs text-gray-400 mt-1">Pending resolution</p>
            <p class="text-2xl font-bold {{ $openDisputes > 0 ? 'text-red-400' : 'text-green-400' }} mt-2">
                {{ $openDisputes }}
            </p>
        </a>
        <a href="/admin/quality-flags"
           class="bg-gray-800 hover:bg-gray-900 border border-gray-700 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-200">Quality Flags</p>
            <p class="text-xs text-gray-400 mt-1">Pharmacovigilance reports</p>
            <p class="text-2xl font-bold text-white mt-2">→</p>
        </a>
    </div>

</div>

@endsection
