@extends('layouts.app')
@section('title', 'Routes & Dispatch Planning — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Routes & Dispatch Planning</h1>
        <p class="text-sm text-gray-500 mt-1">Packed orders grouped by county, ready for dispatch</p>
    </div>

    {{-- Map placeholder --}}
    <div class="bg-gray-100 rounded-xl border border-gray-200 h-48 flex items-center justify-center">
        <p class="text-gray-400 text-sm">Interactive delivery map — Phase 2 (Leaflet.js integration)</p>
    </div>

    @if($unassigned->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">All packed orders have been dispatched</p>
    </div>
    @else
    {{-- County groups --}}
    <div class="space-y-4">
        @foreach($byCounty as $county => $orders)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">{{ $county }}</h3>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                    {{ $orders->count() }} order{{ $orders->count() > 1 ? 's' : '' }}
                </span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">Ward</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">
                            {{ substr($order->ulid, -8) }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $order->facility_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $order->ward }}</td>
                        <td class="px-5 py-3 text-right text-gray-700">
                            KES {{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="/api/v1/wholesale/dispatch">
                                @csrf
                                <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800 transition-colors">
                                    Dispatch →
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
