@extends('layouts.app')
@section('title', 'Design Fee — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ releaseId: null, releaseName: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Design Fee Management</h1>
            <p class="text-sm text-gray-500 mt-1">Release design fees per credit tranche milestone</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
            Tier 1
        </span>
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Design fees are released per credit tranche milestone.
        Manual release triggers payment to NILA Pharmaceuticals.
        Actual fee amounts are configured in platform_fee_config.
    </div>

    @if($tranches->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Tranche</th>
                    <th class="px-5 py-3 text-right">Credit Limit</th>
                    <th class="px-5 py-3 text-right hidden md:table-cell">Order</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tranches as $tranche)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $tranche->tranche_name }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">
                        {{ $currency['symbol'] }} {{ number_format($tranche->credit_limit_kes, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-right text-gray-400 hidden md:table-cell">
                        {{ $tranche->tranche_order }}
                    </td>
                    <td class="px-5 py-3">
                        <button @click="releaseId = {{ $tranche->id }}; releaseName = '{{ addslashes($tranche->tranche_name) }}'"
                                class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800 transition-colors">
                            Release Fee
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No credit tranches configured yet.</p>
    </div>
    @endif

    {{-- Release confirm modal --}}
    <div x-show="releaseId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-gray-900">Confirm Fee Release</h3>
            <p class="text-sm text-gray-500 mt-2">
                Release design fee for tranche: <strong x-text="releaseName"></strong>?
                This will trigger payment to NILA Pharmaceuticals.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST"
                      :action="'/super/design-fee/' + releaseId + '/release'"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                        Confirm Release
                    </button>
                </form>
                <button @click="releaseId = null"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
