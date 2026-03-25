@extends('layouts.retail')
@section('title', 'Order — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-4">
        <a href="/retail/orders" class="text-sm text-gray-400 hover:text-gray-600">← Orders</a>
        <h1 class="text-xl font-bold text-gray-900">Order {{ substr($order->ulid, -8) }}</h1>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
            {{ $order->status === 'DELIVERED' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $order->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-700">Order Summary</h3>
            <div class="flex justify-between"><span class="text-gray-400">Type</span><span>{{ $order->order_type }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Channel</span><span>{{ $order->source_channel }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Total</span>
                <span class="font-bold">{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</span>
            </div>
            <div class="flex justify-between"><span class="text-gray-400">Placed</span>
                <span>{{ \Carbon\Carbon::parse($order->submitted_at)->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Order Lines</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Supplier</th>
                    <th class="px-5 py-3 text-center">Qty</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($lines as $line)
                <tr>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $line->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $line->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $line->supplier_name }}</td>
                    <td class="px-5 py-3 text-center text-gray-800">{{ $line->quantity }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">
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

</div>
@endsection
