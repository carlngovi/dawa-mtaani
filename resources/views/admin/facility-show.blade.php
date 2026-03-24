@extends('layouts.admin')

@section('title', '{{ $facility->facility_name }} — Dawa Mtaani')

@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-4">
        <a href="/admin/facilities" class="text-sm text-gray-400 hover:text-gray-600">← Facilities</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $facility->facility_name }}</h1>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
            {{ $facility->facility_status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $facility->facility_status }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Facility details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Facility Details</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">PPB Licence</span>
                    <span class="font-mono text-gray-700">{{ $facility->ppb_licence_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Type</span>
                    <span class="text-gray-700">{{ $facility->ppb_facility_type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">PPB Status</span>
                    <span class="text-gray-700">{{ $facility->ppb_licence_status }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Membership</span>
                    <span class="text-gray-700">{{ $facility->network_membership }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">County</span>
                    <span class="text-gray-700">{{ $facility->county }}, {{ $facility->ward }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Phone</span>
                    <span class="text-gray-700">{{ $facility->phone }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">GPS</span>
                    @if($facility->latitude)
                        <span class="text-green-600 text-xs">{{ $facility->latitude }}, {{ $facility->longitude }}</span>
                    @else
                        <span class="text-amber-500 text-xs">Not captured</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Authorised placers --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Authorised Placers</h3>
            @forelse($authorisedPlacers as $placer)
                <div class="text-sm py-2 border-b border-gray-100 last:border-0">
                    <p class="font-medium text-gray-800">{{ $placer->name }}</p>
                    <p class="text-xs text-gray-400">{{ $placer->email }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">No authorised placers</p>
            @endforelse
        </div>

        {{-- Active flags --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Active Flags</h3>
            @forelse($flags as $flag)
                <div class="text-sm py-2 border-b border-gray-100 last:border-0">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                        {{ $flag->reason }}
                    </span>
                    @if($flag->notes)
                        <p class="text-xs text-gray-400 mt-1">{{ $flag->notes }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400">No active flags</p>
            @endforelse
        </div>

    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Recent Orders</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Type</th>
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
                                {{ $order->status === 'DELIVERED' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $order->order_type }}</td>
                        <td class="px-5 py-3 text-right text-gray-800">{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">No orders yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
