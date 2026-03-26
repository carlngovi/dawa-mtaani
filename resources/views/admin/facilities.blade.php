@extends('layouts.admin')

@section('title', 'Facilities — Dawa Mtaani')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Facilities</h1>
        <a href="/admin/facilities?gps_pending=1" class="text-sm text-yellow-500 hover:underline">
            {{ $stats['gps_pending'] }} GPS pending
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Total</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Active</p>
            <p class="text-2xl font-bold text-green-400">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Network</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['network']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <p class="text-xs text-gray-400">Off-network</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total'] - $stats['network']) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, licence, phone..."
                   class="col-span-2 w-full text-sm bg-gray-800 border border-gray-600 text-white placeholder-gray-500 rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
            <select name="status" class="text-sm bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                <option value="">All statuses</option>
                @foreach(['ACTIVE','SUSPENDED','PAUSED','CHURNED'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="membership" class="text-sm bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                <option value="">All membership</option>
                <option value="NETWORK" {{ request('membership') == 'NETWORK' ? 'selected' : '' }}>Network</option>
                <option value="OFF_NETWORK" {{ request('membership') == 'OFF_NETWORK' ? 'selected' : '' }}>Off-network</option>
            </select>
            <div class="flex gap-2">
                <select name="county" class="flex-1 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none">
                    <option value="">All counties</option>
                    @foreach($counties as $county)
                        <option value="{{ $county }}" {{ request('county') == $county ? 'selected' : '' }}>{{ $county }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-lg transition-colors">
                    Filter
                </button>
            </div>
        </div>
    </form>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">Licence</th>
                        <th class="px-5 py-3 text-left">County</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Membership</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">GPS</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($facilities as $facility)
                        <tr class="hover:bg-gray-900">
                            <td class="px-5 py-3">
                                <p class="font-medium text-white">{{ $facility->facility_name }}</p>
                                <p class="text-xs text-gray-400">{{ $facility->phone }}</p>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-400">
                                {{ $facility->ppb_licence_number }}
                            </td>
                            <td class="px-5 py-3 text-gray-400">
                                {{ $facility->county }}
                                <span class="text-xs text-gray-400 block">{{ $facility->ward }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
                                    {{ $facility->ppb_facility_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $facility->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-gray-700 text-gray-400' }}">
                                    {{ $facility->network_membership }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($facility->facility_status) {
                                        'ACTIVE' => 'bg-green-900/30 text-green-400 border border-green-800',
                                        'SUSPENDED' => 'bg-red-900/30 text-red-400 border border-red-800',
                                        'PAUSED' => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                        default => 'bg-gray-700 text-gray-400'
                                    } }}">
                                    {{ $facility->facility_status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($facility->latitude)
                                    <span class="text-green-500 text-xs">✓</span>
                                @else
                                    <span class="text-amber-500 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <a href="/admin/facilities/{{ $facility->ulid }}"
                                   class="text-green-400 text-xs hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                                No facilities found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-700">
            {{ $facilities->links() }}
        </div>
    </div>

</div>
@endsection
