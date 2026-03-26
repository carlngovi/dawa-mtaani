@extends('layouts.app')
@section('title', 'Support Tickets — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Support Tickets</h1>
            <p class="text-sm text-gray-400 mt-1">Read-only support console</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    @if($tickets->total() > 0)

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Open</p>
            <p class="text-3xl font-bold text-{{ $stats['open'] > 0 ? 'amber-400' : 'white' }} mt-1">
                {{ $stats['open'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-green-800 p-5">
            <p class="text-xs text-green-400">Resolved</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['resolved'] }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Ticket #</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Issue</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Priority</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">
                            {{ $ticket->id ?? '—' }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-200">
                            {{ $ticket->facility_name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ isset($ticket->issue_type) ? str_replace('_', ' ', $ticket->issue_type) : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($ticket->status ?? '') {
                                    'OPEN'        => 'bg-amber-900/30 text-amber-400',
                                    'IN_PROGRESS' => 'bg-blue-900/30 text-blue-400',
                                    'RESOLVED'    => 'bg-green-900/30 text-green-400',
                                    'CLOSED'      => 'bg-gray-700 text-gray-400',
                                    default       => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $ticket->status ?? '—') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $ticket->priority ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($ticket->created_at)
                                {{ \Carbon\Carbon::parse($ticket->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else
                                —
                            @endisset
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $tickets->links() }}</div>

    @else
    {{-- Empty / not yet active --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center space-y-4">
        <p class="text-gray-400 text-sm font-medium">
            Support ticket management launches in Phase 2.
        </p>
        <p class="text-gray-400 text-sm">
            Use the lookup tools below to assist users now.
        </p>
        <div class="flex justify-center gap-4 pt-2">
            <a href="/support/facilities"
               class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
                Facility Lookup →
            </a>
            <a href="/support/orders"
               class="px-4 py-2 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Order Lookup →
            </a>
        </div>
    </div>
    @endif

</div>
@endsection