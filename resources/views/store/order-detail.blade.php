@extends('layouts.app')
@section('title', 'Order ' . substr($order->ulid, -8) . ' — Dawa Mtaani')
@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{
         status: '{{ $order->status }}',
         poll: null,

         init() {
             if (['PAYMENT_PENDING', 'CONFIRMED', 'PREPARING'].includes(this.status)) {
                 this.poll = setInterval(() => this.checkStatus(), 10000);
             }
         },

         async checkStatus() {
             try {
                 const res = await fetch('/api/store/orders/{{ $order->ulid }}', {
                     headers: { 'Accept': 'application/json' }
                 });
                 const json = await res.json();
                 if (json.status === 'success') {
                     const newStatus = json.data.status;
                     if (newStatus !== this.status) {
                         this.status = newStatus;
                         // Reload page to get updated server-rendered content
                         if (['COLLECTED', 'CANCELLED'].includes(newStatus)) {
                             clearInterval(this.poll);
                         }
                         window.location.reload();
                     }
                 }
             } catch (e) { /* silent */ }
         },

         destroy() {
             if (this.poll) clearInterval(this.poll);
         }
     }">

    {{-- Pending payment banner --}}
    @if($isPending && $order->status === 'PAYMENT_PENDING')
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="h-5 w-5 animate-spin text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Waiting for M-Pesa payment confirmation. This page will update automatically.
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <a href="/store/orders" class="text-xs text-green-700 hover:underline mb-2 inline-block">← My Orders</a>
            <h1 class="text-2xl font-bold text-gray-900">Order {{ substr($order->ulid, -8) }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
            </p>
        </div>
        @php
            $statusBadge = match($order->status) {
                'PAYMENT_PENDING' => 'bg-amber-100 text-amber-700',
                'CONFIRMED'       => 'bg-blue-100 text-blue-700',
                'PREPARING'       => 'bg-blue-100 text-blue-700',
                'READY'           => 'bg-green-100 text-green-700',
                'COLLECTED'       => 'bg-green-100 text-green-700',
                'CANCELLED'       => 'bg-red-100 text-red-700',
                default           => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">
            {{ str_replace('_', ' ', $order->status) }}
        </span>
    </div>

    {{-- Status tracker --}}
    @if(! in_array($order->status, ['CANCELLED']))
        @include('store.components.order-status-tracker', [
            'status' => $order->status,
            'orderUlid' => $order->ulid,
        ])
    @else
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
            This order was cancelled.
            @if($order->rejection_reason)
                Reason: {{ $order->rejection_reason }}
            @endif
        </div>
    @endif

    {{-- Collection details --}}
    @if(in_array($order->status, ['CONFIRMED', 'PREPARING', 'READY']))
    <div class="bg-green-50 border border-green-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-green-800 mb-2">
            @if($order->status === 'READY')
                Ready for collection
            @else
                Being prepared
            @endif
        </h3>
        <p class="text-sm text-green-700">{{ $order->facility_name }}</p>
        @if($order->physical_address)
            <p class="text-xs text-green-600 mt-0.5">{{ $order->physical_address }}</p>
        @endif
        <p class="text-xs text-green-600 mt-0.5">{{ $order->county }}</p>
        @if($order->collection_window_start)
        <p class="text-xs text-green-700 mt-2 font-medium">
            Collection window:
            {{ \Carbon\Carbon::parse($order->collection_window_start)->timezone('Africa/Nairobi')->format('d M, H:i') }}
            –
            {{ \Carbon\Carbon::parse($order->collection_window_end)->timezone('Africa/Nairobi')->format('H:i') }}
        </p>
        @endif
    </div>
    @endif

    {{-- Order lines --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Items</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-right">Qty</th>
                    <th class="px-5 py-3 text-right">Price</th>
                    <th class="px-5 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($lines as $line)
                <tr>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $line->generic_name }}</p>
                        @if($line->brand_name)
                            <p class="text-xs text-gray-400">{{ $line->brand_name }}</p>
                        @endif
                    </td>
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
        <div class="px-5 py-4 border-t border-gray-100 space-y-1">
            @if($order->discount_amount > 0)
            <div class="flex justify-between text-sm text-green-600">
                <span>Discount</span>
                <span>-{{ $currency['symbol'] }} {{ number_format($order->discount_amount, $currency['decimal_places']) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold text-gray-900 pt-1">
                <span>Total</span>
                <span>{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</span>
            </div>
        </div>
    </div>

    {{-- Payment info --}}
    @if($order->paid_at)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment</h3>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Method</span>
                <span class="text-gray-800">M-Pesa</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Paid at</span>
                <span class="text-gray-800">
                    {{ \Carbon\Carbon::parse($order->paid_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                </span>
            </div>
            @if($order->mpesa_receipt_number)
            <div class="flex justify-between">
                <span class="text-gray-500">Receipt</span>
                <span class="text-gray-800 font-mono">{{ $order->mpesa_receipt_number }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Reorder button --}}
    @if(in_array($order->status, ['COLLECTED', 'CANCELLED']))
    <div class="text-center">
        <a href="/store/{{ $order->facility_ulid }}"
           class="inline-block px-6 py-2.5 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">
            Order Again
        </a>
    </div>
    @endif

</div>
@endsection