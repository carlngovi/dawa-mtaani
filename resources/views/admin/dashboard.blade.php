@extends('layouts.admin')

@section('title', 'Dashboard — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Network Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $today }} &middot; All times EAT</p>
        </div>
        @if($ppbIsStale)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-2 rounded-lg">
                ⚠ PPB registry data is stale. Please upload a fresh export.
            </div>
        @endif
    </div>

    {{-- Active alerts --}}
    @if($activeAlerts->count() > 0)
        <div class="space-y-2">
            @foreach($activeAlerts as $alert)
                <div class="flex items-center justify-between px-4 py-3 rounded-lg border text-sm
                    {{ $alert->severity === 'CRITICAL' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Orders today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySummary?->total_orders ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">GMV today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
                {{ $currency['symbol'] }} {{ number_format($todaySummary?->total_gmv ?? 0, $currency['decimal_places']) }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Active facilities</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySummary?->active_facilities ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Open disputes</p>
            <p class="text-3xl font-bold {{ $openDisputes > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                {{ $openDisputes }}
            </p>
        </div>
    </div>

    {{-- Network vs Off-network --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Network facilities</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Orders</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($networkSummary?->total_orders ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">GMV</p>
                    <p class="text-xl font-bold text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($networkSummary?->total_gmv ?? 0, 0) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Off-network facilities</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Orders</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($offNetworkSummary?->total_orders ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">GMV</p>
                    <p class="text-xl font-bold text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($offNetworkSummary?->total_gmv ?? 0, 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Recent orders (last 24h)</h3>
            <a href="/admin/orders" class="text-xs text-green-700 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">County</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ substr($order->ulid, -8) }}</td>
                            <td class="px-5 py-3 text-gray-800">{{ $order->facility_name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $order->county }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($order->status) {
                                        'DELIVERED' => 'bg-green-100 text-green-700',
                                        'CANCELLED' => 'bg-red-100 text-red-700',
                                        'DISPATCHED' => 'bg-blue-100 text-blue-700',
                                        'PENDING' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-600'
                                    } }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-800">
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
        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
            {{ $gpsPendingCount }} active {{ Str::plural('facility', $gpsPendingCount) }} have no GPS coordinates.
            <a href="/admin/facilities?gps_pending=1" class="underline ml-1">View GPS pending</a>
        </div>
    @endif

    {{-- Quick links --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/admin/ppb-registry"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-800">PPB Registry</p>
            <p class="text-xs text-gray-400 mt-1">
                Retail · Hospital · Wholesale · Manufacturer
            </p>
            @if($ppbIsStale)
                <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                    Stale — upload needed
                </span>
            @else
                <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                    Up to date
                </span>
            @endif
        </a>
        <a href="/admin/facilities?gps_pending=1"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-800">GPS Pending</p>
            <p class="text-xs text-gray-400 mt-1">Facilities without coordinates</p>
            <p class="text-2xl font-bold {{ $gpsPendingCount > 0 ? 'text-amber-600' : 'text-green-700' }} mt-2">
                {{ $gpsPendingCount }}
            </p>
        </a>
        <a href="/admin/disputes"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-800">Open Disputes</p>
            <p class="text-xs text-gray-400 mt-1">Pending resolution</p>
            <p class="text-2xl font-bold {{ $openDisputes > 0 ? 'text-red-600' : 'text-green-700' }} mt-2">
                {{ $openDisputes }}
            </p>
        </a>
        <a href="/admin/quality-flags"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 transition-colors">
            <p class="text-sm font-semibold text-gray-800">Quality Flags</p>
            <p class="text-xs text-gray-400 mt-1">Pharmacovigilance reports</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">→</p>
        </a>
    </div>

</div>

@endsection
