@extends('layouts.app')
@section('title', 'Recruiter Firms — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Recruiter Firms</h1>
        <p class="text-sm text-gray-400 mt-1">Agent networks and commission activity</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($firms as $firm)
        <a href="/admin/recruiter?firm_id={{ $firm->id }}"
           class="bg-gray-800 rounded-xl border {{ isset($selectedFirmId) && $selectedFirmId == $firm->id ? 'border-gray-700 ring-2 ring-blue-800' : 'border-gray-700' }} p-5 hover:border-gray-700 transition-all">
            <div class="flex items-start justify-between">
                <p class="font-semibold text-gray-200">{{ $firm->firm_name }}</p>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $firm->status === 'ACTIVE' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-gray-700 text-gray-400' }}">{{ $firm->status }}</span>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="text-center"><p class="text-xl font-bold text-gray-200">{{ $agentCounts[$firm->id] ?? 0 }}</p><p class="text-xs text-gray-400">Agents</p></div>
                <div class="text-center"><p class="text-xl font-bold text-gray-200">{{ $activationCounts[$firm->id] ?? 0 }}</p><p class="text-xs text-gray-400">Activations</p></div>
                <div class="text-center"><p class="text-xl font-bold text-gray-200">{{ number_format($commissionTotals[$firm->id] ?? 0, 0) }}</p><p class="text-xs text-gray-400">KSh</p></div>
            </div>
        </a>
        @empty
        <div class="col-span-3 bg-gray-800 rounded-xl border border-gray-700 p-10 text-center"><p class="text-gray-400 text-sm">No recruiter firms yet</p></div>
        @endforelse
    </div>

    @if($selectedFirm)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Agents — {{ $selectedFirm->firm_name }}</h3></div>
            <div class="divide-y divide-gray-700">
                @forelse($agents as $agent)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-blue-900/30 flex items-center justify-center flex-shrink-0"><span class="text-xs font-bold text-blue-400">{{ strtoupper(substr($agent->agent_name, 0, 1)) }}</span></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ $agent->agent_name }}</p>
                        <p class="text-xs text-gray-400">{{ $agent->agent_phone }} · {{ $agent->agent_role_label }}</p>
                        @if($agent->parent_name)<p class="text-xs text-gray-400">↳ {{ $agent->parent_name }}</p>@endif
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $agent->status === 'ACTIVE' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-gray-700 text-gray-400' }}">{{ $agent->status }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No agents</div>
                @endforelse
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Recent Activations</h3></div>
            <div class="divide-y divide-gray-700">
                @forelse($activations as $act)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between"><p class="text-sm font-medium text-gray-200">{{ $act->facility_name ?? '—' }}</p><span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($act->created_at)->format('d M') }}</span></div>
                    <p class="text-xs text-gray-400 mt-0.5">Agent: {{ $act->agent_name }} · {{ $act->trigger_event }}</p>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No activations yet</div>
                @endforelse
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Commission Ledger</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-right">KSh</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($ledger as $entry)
                        <tr class="hover:bg-gray-900">
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $entry->entry_type }}</td>
                            <td class="px-4 py-2.5 text-right font-medium text-gray-200">{{ number_format($entry->amount_kes, 2) }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-400">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No ledger entries</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
