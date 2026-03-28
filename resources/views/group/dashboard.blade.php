@extends('layouts.app')
@section('title', 'Group Dashboard — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $group)
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        ⚠ No pharmacy group is linked to your account. Contact Network Admin.
    </div>
    @else

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">{{ $group->group_name }}</h1>
        <p class="text-sm text-gray-400 mt-1">Group Dashboard — consolidated view across all outlets</p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Total Outlets</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $outlets->count() }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-green-400">Active Outlets</p>
            <p class="text-3xl font-bold text-green-400 mt-1">
                {{ $outlets->where('facility_status', 'ACTIVE')->count() }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-green-400">Credit Active</p>
            <p class="text-3xl font-bold text-green-400 mt-1">
                {{ $creditSummary->active_count ?? 0 }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Credit Suspended</p>
            <p class="text-3xl font-bold text-{{ ($creditSummary->suspended_count ?? 0) > 0 ? 'red-400' : 'white' }} mt-1">
                {{ $creditSummary->suspended_count ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Placer authority notice --}}
    @if($authorisedOutletIds->isEmpty())
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        ℹ You are not yet an authorised placer for any outlet. Contact Network Admin to be added.
        Group owner identity does not automatically grant order placement authority.
    </div>
    @endif

    {{-- Outlets table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Outlets</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Outlet</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left">Network</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Credit</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Orders (Month)</th>
                        <th class="px-5 py-3 text-right hidden lg:table-cell">GMV (Month)</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($outlets as $outlet)
                    @php
                        $credit   = $outletCredit->get($outlet->id);
                        $orderData = $outletOrders->get($outlet->id);
                        $isAuthorised = $authorisedOutletIds->contains($outlet->id);
                        $creditBadge = match($credit->account_status ?? '') {
                            'ACTIVE'             => 'bg-green-900/30 text-green-400',
                            'SUSPENDED'          => 'bg-red-900/30 text-red-400',
                            'PENDING_ASSESSMENT' => 'bg-amber-900/30 text-amber-400',
                            'CLOSED'             => 'bg-gray-700 text-gray-400',
                            default              => 'bg-gray-700 text-gray-400',
                        };
                        $statusBadge = match($outlet->facility_status) {
                            'ACTIVE'    => 'bg-green-900/30 text-green-400',
                            'SUSPENDED' => 'bg-red-900/30 text-red-400',
                            'PAUSED'    => 'bg-orange-900/30 text-orange-400',
                            default     => 'bg-gray-700 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-200">{{ $outlet->facility_name }}</p>
                            <p class="text-xs text-gray-400">{{ $outlet->ward }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $outlet->county }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $outlet->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                                {{ $outlet->network_membership === 'NETWORK' ? 'Network' : 'Off-Network' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge }}">
                                {{ str_replace('_', ' ', $outlet->facility_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $creditBadge }}">
                                {{ str_replace('_', ' ', $credit->account_status ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-300 hidden md:table-cell">
                            {{ number_format($orderData->order_count ?? 0) }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-300 hidden lg:table-cell">
                            {{ $currency['symbol'] }} {{ number_format($orderData->gmv ?? 0, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @if($isAuthorised)
                                <a href="/group/order?outlet={{ $outlet->ulid }}"
                                   class="px-3 py-1.5 bg-yellow-400 text-white rounded-lg text-xs hover:bg-yellow-500 transition-colors">
                                    Place Order
                                </a>
                            @else
                                <button disabled
                                        title="You are not an authorised placer for this outlet"
                                        class="px-3 py-1.5 bg-gray-700 text-gray-400 rounded-lg text-xs cursor-not-allowed">
                                    Place Order
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No outlets found for this group
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif
</div>
@endsection