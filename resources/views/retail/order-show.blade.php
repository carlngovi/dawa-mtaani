@extends('layouts.retail')
@section('title', 'Order ' . substr($order->ulid, -8) . ' — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ showDisputeModal: false }">

    @if(session('success'))
    <div class="bg-green-900/20 border border-gray-700 text-green-300 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-900/20 border border-gray-700 text-red-300 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="/retail/orders" class="text-sm text-gray-400 hover:text-gray-400">← Orders</a>
        <h1 class="text-xl font-bold text-white">Order {{ substr($order->ulid, -8) }}</h1>
        @php
            $statusBadge = match($order->status) {
                'PENDING'    => 'bg-amber-900/30 text-amber-400',
                'CONFIRMED'  => 'bg-blue-900/30 text-blue-400',
                'PACKED'     => 'bg-purple-900/30 text-purple-400',
                'DISPATCHED' => 'bg-blue-900/30 text-blue-400',
                'DELIVERED'  => 'bg-green-900/30 text-green-400',
                'CANCELLED'  => 'bg-red-900/30 text-red-400',
                default      => 'bg-gray-700 text-gray-400',
            };
        @endphp
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge }}">{{ $order->status }}</span>
    </div>

    {{-- Status timeline --}}
    @php
        $steps = ['PENDING','CONFIRMED','PACKED','DISPATCHED','DELIVERED'];
        $currentIdx = array_search($order->status, $steps);
        if ($currentIdx === false) $currentIdx = -1;
    @endphp
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <div class="flex items-center justify-between">
            @foreach($steps as $idx => $step)
            <div class="flex flex-col items-center flex-1">
                <div @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold',
                    'bg-yellow-400 text-white' => $idx <= $currentIdx,
                    'bg-gray-700/50 text-gray-400' => $idx > $currentIdx,
                    'ring-4 ring-green-200' => $idx === $currentIdx,
                ])>{{ $idx + 1 }}</div>
                <span @class([
                    'mt-1 text-xs font-medium',
                    'text-green-400' => $idx <= $currentIdx,
                    'text-gray-400' => $idx > $currentIdx,
                ])>{{ $step }}</span>
            </div>
            @if(! $loop->last)
            <div @class([
                'h-0.5 flex-1 -mt-5 mx-1 rounded',
                'bg-green-500' => $idx < $currentIdx,
                'bg-gray-700/50' => $idx >= $currentIdx,
            ])></div>
            @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Order summary card --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-300">Order Summary</h3>
            <div class="flex justify-between"><span class="text-gray-400">Type</span><span>{{ $order->order_type }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Channel</span><span>{{ $order->source_channel }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Total</span>
                <span class="font-bold">{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</span>
            </div>
            @if($order->credit_amount > 0)
            <div class="flex justify-between"><span class="text-gray-400">Credit</span>
                <span class="text-yellow-400">{{ $currency['symbol'] }} {{ number_format($order->credit_amount, $currency['decimal_places']) }}</span>
            </div>
            @endif
            @if($order->cash_amount > 0)
            <div class="flex justify-between"><span class="text-gray-400">Cash</span>
                <span>{{ $currency['symbol'] }} {{ number_format($order->cash_amount, $currency['decimal_places']) }}</span>
            </div>
            @endif
            <div class="flex justify-between"><span class="text-gray-400">Placed</span>
                <span>{{ \Carbon\Carbon::parse($order->submitted_at ?? $order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
            </div>
        </div>

        {{-- Dispatch info --}}
        @if($courier)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-300">Dispatch</h3>
            <div class="flex justify-between"><span class="text-gray-400">Courier</span><span>SGA Logistics</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Tracking</span><span class="font-mono text-xs">{{ $courier->courier_reference }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Dispatched</span>
                <span>{{ \Carbon\Carbon::parse($courier->assigned_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
            </div>
        </div>
        @endif

        {{-- Dispute status --}}
        @if($dispute)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-300">Dispute</h3>
            <div class="flex justify-between"><span class="text-gray-400">Status</span>
                @php
                    $dBadge = match($dispute->status) {
                        'OPEN'         => 'bg-amber-900/30 text-amber-400',
                        'UNDER_REVIEW' => 'bg-blue-900/30 text-blue-400',
                        'RESOLVED'     => 'bg-green-900/30 text-green-400',
                        default        => 'bg-gray-700 text-gray-400',
                    };
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $dBadge }}">{{ $dispute->status }}</span>
            </div>
            <div class="flex justify-between"><span class="text-gray-400">Reason</span><span>{{ str_replace('_', ' ', $dispute->reason) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Raised</span>
                <span>{{ \Carbon\Carbon::parse($dispute->raised_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
            </div>
            @if($dispute->sla_deadline_at)
            <div class="flex justify-between"><span class="text-gray-400">SLA Deadline</span>
                <span class="{{ $dispute->sla_breached ? 'text-red-400 font-semibold' : '' }}">
                    {{ \Carbon\Carbon::parse($dispute->sla_deadline_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                    @if($dispute->sla_breached) (BREACHED) @endif
                </span>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Order lines --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Items</h3>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">SKU</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Supplier</th>
                    <th class="px-5 py-3 text-right">Qty</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($lines as $line)
                <tr>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $line->generic_name }}</p>
                        @if($line->brand_name)<p class="text-xs text-gray-400">{{ $line->brand_name }}</p>@endif
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400 hidden md:table-cell">{{ $line->sku_code }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $line->supplier_name }}</td>
                    <td class="px-5 py-3 text-right text-gray-300">{{ $line->quantity }}</td>
                    <td class="px-5 py-3 text-right text-gray-300">{{ $currency['symbol'] }} {{ number_format($line->unit_price, $currency['decimal_places']) }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-200">{{ $currency['symbol'] }} {{ number_format($line->line_total, $currency['decimal_places']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-700 bg-gray-900/50">
                <tr>
                    <td colspan="5" class="px-5 py-3 text-right font-semibold text-gray-300">Total</td>
                    <td class="px-5 py-3 text-right font-bold text-white">{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</td>
                </tr>
            </tfoot>
        </table></div>
    </div>

    {{-- Dispute action --}}
    @if($canDispute)
    <div class="flex justify-end">
        <button @click="showDisputeModal = true"
                class="px-4 py-2 border border-gray-700 text-red-400 rounded-lg text-sm hover:bg-red-900/20 transition-colors">
            Raise Dispute
        </button>
    </div>
    @endif

    {{-- Dispute modal --}}
    <div x-show="showDisputeModal" x-cloak
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl" @click.outside="showDisputeModal = false">
            <h3 class="text-base font-semibold text-white">Raise Dispute</h3>
            <p class="text-sm text-gray-400 mt-1">Describe the issue with your delivered order.</p>
            <form method="POST" action="/retail/orders/{{ $order->ulid }}/dispute" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Dispute Type</label>
                    <select name="reason" required
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Select type...</option>
                        <option value="MISSING_ITEMS">Missing Items</option>
                        <option value="DAMAGED_GOODS">Damaged Goods</option>
                        <option value="WRONG_ITEMS">Wrong Items</option>
                        <option value="SHORT_DELIVERY">Short Delivery</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Description (min 20 characters)</label>
                    <textarea name="notes" rows="4" required minlength="20" maxlength="2000"
                              placeholder="Describe the issue in detail..."
                              class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Submit Dispute</button>
                    <button type="button" @click="showDisputeModal = false" class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection