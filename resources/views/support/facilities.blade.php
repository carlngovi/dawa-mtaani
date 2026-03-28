@extends('layouts.app')
@section('title', 'Facility Lookup — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Facility Lookup</h1>
            <p class="text-sm text-gray-400 mt-1">Search by name, PPB licence, or phone number</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Facility name, PPB licence, or phone (min 3 characters)..."
               class="flex-1 px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            Search
        </button>
        @if(request('search'))
            <a href="/support/facilities"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    @if(! $searched)
    {{-- Prompt --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">Enter a search term above to look up a pharmacy</p>
    </div>

    @elseif($facilities->isEmpty())
    {{-- No results --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No facilities found matching "{{ request('search') }}"</p>
    </div>

    @else
    {{-- Results --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-700">
            <p class="text-xs text-gray-400">{{ $facilities->count() }} result(s) for "{{ request('search') }}"</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Type</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left">Network</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Phone</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Registered</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($facilities as $facility)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-200">{{ $facility->facility_name }}</p>
                            <p class="text-xs font-mono text-gray-400">{{ $facility->ppb_licence }}</p>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $facility->facility_type ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $facility->county }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $facility->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                                {{ $facility->network_membership === 'NETWORK' ? 'Network' : 'Off-Network' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($facility->facility_status) {
                                    'ACTIVE'         => 'bg-green-900/30 text-green-400',
                                    'APPLIED'        => 'bg-gray-700 text-gray-400',
                                    'PPB_VERIFIED'   => 'bg-blue-900/30 text-blue-400',
                                    'ACCOUNT_LINKED' => 'bg-amber-900/30 text-amber-400',
                                    'SUSPENDED'      => 'bg-red-900/30 text-red-400',
                                    default          => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $facility->facility_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $facility->phone ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($facility->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <a href="/admin/facilities/{{ $facility->ulid }}"
                               class="px-3 py-1.5 border border-gray-700 text-gray-400 rounded-lg text-xs hover:bg-gray-900">
                                View Profile →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-400">
        Read-only. Contact Network Admin for account changes or resets.
    </p>
    @endif

</div>
@endsection