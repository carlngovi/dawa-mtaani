@extends('layouts.app')
@section('title', 'Support Tickets — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
            <p class="text-sm text-gray-500 mt-1">Read-only support console</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    @if($tickets->total() > 0)

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-{{ $stats['open'] > 0 ? 'amber' : 'gray' }}-200 p-5">
            <p class="text-xs text-gray-400">Open</p>
            <p class="text-3xl font-bold text-{{ $stats['open'] > 0 ? 'amber-600' : 'gray-900' }} mt-1">
                {{ $stats['open'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-5">
            <p class="text-xs text-green-600">Resolved</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $stats['resolved'] }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Ticket #</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Issue</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Priority</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">
                            {{ $ticket->id ?? '—' }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $ticket->facility_name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600 hidden md:table-cell">
                            {{ isset($ticket->issue_type) ? str_replace('_', ' ', $ticket->issue_type) : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($ticket->status ?? '') {
                                    'OPEN'        => 'bg-amber-100 text-amber-700',
                                    'IN_PROGRESS' => 'bg-blue-100 text-blue-700',
                                    'RESOLVED'    => 'bg-green-100 text-green-700',
                                    'CLOSED'      => 'bg-gray-100 text-gray-500',
                                    default       => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $ticket->status ?? '—') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell">
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
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center space-y-4">
        <p class="text-gray-500 text-sm font-medium">
            Support ticket management launches in Phase 2.
        </p>
        <p class="text-gray-400 text-sm">
            Use the lookup tools below to assist users now.
        </p>
        <div class="flex justify-center gap-4 pt-2">
            <a href="/support/facilities"
               class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                Facility Lookup →
            </a>
            <a href="/support/orders"
               class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Order Lookup →
            </a>
        </div>
    </div>
    @endif

</div>
@endsection