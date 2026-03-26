@extends('layouts.app')
@section('title', 'My Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">My Orders</h1>
        <p class="text-sm text-gray-400 mt-1">Track your medicine orders and collection status</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Total Orders</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-3xl font-bold text-{{ $stats['pending'] > 0 ? 'amber-400' : 'white' }} mt-1">
                {{ $stats['pending'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-green-800 p-5">
            <p class="text-xs text-green-400">Collected</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['collected'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['PENDING','CONFIRMED','READY','COLLECTED','CANCELLED','REJECTED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
            Filter
        </button>
        @if(request('status'))
            <a href="/store/orders"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    {{-- Orders list --}}
    @if($orders->isNotEmpty())
    <div class="space-y-3">
        @foreach($orders as $order)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-200">{{ $order->facility_name ?? 'Pharmacy' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->county ?? '' }}</p>
                </div>
                @php
                    $badge = match($order->status) {
                        'COLLECTED'  => 'bg-green-900/30 text-green-400',
                        'READY'      => 'bg-blue-900/30 text-blue-400',
                        'CONFIRMED'  => 'bg-blue-900/30 text-blue-400',
                        'CANCELLED'  => 'bg-red-900/30 text-red-400',
                        'REJECTED'   => 'bg-red-900/30 text-red-400',
                        'PENDING'    => 'bg-amber-900/30 text-amber-400',
                        default      => 'bg-gray-100 text-gray-400',
                    };
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                    {{ $order->status }}
                </span>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-700">
                <div class="flex gap-4 text-xs text-gray-400">
                    <span>Ref: {{ substr($order->ulid, -8) }}</span>
                    <span>{{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
                </div>
                <p class="text-sm font-semibold text-gray-200">
                    {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                </p>
            </div>
            @if($order->collection_window_start && $order->status !== 'COLLECTED')
            <div class="mt-2 text-xs text-yellow-500">
                Collection window:
                {{ \Carbon\Carbon::parse($order->collection_window_start)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                –
                {{ \Carbon\Carbon::parse($order->collection_window_end)->timezone('Africa/Nairobi')->format('H:i') }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div>{{ $orders->links() }}</div>

    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No orders yet. Browse medicines to place your first order.</p>
        <a href="/store"
           class="inline-block mt-4 px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
            Browse Medicines
        </a>
    </div>
    @endif

</div>
@endsection