@extends('layouts.admin')
@section('title', 'Disputes — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Delivery Disputes</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Open</p>
            <p class="text-2xl font-bold {{ $stats['open'] > 0 ? 'text-red-400' : 'text-white' }}">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Under Review</p>
            <p class="text-2xl font-bold text-amber-400">{{ $stats['under_review'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">SLA Breached</p>
            <p class="text-2xl font-bold {{ $stats['sla_breached'] > 0 ? 'text-red-400' : 'text-white' }}">{{ $stats['sla_breached'] }}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Order</th>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">Reason</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">SLA</th>
                    <th class="px-5 py-3 text-left">Raised</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($disputes as $dispute)
                <tr class="hover:bg-gray-900 {{ $dispute->sla_breached ? 'bg-red-900/20' : '' }}">
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ substr($dispute->order_ulid ?? '—', -8) }}</td>
                    <td class="px-5 py-3">
                        <p class="text-gray-200">{{ $dispute->facility_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $dispute->county ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
                            {{ str_replace('_', ' ', $dispute->reason) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $dispute->status === 'OPEN' ? 'bg-red-900/30 text-red-400 border border-gray-700' : ($dispute->status === 'UNDER_REVIEW' ? 'bg-amber-900/30 text-amber-400 border border-amber-800' : 'bg-green-900/30 text-green-400 border border-gray-700') }}">
                            {{ $dispute->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs {{ $dispute->sla_breached ? 'text-red-400 font-semibold' : 'text-gray-400' }}">
                        {{ $dispute->sla_breached ? 'BREACHED' : \Carbon\Carbon::parse($dispute->sla_deadline_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($dispute->raised_at)->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No disputes</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $disputes->links() }}</div>
    </div>
</div>
@endsection
