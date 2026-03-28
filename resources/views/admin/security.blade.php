@extends('layouts.admin')
@section('title', 'Security Events — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Security Events</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Critical Unresolved</p>
            <p class="text-2xl font-bold {{ $summary['critical_unresolved'] > 0 ? 'text-red-400' : 'text-white' }}">
                {{ $summary['critical_unresolved'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">High Unresolved</p>
            <p class="text-2xl font-bold {{ $summary['high_unresolved'] > 0 ? 'text-amber-400' : 'text-white' }}">
                {{ $summary['high_unresolved'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Last 24h</p>
            <p class="text-2xl font-bold text-white">{{ number_format($summary['last_24h']) }}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Event</th>
                    <th class="px-5 py-3 text-left">Severity</th>
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">IP</th>
                    <th class="px-5 py-3 text-left">Time</th>
                    <th class="px-5 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($events as $event)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3">
                        <span class="text-xs font-mono text-gray-400">{{ str_replace('_',' ',$event->event_type) }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($event->severity) {
                                'CRITICAL' => 'bg-red-900/30 text-red-400 border border-gray-700',
                                'HIGH'     => 'bg-orange-900/30 text-orange-400 border border-gray-700',
                                'MEDIUM'   => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                'LOW'      => 'bg-blue-900/30 text-blue-400 border border-gray-700',
                                default    => 'bg-gray-700 text-gray-400'
                            } }}">{{ $event->severity }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $event->user_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $event->facility_name ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $event->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        @if($event->resolved_at)
                            <span class="text-xs text-green-400">Resolved</span>
                        @else
                            <span class="text-xs text-amber-400">Open</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No security events</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $events->links() }}</div>
    </div>
</div>
@endsection
