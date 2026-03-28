@extends('layouts.app')
@section('title', 'Design Fee — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ releaseId: null, releaseName: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Design Fee Management</h1>
            <p class="text-sm text-gray-400 mt-1">Release design fees per credit tranche milestone</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
            Tier 1
        </span>
    </div>

    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Design fees are released per credit tranche milestone.
        Manual release triggers payment to NILA Pharmaceuticals.
        Actual fee amounts are configured in platform_fee_config.
    </div>

    @if($tranches->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Tranche</th>
                    <th class="px-5 py-3 text-right">Entry Amount</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($tranches as $tranche)
                <tr class="hover:bg-gray-700/50">
                    <td class="px-5 py-3 font-medium text-gray-200">{{ $tranche->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-300">
                        {{ $currency['symbol'] }} {{ number_format($tranche->entry_amount, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3">
                        <button @click="releaseId = {{ $tranche->id }}; releaseName = '{{ addslashes($tranche->name) }}'"
                                class="px-3 py-1.5 bg-yellow-400 text-white rounded-lg text-xs font-bold hover:bg-yellow-500 transition-colors">
                            Release Fee
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No credit tranches configured yet.</p>
    </div>
    @endif

    {{-- Release confirm modal --}}
    <div x-show="releaseId !== null"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl border border-gray-700">
            <h3 class="text-base font-semibold text-white">Confirm Fee Release</h3>
            <p class="text-sm text-gray-400 mt-2">
                Release design fee for tranche: <strong x-text="releaseName"></strong>?
                This will trigger payment to NILA Pharmaceuticals.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST"
                      :action="'/super/design-fee/' + releaseId + '/release'"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm font-bold hover:bg-yellow-500">
                        Confirm Release
                    </button>
                </form>
                <button @click="releaseId = null"
                        class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
