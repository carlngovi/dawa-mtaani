@extends('layouts.app')
@section('title', 'System Settings — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">System Settings</h1>
            <p class="text-sm text-gray-400 mt-1">Platform-wide configuration</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
            Tier 1
        </span>
    </div>

    {{-- Warning --}}
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        Changes take effect immediately across all interfaces for all users.
    </div>

    {{-- Settings form --}}
    <form method="POST" action="/super/settings" class="space-y-6">
        @csrf

        {{-- Currency --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
                <h3 class="text-sm font-semibold text-gray-300">Currency</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $currencyFields = [
                        'currency_iso_code'        => ['ISO Code',         'e.g. KES, UGX, TZS'],
                        'currency_symbol'          => ['Symbol',           'e.g. KES, Ush, TSh'],
                        'currency_decimal_places'  => ['Decimal Places',   '0 or 2'],
                        'grant_exchange_rate'       => ['Grant Exchange Rate', 'e.g. 127'],
                        'grant_base_currency'       => ['Grant Base Currency', 'e.g. USD'],
                    ];
                @endphp
                @foreach($currencyFields as $key => [$label, $placeholder])
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">{{ $label }}</label>
                    <input type="text" name="{{ $key }}"
                           value="{{ $settings->get($key)?->value ?? '' }}"
                           placeholder="{{ $placeholder }}"
                           class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Regional --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
                <h3 class="text-sm font-semibold text-gray-300">Regional</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Display Timezone</label>
                    <input type="text" name="display_timezone"
                           value="{{ $settings->get('display_timezone')?->value ?? 'Africa/Nairobi' }}"
                           placeholder="Africa/Nairobi"
                           class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
            </div>
        </div>

        <div>
            <button type="submit"
                    class="px-6 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm font-bold hover:bg-yellow-500 transition-colors">
                Save Settings
            </button>
        </div>
    </form>

    {{-- Read-only info --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
            <h3 class="text-sm font-semibold text-gray-300">Environment Info</h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-700">
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400">PPB Mode</dt>
                <dd class="mt-1 text-sm font-medium text-gray-200">{{ config('services.ppb.mode', env('PPB_MODE', 'FILE')) }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400">Environment</dt>
                <dd class="mt-1 text-sm font-medium text-gray-200">{{ app()->environment() }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400">Laravel Version</dt>
                <dd class="mt-1 text-sm font-medium text-gray-200">{{ app()->version() }}</dd>
            </div>
        </dl>
    </div>

</div>
@endsection
