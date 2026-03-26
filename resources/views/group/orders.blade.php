@extends('layouts.app')
@section('title', 'Order History — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Order History</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $group->group_name }} — all outlets</p>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="outlet"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All outlets</option>
            @foreach($outlets as $outlet)
                <option value="{{ $outlet->ulid }}" {{ request('outlet') === $outlet->ulid ? 'selected' : '' }}>
                    {{ $outlet->facility_name }}
                </option>
            @endforeach
        </select>
        <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All statuses</option>
            @foreach(['PENDING','CONFIRMED','PACKED','DISPATCHED','DELIVERED','CANCELLED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['outlet', 'status']))
            <a href="/group/orders"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Outlet</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Placer</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Payment</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">
                            {{ substr($order->ulid, -8) }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $order->facility_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell">
                            {{ $order->placer_name }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($order->status) {
                                    'DELIVERED'  => 'bg-green-100 text-green-700',
                                    'DISPATCHED' => 'bg-blue-100 text-blue-700',
                                    'CANCELLED'  => 'bg-red-100 text-red-700',
                                    'PENDING'    => 'bg-amber-100 text-amber-700',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $order->payment_type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">
                            {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No orders found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $orders->links() }}</div>

</div>
@endsection