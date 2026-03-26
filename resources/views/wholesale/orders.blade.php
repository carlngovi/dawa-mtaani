@extends('layouts.wholesale')
@section('title', 'Order Queue — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{ selected: [], selectAll(ids) { this.selected = ids }, clearAll() { this.selected = [] }, toggle(id) { this.selected.includes(id) ? this.selected = this.selected.filter(x => x !== id) : this.selected.push(id) } }">

    @if(session('success'))
    <div class="bg-green-900/20 border border-green-800 text-green-300 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Order Queue</h1>
        <button x-show="selected.length > 0" x-cloak
                @click="document.getElementById('bulk-form').submit()"
                class="px-4 py-2.5 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition-colors">
            Dispatch Selected (<span x-text="selected.length"></span>)
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/wholesale/orders?status=PENDING"
           class="bg-gray-800 rounded-xl border border-gray-700 p-4 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-2xl font-bold text-{{ $counts['pending'] > 0 ? 'amber-400' : 'white' }}">{{ $counts['pending'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Needs action</p>
        </a>
        <a href="/wholesale/orders?status=CONFIRMED"
           class="bg-gray-800 rounded-xl border border-gray-700 p-4 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400">Confirmed</p>
            <p class="text-2xl font-bold text-white">{{ $counts['confirmed'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Being processed</p>
        </a>
        <a href="/wholesale/orders?status=PACKED"
           class="bg-gray-800 rounded-xl border border-gray-700 p-4 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400">Packed</p>
            <p class="text-2xl font-bold text-purple-600">{{ $counts['packed'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Ready to dispatch</p>
        </a>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Dispatched Today</p>
            <p class="text-2xl font-bold text-green-400">{{ $counts['dispatched'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="status" class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['PENDING','CONFIRMED','PACKED','DISPATCHED','DELIVERED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Order ref or pharmacy name..."
               class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm w-64 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
        <button type="submit" class="px-4 py-2.5 bg-yellow-400 text-gray-900 font-medium rounded-lg text-sm hover:bg-yellow-500">Filter</button>
        @if(request()->hasAny(['status','search']))
            <a href="/wholesale/orders" class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">Clear</a>
        @endif
    </form>

    {{-- Bulk dispatch hidden form --}}
    <form id="bulk-form" method="POST" action="/wholesale/orders/bulk-dispatch" class="hidden">
        @csrf
        <template x-for="ulid in selected" :key="ulid">
            <input type="hidden" name="order_ulids[]" :value="ulid">
        </template>
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1000px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" class="rounded"
                                   @change="$event.target.checked ? selectAll({{ json_encode(collect($orders->items())->where('status', 'PACKED')->pluck('ulid')) }}) : clearAll()">
                        </th>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Pharmacy</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Items</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Payment</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Submitted</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($orders as $order)
                    @php
                        $sBadge = match($order->status) {
                            'PENDING'    => 'bg-amber-900/30 text-amber-400',
                            'CONFIRMED'  => 'bg-blue-900/30 text-blue-400',
                            'PACKED'     => 'bg-purple-900/30 text-purple-400',
                            'DISPATCHED' => 'bg-green-900/30 text-green-400',
                            'DELIVERED'  => 'bg-green-900/30 text-green-400',
                            default      => 'bg-gray-700 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-900" :class="selected.includes('{{ $order->ulid }}') ? 'bg-purple-50' : ''">
                        <td class="px-4 py-3">
                            @if($order->status === 'PACKED')
                            <input type="checkbox" class="rounded"
                                   :checked="selected.includes('{{ $order->ulid }}')"
                                   @change="toggle('{{ $order->ulid }}')">
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($order->ulid, -8) }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-200">{{ $order->retail_name }}</p>
                            <p class="text-xs text-gray-400">{{ $order->county }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400 hidden md:table-cell">{{ $lineCounts[$order->id] ?? 0 }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            @if($order->credit_amount > 0 && $order->cash_amount > 0)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-900/30 text-purple-400">MIXED</span>
                            @elseif($order->credit_amount > 0)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400">CREDIT</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">CASH</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $sBadge }}">{{ $order->status }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                @if($order->status === 'PENDING')
                                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/confirm" class="inline">
                                    @csrf
                                    <button class="px-2.5 py-1 bg-yellow-400 text-gray-900 rounded text-xs hover:bg-yellow-500">Confirm</button>
                                </form>
                                @elseif($order->status === 'CONFIRMED')
                                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/pack" class="inline">
                                    @csrf
                                    <button class="px-2.5 py-1 bg-yellow-400 text-gray-900 rounded text-xs hover:bg-yellow-500">Pack</button>
                                </form>
                                @elseif($order->status === 'PACKED')
                                <form method="POST" action="/wholesale/orders/{{ $order->ulid }}/dispatch" class="inline">
                                    @csrf
                                    <button class="px-2.5 py-1 bg-purple-600 text-white rounded text-xs hover:bg-purple-700">Dispatch</button>
                                </form>
                                @endif
                                <a href="/wholesale/orders/{{ $order->ulid }}" class="px-2.5 py-1 border border-gray-700 text-gray-400 rounded text-xs hover:bg-gray-900">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $orders->links() }}</div>
</div>
@endsection