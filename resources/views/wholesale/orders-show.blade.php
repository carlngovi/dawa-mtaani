@extends('layouts.wholesale')
@section('title', 'Order ' . substr($order->ulid, -8) . ' — Wholesale')
@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <a href="/wholesale/orders" class="text-xs text-green-700 hover:underline mb-2 inline-block">← Order Queue</a>
            <h1 class="text-2xl font-bold text-gray-900">Order {{ substr($order->ulid, -8) }}</h1>
            <div class="flex items-center gap-3 mt-2">
                @php
                    $badge = match($order->status) {
                        'PENDING'    => 'bg-amber-100 text-amber-700',
                        'CONFIRMED'  => 'bg-blue-100 text-blue-700',
                        'PACKED'     => 'bg-purple-100 text-purple-700',
                        'DISPATCHED' => 'bg-green-100 text-green-700',
                        'DELIVERED'  => 'bg-green-100 text-green-700',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $order->status }}</span>
                <span class="text-xs text-gray-400">
                    {{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                    ({{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }})
                </span>
                @if($order->order_type)
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ $order->order_type }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Status timeline --}}
    @php
        $steps = ['PENDING','CONFIRMED','PACKED','DISPATCHED','DELIVERED'];
        $currentIdx = array_search($order->status, $steps);
        if ($currentIdx === false) $currentIdx = -1;
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            @foreach($steps as $idx => $step)
            <div class="flex flex-col items-center flex-1">
                <div @class([
                    'flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold',
                    'bg-green-600 text-white' => $idx <= $currentIdx,
                    'bg-gray-200 text-gray-400' => $idx > $currentIdx,
                    'ring-4 ring-green-200' => $idx === $currentIdx,
                ])>{{ $idx + 1 }}</div>
                <span @class([
                    'mt-1 text-xs font-medium',
                    'text-green-700' => $idx <= $currentIdx,
                    'text-gray-400' => $idx > $currentIdx,
                ])>{{ $step }}</span>
            </div>
            @if(! $loop->last)
            <div @class([
                'h-0.5 flex-1 -mt-5 mx-1 rounded',
                'bg-green-500' => $idx < $currentIdx,
                'bg-gray-200' => $idx >= $currentIdx,
            ])></div>
            @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- Retail Facility --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Retail Facility</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-gray-400">Name</dt><dd class="text-gray-800 font-medium">{{ $order->retail_name }}</dd></div>
                    <div><dt class="text-xs text-gray-400">County</dt><dd class="text-gray-800">{{ $order->retail_county }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Phone</dt><dd class="text-gray-800">{{ $order->retail_phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">PPB Licence</dt><dd class="text-gray-800 font-mono text-xs">{{ $order->retail_ppb ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Order Lines --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Order Lines</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Product</th>
                            <th class="px-5 py-3 text-left hidden md:table-cell">SKU</th>
                            <th class="px-5 py-3 text-right">Qty</th>
                            <th class="px-5 py-3 text-right">Unit Price</th>
                            <th class="px-5 py-3 text-right">Line Total</th>
                            <th class="px-5 py-3 text-left">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($lines as $line)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $line->generic_name }}</p>
                                <p class="text-xs text-gray-400">{{ $line->brand_name }} — {{ $line->unit_size }}</p>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500 hidden md:table-cell">{{ $line->sku_code }}</td>
                            <td class="px-5 py-3 text-right text-gray-700">{{ $line->quantity }}</td>
                            <td class="px-5 py-3 text-right text-gray-700">{{ $currency['symbol'] }} {{ number_format($line->unit_price, $currency['decimal_places']) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800">{{ $currency['symbol'] }} {{ number_format($line->line_total, $currency['decimal_places']) }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $line->payment_type === 'CREDIT' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $line->payment_type ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-gray-900">{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</td>
                            <td></td>
                        </tr>
                        @if($order->credit_amount > 0)
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right text-xs text-gray-500">Credit</td>
                            <td class="px-5 py-2 text-right text-xs text-blue-600">{{ $currency['symbol'] }} {{ number_format($order->credit_amount, $currency['decimal_places']) }}</td>
                            <td></td>
                        </tr>
                        @endif
                        @if($order->cash_amount > 0)
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right text-xs text-gray-500">Cash</td>
                            <td class="px-5 py-2 text-right text-xs text-gray-600">{{ $currency['symbol'] }} {{ number_format($order->cash_amount, $currency['decimal_places']) }}</td>
                            <td></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Courier / Tracking --}}
            @if($courier)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Dispatch Details</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-gray-400">Tracking Reference</dt><dd class="text-gray-800 font-mono">{{ $courier->courier_reference }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Dispatched At</dt><dd class="text-gray-800">{{ \Carbon\Carbon::parse($courier->assigned_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</dd></div>
                </dl>
            </div>
            @endif

            {{-- Audit Trail --}}
            @if($auditLogs->isNotEmpty())
            <details class="bg-white rounded-xl border border-gray-200">
                <summary class="px-5 py-4 text-sm font-semibold text-gray-700 cursor-pointer hover:bg-gray-50">Audit Trail ({{ $auditLogs->count() }})</summary>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-2 text-left">Date</th>
                            <th class="px-5 py-2 text-left">Action</th>
                            <th class="px-5 py-2 text-left">Actor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($auditLogs as $log)
                        <tr>
                            <td class="px-5 py-2 text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}</td>
                            <td class="px-5 py-2 text-xs text-gray-700 font-medium">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td class="px-5 py-2 text-xs text-gray-500">{{ $log->actor_name ?? 'System' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
            @endif
        </div>

        {{-- Action panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Actions</h3>

                @if($order->status === 'PENDING')
                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/confirm">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">
                        Confirm Order
                    </button>
                </form>
                <p class="text-xs text-gray-400">Confirming means you will fulfil this order.</p>
                @endif

                @if($order->status === 'CONFIRMED')
                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/pack">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        Mark as Packed
                    </button>
                </form>
                <p class="text-xs text-gray-400">Confirm all items have been picked and packed.</p>
                @endif

                @if($order->status === 'PACKED')
                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/dispatch">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                        Dispatch to SGA
                    </button>
                </form>
                <p class="text-xs text-gray-400">Triggers courier notification and generates tracking reference.</p>
                @endif

                @if($order->status === 'DISPATCHED')
                <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-3 py-3 rounded-lg">
                    Order dispatched. Awaiting delivery confirmation.
                    @if($courier)
                    <p class="mt-1 font-mono">Tracking: {{ $courier->courier_reference }}</p>
                    @endif
                </div>
                @endif

                @if($order->status === 'DELIVERED')
                <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-3 py-3 rounded-lg">
                    Order delivered and confirmed.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection