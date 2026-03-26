@extends('layouts.app')
@section('title', 'My Orders — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
        <p class="text-sm text-gray-500 mt-1">Track your medicine orders and collection status</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Total Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-{{ $stats['pending'] > 0 ? 'amber' : 'gray' }}-200 p-5">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-3xl font-bold text-{{ $stats['pending'] > 0 ? 'amber-600' : 'gray-900' }} mt-1">
                {{ $stats['pending'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-5">
            <p class="text-xs text-green-600">Collected</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $stats['collected'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All statuses</option>
            @foreach(['PENDING','CONFIRMED','READY','COLLECTED','CANCELLED','REJECTED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
            Filter
        </button>
        @if(request('status'))
            <a href="/store/orders"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Orders list --}}
    @if($orders->isNotEmpty())
    <div class="space-y-3">
        @foreach($orders as $order)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $order->facility_name ?? 'Pharmacy' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->county ?? '' }}</p>
                </div>
                @php
                    $badge = match($order->status) {
                        'COLLECTED'  => 'bg-green-100 text-green-700',
                        'READY'      => 'bg-blue-100 text-blue-700',
                        'CONFIRMED'  => 'bg-blue-100 text-blue-700',
                        'CANCELLED'  => 'bg-red-100 text-red-700',
                        'REJECTED'   => 'bg-red-100 text-red-700',
                        'PENDING'    => 'bg-amber-100 text-amber-700',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                    {{ $order->status }}
                </span>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                <div class="flex gap-4 text-xs text-gray-500">
                    <span>Ref: {{ substr($order->ulid, -8) }}</span>
                    <span>{{ \Carbon\Carbon::parse($order->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
                </div>
                <p class="text-sm font-semibold text-gray-800">
                    {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                </p>
            </div>
            @if($order->collection_window_start && $order->status !== 'COLLECTED')
            <div class="mt-2 text-xs text-blue-600">
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
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No orders yet. Browse medicines to place your first order.</p>
        <a href="/store"
           class="inline-block mt-4 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
            Browse Medicines
        </a>
    </div>
    @endif

</div>
@endsection