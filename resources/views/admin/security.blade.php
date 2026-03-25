@extends('layouts.admin')
@section('title', 'Security Events — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Security Events</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Critical Unresolved</p>
            <p class="text-2xl font-bold {{ $summary['critical_unresolved'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $summary['critical_unresolved'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">High Unresolved</p>
            <p class="text-2xl font-bold {{ $summary['high_unresolved'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                {{ $summary['high_unresolved'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Last 24h</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['last_24h']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <span class="text-xs font-mono text-gray-600">{{ str_replace('_',' ',$event->event_type) }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($event->severity) {
                                'CRITICAL' => 'bg-red-100 text-red-700',
                                'HIGH'     => 'bg-orange-100 text-orange-700',
                                'MEDIUM'   => 'bg-amber-100 text-amber-700',
                                'LOW'      => 'bg-blue-100 text-blue-700',
                                default    => 'bg-gray-100 text-gray-600'
                            } }}">{{ $event->severity }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $event->user_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $event->facility_name ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $event->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        @if($event->resolved_at)
                            <span class="text-xs text-green-600">Resolved</span>
                        @else
                            <span class="text-xs text-amber-600">Open</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No security events</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $events->links() }}</div>
    </div>
</div>
@endsection
