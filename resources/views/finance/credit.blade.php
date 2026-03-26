@extends('layouts.app')
@section('title', 'Credit Positions — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Credit Positions</h1>
            <p class="text-sm text-gray-400 mt-1">All facility credit accounts</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Total Accounts</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $summary->total ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-green-800 p-5">
            <p class="text-xs text-green-400">Active</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $summary->active_count ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Pending Assessment</p>
            <p class="text-3xl font-bold text-{{ ($summary->pending_count ?? 0) > 0 ? 'amber-400' : 'white' }} mt-1">
                {{ $summary->pending_count ?? 0 }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Suspended</p>
            <p class="text-3xl font-bold text-{{ ($summary->suspended_count ?? 0) > 0 ? 'red-400' : 'white' }} mt-1">
                {{ $summary->suspended_count ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="status"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['ACTIVE','PENDING_ASSESSMENT','SUSPENDED','CLOSED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $s) }}
                </option>
            @endforeach
        </select>
        <select name="county"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All counties</option>
            @foreach($counties as $county)
                <option value="{{ $county }}" {{ request('county') === $county ? 'selected' : '' }}>
                    {{ $county }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
            Filter
        </button>
        @if(request()->hasAny(['status','county']))
            <a href="/finance/credit"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Network</th>
                        <th class="px-5 py-3 text-left">Tranche</th>
                        <th class="px-5 py-3 text-right">Credit Limit</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Approved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $account->facility_name }}</td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $account->county }}</td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $account->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                                {{ $account->network_membership === 'NETWORK' ? 'Network' : 'Off-Network' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $account->tranche_name }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($account->credit_limit_kes, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($account->account_status) {
                                    'ACTIVE'             => 'bg-green-900/30 text-green-400',
                                    'SUSPENDED'          => 'bg-red-900/30 text-red-400',
                                    'PENDING_ASSESSMENT' => 'bg-amber-900/30 text-amber-400',
                                    'CLOSED'             => 'bg-gray-700 text-gray-400',
                                    default              => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $account->account_status) }}
                            </span>
                            @if($account->account_status === 'SUSPENDED' && $account->suspension_reason)
                                <p class="text-xs text-red-500 mt-0.5">{{ $account->suspension_reason }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($account->approved_at)
                                {{ \Carbon\Carbon::parse($account->approved_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else
                                —
                            @endisset
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No credit accounts found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $accounts->links() }}</div>

</div>
@endsection