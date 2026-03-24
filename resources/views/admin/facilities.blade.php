@extends('layouts.admin')

@section('title', 'Facilities — Dawa Mtaani')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Facilities</h1>
        <a href="/admin/facilities?gps_pending=1" class="text-sm text-blue-600 hover:underline">
            {{ $stats['gps_pending'] }} GPS pending
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Active</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Network</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['network']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Off-network</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total'] - $stats['network']) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, licence, phone..."
                   class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-green-500">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All statuses</option>
                @foreach(['ACTIVE','SUSPENDED','PAUSED','CHURNED'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="membership" class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All membership</option>
                <option value="NETWORK" {{ request('membership') == 'NETWORK' ? 'selected' : '' }}>Network</option>
                <option value="OFF_NETWORK" {{ request('membership') == 'OFF_NETWORK' ? 'selected' : '' }}>Off-network</option>
            </select>
            <div class="flex gap-2">
                <select name="county" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm
                                              focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All counties</option>
                    @foreach($counties as $county)
                        <option value="{{ $county }}" {{ request('county') == $county ? 'selected' : '' }}>{{ $county }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">
                    Filter
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
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
                <tbody class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $facility->facility_name }}</p>
                                <p class="text-xs text-gray-400">{{ $facility->phone }}</p>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500">
                                {{ $facility->ppb_licence_number }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ $facility->county }}
                                <span class="text-xs text-gray-400 block">{{ $facility->ward }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $facility->ppb_facility_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $facility->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $facility->network_membership }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($facility->facility_status) {
                                        'ACTIVE' => 'bg-green-100 text-green-700',
                                        'SUSPENDED' => 'bg-red-100 text-red-700',
                                        'PAUSED' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-500'
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
                                   class="text-green-700 text-xs hover:underline">View</a>
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
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $facilities->links() }}
        </div>
    </div>

</div>
@endsection
