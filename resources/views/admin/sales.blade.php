@extends('layouts.admin')
@section('title', 'Sales — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Sales by Facility</h1>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">County</th>
                    <th class="px-5 py-3 text-left">Membership</th>
                    <th class="px-5 py-3 text-right">Orders</th>
                    <th class="px-5 py-3 text-right">Total GMV</th>
                    <th class="px-5 py-3 text-left">Last Order</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($salesByFacility as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $row->facility_name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $row->county }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $row->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $row->network_membership }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-800">{{ number_format($row->total_orders) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($row->total_gmv, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($row->last_order_date)->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No sales data</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $salesByFacility->links() }}</div>
    </div>
</div>
@endsection
