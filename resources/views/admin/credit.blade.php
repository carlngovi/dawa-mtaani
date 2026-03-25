@extends('layouts.app')

@section('content')
<div class="p-6 space-y-8">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Credit Engine Configuration</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tranches · Tiers · Progression Rules · Changelog — zero hardcoded values</p>
    </div>
  </div>

  @if(session('success'))
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
      <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active accounts</p>
      <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ $totalActive }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
      <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Suspended</p>
      <p class="text-2xl font-semibold text-red-600 dark:text-red-400 mt-1">{{ $totalSuspended }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
      <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pending assessment</p>
      <p class="text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-1">{{ $totalPending }}</p>
    </div>
  </div>

  {{-- TRANCHES --}}
  <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
      <h2 class="font-semibold text-gray-900 dark:text-white">Tranches</h2>
      <button onclick="document.getElementById('new-tranche-form').classList.toggle('hidden')" class="text-sm text-blue-600 hover:underline">+ Add tranche</button>
    </div>

    <div id="new-tranche-form" class="hidden px-5 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
      <form method="POST" action="{{ route('admin.credit.tranches.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @csrf
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Name</label><input type="text" name="name" required class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Approval pathway</label><select name="approval_pathway" class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"><option value="AUTOMATIC">Automatic</option><option value="ASSESSED">Assessed</option></select></div>
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Entry amount ({{ $currency['symbol'] }})</label><input type="number" step="0.01" name="entry_amount" required class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Ceiling amount — blank for fixed</label><input type="number" step="0.01" name="ceiling_amount" class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Effective from</label><input type="date" name="effective_from" required class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Product restriction (SKUs, comma-separated)</label><input type="text" name="product_restriction_scope" placeholder="blank = unrestricted" class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 dark:text-white"></div>
        <div class="sm:col-span-2 flex items-center gap-4">
          <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input type="checkbox" name="is_fixed" value="1"> Fixed (non-progressive)</label>
          <button type="submit" class="ml-auto px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Create tranche</button>
        </div>
      </form>
    </div>

    @forelse($tranches as $tranche)
    <div class="px-5 py-5 border-b border-gray-100 dark:border-gray-700 last:border-0" x-data="{ open: false }">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <span @click="open = !open" class="cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xs select-none"><span x-show="!open">▶</span><span x-show="open">▼</span></span>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-medium text-gray-900 dark:text-white">{{ $tranche->name }}</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $tranche->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }}">{{ $tranche->is_active ? 'Active' : 'Inactive' }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ $tranche->approval_pathway }}</span>
              @if($tranche->is_fixed)<span class="text-xs text-purple-600 dark:text-purple-400">Fixed</span>@endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Entry: {{ $currency['symbol'] }} {{ number_format($tranche->entry_amount, $currency['decimal_places']) }}@if($tranche->ceiling_amount) · Ceiling: {{ $currency['symbol'] }} {{ number_format($tranche->ceiling_amount, $currency['decimal_places']) }}@endif</p>
          </div>
        </div>
        <form method="POST" action="{{ route('admin.credit.tranches.toggle', $tranche) }}">@csrf @method('PATCH')<button type="submit" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ $tranche->is_active ? 'Deactivate' : 'Activate' }}</button></form>
      </div>

      <div x-show="open" class="mt-4 ml-6 space-y-4">
        {{-- Parties --}}
        <div>
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Parties</p>
            @php $riskSum = $tranche->activeParties->sum('risk_percentage'); @endphp
            <span class="text-xs {{ abs($riskSum - 100) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">Risk sum: {{ $riskSum }}% {{ abs($riskSum - 100) < 0.01 ? '✓' : '⚠ must = 100%' }}</span>
          </div>
          @if($tranche->activeParties->isNotEmpty())
          <div class="overflow-x-auto"><table class="min-w-full text-xs"><thead><tr class="text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700"><th class="text-left pb-1 pr-4">Party</th><th class="text-left pb-1 pr-4">Type</th><th class="text-right pb-1 pr-4">Risk %</th><th class="text-right pb-1 pr-4">Return %</th><th class="text-left pb-1">Binding</th></tr></thead><tbody>
            @foreach($tranche->activeParties as $party)
            <tr class="border-b border-gray-50 dark:border-gray-800"><td class="py-1.5 pr-4 text-gray-800 dark:text-gray-200">{{ $party->party_name }}</td><td class="py-1.5 pr-4 text-gray-500">{{ $party->party_type }}</td><td class="py-1.5 pr-4 text-right font-mono text-gray-800 dark:text-gray-200">{{ $party->risk_percentage }}%</td><td class="py-1.5 pr-4 text-right font-mono text-gray-800 dark:text-gray-200">{{ $party->return_percentage }}%</td><td class="py-1.5 text-gray-400 font-mono text-xs">{{ $party->banking_party_binding ?? '—' }}</td></tr>
            @endforeach
          </tbody></table></div>
          @else<p class="text-xs text-gray-400 italic">No parties assigned.</p>@endif
          <form method="POST" action="{{ route('admin.credit.parties.store', $tranche) }}" class="mt-3 grid grid-cols-2 sm:grid-cols-5 gap-2">@csrf
            <input type="text" name="party_name" placeholder="Party name" required class="sm:col-span-2 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <input type="text" name="party_type" placeholder="Type" required class="text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <input type="number" step="0.01" name="risk_percentage" placeholder="Risk %" required min="0" max="100" class="text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <div class="flex gap-1"><input type="number" step="0.01" name="return_percentage" placeholder="Return %" required min="0" max="100" class="flex-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"><button type="submit" class="px-2 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Add</button></div>
          </form>
        </div>

        {{-- Tiers --}}
        <div>
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Tiers</p>
            @php $allocSum = $tranche->activeTiers->sum('allocation_pct'); @endphp
            <span class="text-xs {{ abs($allocSum - 100) < 0.01 || $allocSum == 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">Allocation sum: {{ $allocSum }}% {{ abs($allocSum - 100) < 0.01 ? '✓' : ($allocSum == 0 ? '(add tiers)' : '⚠ must = 100%') }}</span>
          </div>
          @if($tranche->activeTiers->isNotEmpty())
          <div class="space-y-1">
            @foreach($tranche->activeTiers as $tier)
            <div class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-900/40 rounded px-3 py-2">
              <div><span class="font-medium text-gray-800 dark:text-gray-200">{{ $tier->name }}</span><span class="ml-2 text-gray-500">Unlock {{ $tier->unlock_threshold_pct }}%</span><span class="ml-2 text-gray-500">Alloc {{ $tier->allocation_pct }}%</span>@if($tier->approval_required)<span class="ml-2 text-amber-600 dark:text-amber-400">Approval req</span>@endif</div>
              <span class="text-gray-400 truncate max-w-xs hidden sm:inline">{{ $tier->product_scope_description }}</span>
            </div>
            @endforeach
          </div>
          @else<p class="text-xs text-gray-400 italic">No tiers configured.</p>@endif
          <form method="POST" action="{{ route('admin.credit.tiers.store', $tranche) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">@csrf
            <input type="text" name="name" placeholder="Tier name" required class="text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <input type="text" name="product_scope_description" placeholder="Scope (e.g. All products)" required class="text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <input type="number" step="0.01" name="unlock_threshold_pct" placeholder="Unlock at %" required min="0" max="100" class="text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white">
            <div class="flex gap-1"><input type="number" step="0.01" name="allocation_pct" placeholder="Alloc %" required min="0" max="100" class="flex-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"><label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400"><input type="checkbox" name="approval_required" value="1"> Approval</label><button type="submit" class="px-2 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Add</button></div>
          </form>
        </div>
      </div>
    </div>
    @empty
    <div class="px-5 py-8 text-center text-sm text-gray-400">No tranches configured yet.</div>
    @endforelse
  </div>

  {{-- PROGRESSION RULES --}}
  <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="font-semibold text-gray-900 dark:text-white">Progression Rules</h2>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">How credit grows based on repayment speed.</p>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
      @forelse($progressionRules as $rule)
      <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
        <div><span class="font-medium text-gray-900 dark:text-white">{{ $rule->label }}</span><span class="ml-3 text-gray-500">≤ {{ $rule->max_days_to_qualify }} days</span><span class="ml-3 text-green-600 dark:text-green-400">+{{ $rule->progression_rate_pct }}%</span>@if($rule->is_suspension_trigger)<span class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">Suspension trigger</span>@endif</div>
        <span class="text-xs text-gray-400">Order {{ $rule->sort_order }}</span>
      </div>
      @empty<div class="px-5 py-6 text-center text-sm text-gray-400">No rules defined.</div>@endforelse
    </div>
    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
      <form method="POST" action="{{ route('admin.credit.progression.store') }}" class="grid grid-cols-2 sm:grid-cols-5 gap-2 items-end">@csrf
        <div><label class="block text-xs text-gray-500 mb-1">Label</label><input type="text" name="label" required placeholder="e.g. On-time" class="w-full text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Max days</label><input type="number" name="max_days_to_qualify" required min="1" class="w-full text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Rate %</label><input type="number" step="0.01" name="progression_rate_pct" required min="0" max="100" class="w-full text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Sort</label><input type="number" name="sort_order" value="0" class="w-full text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 dark:text-white"></div>
        <div class="flex items-center gap-2"><label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400"><input type="checkbox" name="is_suspension_trigger" value="1"> Suspension</label><button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Add</button></div>
      </form>
    </div>
  </div>

  {{-- CHANGELOG --}}
  <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="font-semibold text-gray-900 dark:text-white">Config Changelog</h2>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Immutable — every field-level change recorded automatically.</p>
    </div>
    @if($changelog->isEmpty())
    <div class="px-5 py-6 text-center text-sm text-gray-400">No changes recorded yet.</div>
    @else
    <div class="overflow-x-auto"><table class="min-w-full text-xs">
      <thead><tr class="border-b border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400"><th class="text-left px-5 py-2">When</th><th class="text-left px-4 py-2">By</th><th class="text-left px-4 py-2">Model</th><th class="text-left px-4 py-2">Field</th><th class="text-left px-4 py-2">Before</th><th class="text-left px-4 py-2">After</th></tr></thead>
      <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
        @foreach($changelog as $entry)
        <tr><td class="px-5 py-2 text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->changed_at)->timezone('Africa/Nairobi')->format('d M Y H:i') }}</td><td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $entry->changedByUser?->name ?? 'System' }}</td><td class="px-4 py-2 text-gray-500 font-mono">{{ class_basename($entry->model_type) }}#{{ $entry->model_id }}</td><td class="px-4 py-2 text-gray-700 dark:text-gray-300 font-mono">{{ $entry->field_name }}</td><td class="px-4 py-2 text-red-500 font-mono max-w-xs truncate">{{ $entry->value_before ?? '—' }}</td><td class="px-4 py-2 text-green-600 dark:text-green-400 font-mono max-w-xs truncate">{{ $entry->value_after ?? '—' }}</td></tr>
        @endforeach
      </tbody>
    </table></div>
    @endif
  </div>

</div>
@endsection
