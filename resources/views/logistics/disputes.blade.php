@extends('layouts.app')
@section('title', 'Disputes — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ respondId: null, respondText: '' }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Delivery Disputes</h1>
        <p class="text-sm text-gray-400 mt-1">Respond within 24 hours or the dispute escalates automatically</p>
    </div>

    {{-- Info --}}
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        SGA must respond within 24 hours of a dispute being raised.
        Non-response automatically escalates to a Network Field Agent for investigation.
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Open</p>
            <p class="text-3xl font-bold text-{{ $stats['open'] > 0 ? 'amber-400' : 'white' }} mt-1">
                {{ $stats['open'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">SLA Breached</p>
            <p class="text-3xl font-bold text-{{ $stats['breached'] > 0 ? 'red-400' : 'white' }} mt-1">
                {{ $stats['breached'] }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Resolved This Month</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['resolved'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['OPEN', 'UNDER_REVIEW', 'RESOLVED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $s) }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
            Filter
        </button>
        @if(request('status'))
            <a href="/logistics/disputes"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Order</th>
                        <th class="px-5 py-3 text-left">Retail Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left">Reason</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Raised</th>
                        <th class="px-5 py-3 text-left">SLA Deadline</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($disputes as $dispute)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">
                            {{ substr($dispute->order_ulid, -8) }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $dispute->facility_name }}</td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $dispute->county }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">
                            {{ str_replace('_', ' ', $dispute->reason) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ \Carbon\Carbon::parse($dispute->raised_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="{{ $dispute->sla_breached && $dispute->status === 'OPEN' ? 'text-red-400 font-semibold' : 'text-gray-400' }} text-xs">
                                {{ \Carbon\Carbon::parse($dispute->sla_deadline_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                            </span>
                            @if($dispute->sla_breached && $dispute->status === 'OPEN')
                                <span class="inline-flex ml-1 px-1.5 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400">
                                    BREACHED
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($dispute->status) {
                                    'OPEN'         => 'bg-amber-900/30 text-amber-400',
                                    'UNDER_REVIEW' => 'bg-blue-900/30 text-blue-400',
                                    'RESOLVED'     => 'bg-green-900/30 text-green-400',
                                    default        => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $dispute->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if(in_array($dispute->status, ['OPEN', 'UNDER_REVIEW']))
                                <button @click="respondId = {{ $dispute->id }}; respondText = ''"
                                        class="px-3 py-1.5 bg-yellow-400 text-gray-900 rounded-lg text-xs hover:bg-yellow-500 transition-colors">
                                    Respond
                                </button>
                            @else
                                <span class="text-xs text-gray-400">Closed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No disputes found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $disputes->links() }}</div>

    {{-- Respond modal --}}
    <div x-show="respondId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-base font-semibold text-white">Submit SGA Response</h3>
            <p class="text-sm text-gray-400 mt-1">
                Your response is recorded. If unresolved, it escalates to the field agent.
            </p>
            <form method="POST"
                  :action="'/api/v1/disputes/' + respondId + '/respond'"
                  class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">SGA Position</label>
                    <textarea name="sga_position" x-model="respondText"
                              rows="4" required minlength="20"
                              placeholder="Describe SGA's position (minimum 20 characters)..."
                              class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                        Submit Response
                    </button>
                    <button type="button" @click="respondId = null"
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
