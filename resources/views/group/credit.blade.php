@extends('layouts.app')
@section('title', 'Consolidated Credit — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Consolidated Credit</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $group->group_name }}</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    {{-- Info banner --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Credit limits, increases, and suspensions are managed exclusively by Network Admin.
        This is a read-only view — no changes can be made here.
    </div>

    {{-- Credit accounts --}}
    @if($creditAccounts->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No credit accounts found for your outlets</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($creditAccounts as $account)
        @php
            $badge = match($account->account_status) {
                'ACTIVE'             => 'bg-green-100 text-green-700 border-green-200',
                'SUSPENDED'          => 'bg-red-100 text-red-700 border-red-200',
                'PENDING_ASSESSMENT' => 'bg-amber-100 text-amber-700 border-amber-200',
                'CLOSED'             => 'bg-gray-100 text-gray-500 border-gray-200',
                default              => 'bg-gray-100 text-gray-400 border-gray-200',
            };
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-800">{{ $account->facility_name }}</p>
                    <p class="text-xs text-gray-400">{{ $account->county }}</p>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                    {{ str_replace('_', ' ', $account->account_status) }}
                </span>
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Tranche</span>
                    <span class="text-gray-800 font-medium">{{ $account->tranche_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Credit Limit</span>
                    <span class="text-gray-800 font-medium">
                        {{ $currency['symbol'] }} {{ number_format($account->credit_limit_kes, $currency['decimal_places']) }}
                    </span>
                </div>
                @if($account->account_status === 'SUSPENDED' && $account->suspension_reason)
                <div class="pt-1">
                    <p class="text-xs text-red-600">⚠ {{ $account->suspension_reason }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Recent events --}}
    @if($recentEvents->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Recent Credit Events</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @foreach($recentEvents as $event)
            <li class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">{{ $event->facility_name }}</span>
                        · {{ str_replace('_', ' ', $event->event_type) }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($event->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                    </p>
                </div>
                <span class="text-sm font-medium text-gray-800">
                    {{ $currency['symbol'] }} {{ number_format($event->amount, $currency['decimal_places']) }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection