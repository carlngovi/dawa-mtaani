@extends('layouts.app')
@section('title', 'Consolidated Credit — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Consolidated Credit</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $group->group_name }}</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Info banner --}}
    <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Credit limits, increases, and suspensions are managed exclusively by Network Admin.
        This is a read-only view — no changes can be made here.
    </div>

    {{-- Credit accounts --}}
    @if($creditAccounts->isEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No credit accounts found for your outlets</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($creditAccounts as $account)
        @php
            $badge = match($account->account_status) {
                'ACTIVE'             => 'bg-green-900/30 text-green-400 border-green-800',
                'SUSPENDED'          => 'bg-red-900/30 text-red-400 border-red-800',
                'PENDING_ASSESSMENT' => 'bg-amber-900/30 text-amber-400 border-amber-800',
                'CLOSED'             => 'bg-gray-700 text-gray-400 border-gray-700',
                default              => 'bg-gray-700 text-gray-400 border-gray-700',
            };
        @endphp
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-200">{{ $account->facility_name }}</p>
                    <p class="text-xs text-gray-400">{{ $account->county }}</p>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                    {{ str_replace('_', ' ', $account->account_status) }}
                </span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Tranche</span>
                    <span class="text-gray-200 font-medium">{{ $account->tranche_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Credit Limit</span>
                    <span class="text-gray-200 font-medium">
                        {{ $currency['symbol'] }} {{ number_format($account->credit_limit_kes, $currency['decimal_places']) }}
                    </span>
                </div>
                @if($account->account_status === 'SUSPENDED' && $account->suspension_reason)
                <div class="pt-1">
                    <p class="text-xs text-red-400">⚠ {{ $account->suspension_reason }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Recent events --}}
    @if($recentEvents->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Recent Credit Events</h3>
        </div>
        <ul class="divide-y divide-gray-700">
            @foreach($recentEvents as $event)
            <li class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-300">
                        <span class="font-medium">{{ $event->facility_name }}</span>
                        · {{ str_replace('_', ' ', $event->event_type) }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($event->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                    </p>
                </div>
                <span class="text-sm font-medium text-gray-200">
                    {{ $currency['symbol'] }} {{ number_format($event->amount, $currency['decimal_places']) }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection