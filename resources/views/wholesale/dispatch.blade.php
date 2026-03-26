@extends('layouts.wholesale')
@section('title', 'Dispatch Centre — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         selected: [],
         toggle(id) {
             this.selected.includes(id)
                 ? this.selected = this.selected.filter(x => x !== id)
                 : this.selected.push(id)
         },
         selectAll(ids) { this.selected = ids },
         clearAll()     { this.selected = [] },
         showConfirm:   false,
     }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Dispatch Centre</h1>
            <p class="text-sm text-gray-400 mt-1">Select packed orders and dispatch to SGA Courier</p>
        </div>
        <button x-show="selected.length > 0"
                @click="showConfirm = true"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            Dispatch Selected (<span x-text="selected.length"></span>)
        </button>
    </div>

    {{-- Info --}}
    <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Dispatching notifies SGA Courier and generates a dispatch note per order.
        Bulk dispatch sends all selected orders in a single operation.
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Ready to Dispatch</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $packedOrders->total() }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Dispatched Today</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $dispatchedToday }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" class="rounded"
                                   @change="$event.target.checked
                                       ? selectAll({{ json_encode($packedOrders->pluck('id')) }})
                                       : clearAll()">
                        </th>
                        <th class="px-5 py-3 text-left">Ref</th>
                        <th class="px-5 py-3 text-left">Retail Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Ward</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Payment</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Packed At</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($packedOrders as $order)
                    <tr class="hover:bg-gray-900" :class="selected.includes({{ $order->id }}) ? 'bg-green-900/20' : ''">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="rounded"
                                   :checked="selected.includes({{ $order->id }})"
                                   @change="toggle({{ $order->id }})">
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">
                            {{ substr($order->ulid, -8) }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $order->facility_name }}</td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $order->county }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $order->ward }}</td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
                                {{ $order->payment_type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($order->updated_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="/api/v1/wholesale/dispatch">
                                @csrf
                                <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-yellow-400 text-gray-900 rounded-lg text-xs hover:bg-yellow-500 transition-colors">
                                    Dispatch →
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No orders are packed and ready to dispatch
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $packedOrders->links() }}</div>

    {{-- Bulk confirm modal --}}
    <div x-show="showConfirm"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-white">Confirm Bulk Dispatch</h3>
            <p class="text-sm text-gray-400 mt-2">
                You are about to dispatch <strong x-text="selected.length"></strong> order(s) to SGA Courier.
                This cannot be undone.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST" action="/api/v1/wholesale/dispatch" class="flex-1">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="order_ids[]" :value="id">
                    </template>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                        Confirm Dispatch
                    </button>
                </form>
                <button @click="showConfirm = false"
                        class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection