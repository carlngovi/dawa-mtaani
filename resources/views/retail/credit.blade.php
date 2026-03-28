@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

  <div>
    <h1 class="text-xl font-semibold text-white">My Credit</h1>
    <p class="text-sm text-gray-400 mt-1">{{ $facility->facility_name }} · Live balance from credit engine</p>
  </div>

  {{-- Health badge --}}
  <div @class([
    'rounded-xl px-5 py-4 border flex items-start gap-4',
    'bg-green-900/20 border-gray-700' => $healthStatus === 'ON_TRACK',
    'bg-amber-900/20 border-amber-800' => $healthStatus === 'AT_RISK',
    'bg-red-900/20 border-gray-700' => $healthStatus === 'SUSPENDED',
  ])>
    <div @class(['text-2xl', 'text-green-500'=>$healthStatus==='ON_TRACK', 'text-amber-500'=>$healthStatus==='AT_RISK', 'text-red-500'=>$healthStatus==='SUSPENDED'])>
      @if($healthStatus === 'ON_TRACK') ✓ @elseif($healthStatus === 'AT_RISK') ⚠ @else ✕ @endif
    </div>
    <div>
      <p class="font-semibold text-sm @if($healthStatus==='ON_TRACK') text-green-400 @elseif($healthStatus==='AT_RISK') text-amber-400 @else text-red-400 @endif">
        {{ $healthStatus === 'ON_TRACK' ? 'On Track' : ($healthStatus === 'AT_RISK' ? 'At Risk' : 'Suspended') }}
      </p>
      <p class="text-sm text-gray-400 mt-0.5">{{ $healthMessage }}</p>
    </div>
  </div>

  @if(! $account)
    <div class="rounded-xl bg-gray-800 border border-gray-700 px-5 py-10 text-center">
      <p class="text-gray-400 text-sm">No credit account has been set up for your facility yet.</p>
      <p class="text-gray-400 text-xs mt-1">Your network administrator will activate your account once onboarding is complete.</p>
    </div>
  @elseif($trancheCards->isEmpty())
    <div class="rounded-xl bg-gray-800 border border-gray-700 px-5 py-10 text-center">
      <p class="text-gray-400 text-sm">No credit tranches configured yet.</p>
    </div>
  @else

    {{-- Tranche cards --}}
    <div class="space-y-4">
      @foreach($trancheCards as $card)
      <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-white">{{ $card['name'] }}</h3>
                @if($card['is_fixed'])<span class="text-xs px-2 py-0.5 rounded-full bg-purple-900/30 text-purple-400">Fixed</span>@endif
                @if($card['approval_pathway'] === 'ASSESSED')<span class="text-xs px-2 py-0.5 rounded-full bg-amber-900/30 text-amber-400">Assessed</span>@endif
              </div>
              @if($card['last_repayment_at'])<p class="text-xs text-gray-400 mt-0.5">Last repayment: {{ \Carbon\Carbon::parse($card['last_repayment_at'])->timezone('Africa/Nairobi')->format('d M Y') }}</p>@endif
            </div>
            <div class="text-right">
              <p class="text-2xl font-semibold text-white">{{ $currency['symbol'] }} {{ number_format($card['current_balance'], $currency['decimal_places']) }}</p>
              @if($card['ceiling'])<p class="text-xs text-gray-400 mt-0.5">of {{ $currency['symbol'] }} {{ number_format($card['ceiling'], $currency['decimal_places']) }} ceiling</p>@endif
            </div>
          </div>

          @if($card['progress_pct'] !== null)
          <div class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1"><span>Utilisation</span><span>{{ $card['progress_pct'] }}%</span></div>
            <div class="w-full bg-gray-700 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-300 @if($card['progress_pct'] >= 85) bg-red-500 @elseif($card['progress_pct'] >= 60) bg-amber-500 @else bg-green-500 @endif" style="width: {{ $card['progress_pct'] }}%"></div>
            </div>
          </div>
          @endif
        </div>

        @if(! empty($card['tiers']))
        <div class="px-5 py-3 grid grid-cols-1 gap-2">
          @foreach($card['tiers'] as $tier)
          <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
              <div @class(['w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0', 'bg-green-900/30 text-green-400'=>$tier['is_unlocked'], 'bg-gray-700 text-gray-400'=>!$tier['is_unlocked']])>
                {{ $tier['is_unlocked'] ? '✓' : '🔒' }}
              </div>
              <div>
                <p class="font-medium text-white text-sm">{{ $tier['name'] }}</p>
                <p class="text-xs text-gray-400">{{ $tier['product_scope'] }}</p>
                @if(! $tier['is_unlocked'] && $tier['unlock_at_amount'])
                  <p class="text-xs text-gray-400 mt-0.5">Unlocks at {{ \App\Services\CurrencyConfig::format($tier['unlock_at_amount']) }} ({{ $tier['unlock_threshold_pct'] }}%)</p>
                @endif
                @if($tier['approval_required'])<span class="text-xs text-amber-400">Requires approval</span>@endif
              </div>
            </div>
            <div class="text-right ml-4 flex-shrink-0">
              @if($tier['is_unlocked'])
                <p class="font-semibold text-white text-sm">{{ \App\Services\CurrencyConfig::format($tier['available']) }}</p>
                <p class="text-xs text-green-400">Available</p>
              @else
                <p class="text-sm text-gray-400">Locked</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>
      @endforeach
    </div>

    @if($progressionRules->isNotEmpty())
    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-700">
        <h3 class="font-semibold text-white text-sm">How your credit grows</h3>
        <p class="text-xs text-gray-400 mt-0.5">Repay on time to unlock higher limits.</p>
      </div>
      <div class="divide-y divide-gray-50">
        @foreach($progressionRules as $rule)
        <div class="px-5 py-3 flex items-center justify-between text-sm">
          <div><span class="font-medium text-white">{{ $rule->label }}</span><span class="ml-2 text-gray-400 text-xs">≤ {{ $rule->max_days_to_qualify }} days</span></div>
          <span class="text-green-400 font-medium text-sm">+{{ $rule->progression_rate_pct }}%</span>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($recentEvents->isNotEmpty())
    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-700">
        <h3 class="font-semibold text-white text-sm">Recent activity</h3>
      </div>
      <div class="divide-y divide-gray-50">
        @foreach($recentEvents as $event)
        <div class="px-5 py-3 flex items-center justify-between text-sm">
          <div class="flex items-center gap-3">
            <span @class(['text-xs font-medium px-2 py-0.5 rounded-full',
              'bg-red-900/30 text-red-400'=>$event->event_type==='DRAW',
              'bg-green-900/30 text-green-400'=>in_array($event->event_type,['REPAYMENT','PROGRESSION','REINSTATEMENT']),
              'bg-blue-900/30 text-yellow-400'=>$event->event_type==='TIER_UNLOCK',
              'bg-gray-700 text-gray-400'=>in_array($event->event_type,['SUSPENSION','RETURN_DISTRIBUTION']),
            ])>{{ str_replace('_', ' ', $event->event_type) }}</span>
            <span class="text-gray-400 text-xs">{{ \Carbon\Carbon::parse($event->created_at)->timezone('Africa/Nairobi')->format('d M Y H:i') }}</span>
          </div>
          <div class="text-right">
            <p class="font-medium text-white">{{ \App\Services\CurrencyConfig::format($event->amount) }}</p>
            <p class="text-xs text-gray-400">Balance: {{ \App\Services\CurrencyConfig::format($event->balance_after) }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

  @endif
</div>
@endsection
