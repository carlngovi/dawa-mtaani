@extends('layouts.app')
@section('title', 'Mystery Shopping — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         selectedFacility: null,
         scores: { availability: 0, pricing: 0, storage: 0, knowledge: 0, cleanliness: 0 },
         get overall() {
             const vals = Object.values(this.scores);
             return vals.every(v => v > 0)
                 ? (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1)
                 : '—';
         },
         overallColor() {
             const o = parseFloat(this.overall);
             if (isNaN(o)) return 'text-gray-400';
             if (o >= 4)   return 'text-green-400';
             if (o >= 3)   return 'text-amber-400';
             return 'text-red-400';
         }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Mystery Shopping</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $county }} County · Reports are advisory only</p>
    </div>

    {{-- Info --}}
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Visit reports are advisory. Findings are forwarded to Network Admin for review.
        They do not automatically affect facility status.
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Facility selector --}}
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <label class="block text-xs font-medium text-gray-300 mb-2">Select Pharmacy</label>
                <select x-model="selectedFacility"
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                    <option value="">— choose a pharmacy —</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}">{{ $facility->facility_name }} ({{ $facility->ward }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Scoring form --}}
            <div x-show="selectedFacility" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-6">
                <form method="POST" action="/api/v1/mystery-shop">
                    @csrf
                    <input type="hidden" name="facility_id" :value="selectedFacility">

                    @php
                        $categories = [
                            'availability'  => 'Stock Availability — Are essential medicines available?',
                            'pricing'       => 'Pricing Accuracy — Do shelf prices match the network price list?',
                            'storage'       => 'Storage Conditions — Proper temperature and storage observed?',
                            'knowledge'     => 'Staff Knowledge — Can staff answer basic medicine queries?',
                            'cleanliness'   => 'Cleanliness & Compliance — Clean environment, PPB certificate displayed?',
                        ];
                    @endphp

                    @foreach($categories as $key => $label)
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-300">{{ $label }}</label>
                        <input type="hidden" name="score_{{ $key }}" :value="scores.{{ $key }}">
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button"
                                    @click="scores.{{ $key }} = {{ $i }}"
                                    :class="scores.{{ $key }} >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'"
                                    class="text-2xl hover:text-yellow-400 transition-colors">★</button>
                            @endfor
                            <span x-show="scores.{{ $key }} > 0"
                                  class="ml-2 text-xs text-gray-400 self-center"
                                  x-text="scores.{{ $key }} + '/5'"></span>
                        </div>
                    </div>
                    @endforeach

                    {{-- Overall --}}
                    <div class="pt-2 border-t border-gray-700 flex items-center gap-4">
                        <span class="text-sm text-gray-400">Overall Score:</span>
                        <span class="text-3xl font-bold" :class="overallColor()" x-text="overall"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Date of Visit</label>
                            <input type="date" name="visited_at"
                                   value="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Notes (optional)</label>
                        <textarea name="notes" rows="3"
                                  placeholder="Additional observations..."
                                  class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">
                        Submit Report
                    </button>
                </form>
            </div>
        </div>

        {{-- Recent visits --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden h-fit">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-300">Recent Visits</h3>
            </div>
            @if($recentVisits->isEmpty())
            <p class="px-5 py-6 text-center text-gray-400 text-sm">
                @if(Schema::hasTable('mystery_shop_visits'))
                    No visits recorded yet
                @else
                    Visit history will appear here once the module is active
                @endif
            </p>
            @else
            <ul class="divide-y divide-gray-700">
                @foreach($recentVisits as $visit)
                <li class="px-5 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-200">{{ $visit->facility_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($visit->visited_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            </p>
                        </div>
                        @if($visit->overall_score)
                        @php
                            $sc = $visit->overall_score >= 4 ? 'bg-green-900/30 text-green-400'
                                : ($visit->overall_score >= 3 ? 'bg-amber-900/30 text-amber-400'
                                : 'bg-red-900/30 text-red-400');
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">
                            {{ number_format($visit->overall_score, 1) }}
                        </span>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>
</div>
@endsection
