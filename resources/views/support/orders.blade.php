@extends('layouts.app')
@section('title', 'Order Lookup — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order Lookup</h1>
            <p class="text-sm text-gray-500 mt-1">Search by order reference (full or partial ULID)</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="ref" value="{{ request('ref') }}"
               placeholder="Enter order reference..."
               class="flex-1 max-w-md px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Search
        </button>
        @if(request('ref'))
            <a href="/support/orders"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    @if(! $searched)
    {{-- Prompt --}}
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">Enter an order reference to look up</p>
    </div>

    @elseif(! $order)
    {{-- No result --}}
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No order found for "{{ request('ref') }}"</p>
    </div>

    @else
    {{-- Order detail --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Order Detail</h3>
        </div>
        <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
            @php
                $statusBadge = match($order->status) {
                    'DELIVERED'  => 'bg-green-100 text-green-700',
                    'DISPATCHED' => 'bg-blue-100 text-blue-700',
                    'CANCELLED'  => 'bg-red-100 text-red-700',
                    'PENDING'    => 'bg-amber-100 text-amber-700',
                    default      => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Order Ref</dt>
                <dd class="mt-1 text-sm font-mono font-medium text-gray-800">{{ substr($order->ulid, -12) }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Facility</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800">{{ $order->facility_name }}</dd>
                <dd class="text-xs text-gray-400">{{ $order->county }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Phone</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ $order->facility_phone ?? '—' }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Status</dt>
                <dd class="mt-1">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge }}">
                        {{ $order->status }}
                    </span>
                </dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Payment</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ $order->payment_type }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Channel</dt>
                <dd class="mt-1 text-sm text-gray-800">{{ $order->source_channel ?? '—' }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Total</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">
                    {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                </dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Date</dt>
                <dd class="mt-1 text-sm text-gray-800">
                    {{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Order lines --}}
    @if($orderLines->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Order Lines</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Pack Size</th>
                    <th class="px-5 py-3 text-right">Qty</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($orderLines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $line->product_name }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $line->pack_size }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">{{ $line->quantity }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">
                        {{ $currency['symbol'] }} {{ number_format($line->unit_price, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($line->line_total, $currency['decimal_places']) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <p class="text-xs text-gray-400">
        Read-only. Direct customers to place a new order or contact their pharmacy directly.
    </p>
    @endif

</div>
@endsection