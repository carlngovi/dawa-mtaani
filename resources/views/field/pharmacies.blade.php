@extends('layouts.app')
@section('title', 'My Pharmacies — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $county)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        No county assigned to your account. Contact super_admin to assign your county before you can manage pharmacies.
    </div>
    @else

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $county }} County Pharmacies</h1>
            <p class="text-xs text-gray-400 mt-1">County scope enforced · showing {{ $county }} pharmacies only</p>
        </div>
        <div class="flex gap-3">
            <a href="/field/register"
               class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                + Register Pharmacy
            </a>
            <a href="/field/gps"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Capture GPS
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-5">
            <p class="text-xs text-green-600">Active</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-{{ $stats['pending'] > 0 ? 'amber' : 'gray' }}-200 p-5">
            <p class="text-xs text-gray-400">Pending Review</p>
            <p class="text-3xl font-bold text-{{ $stats['pending'] > 0 ? 'amber-600' : 'gray-900' }} mt-1">
                {{ $stats['pending'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-{{ $stats['gps_pending'] > 0 ? 'amber' : 'gray' }}-200 p-5">
            <p class="text-xs text-gray-400">GPS Pending</p>
            <p class="text-3xl font-bold text-{{ $stats['gps_pending'] > 0 ? 'amber-600' : 'gray-900' }} mt-1">
                {{ $stats['gps_pending'] }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All statuses</option>
            @foreach(['ACTIVE','APPLIED','PPB_VERIFIED','ACCOUNT_LINKED','SUSPENDED','PAUSED','CHURNED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $s) }}
                </option>
            @endforeach
        </select>
        <select name="gps"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All GPS</option>
            <option value="pending"  {{ request('gps') === 'pending'  ? 'selected' : '' }}>GPS Pending</option>
            <option value="captured" {{ request('gps') === 'captured' ? 'selected' : '' }}>GPS Captured</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name or licence..."
               class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['status','gps','search']))
            <a href="/field/pharmacies"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">PPB Licence</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Ward</th>
                        <th class="px-5 py-3 text-left">Network</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">GPS</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Registered</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $facility->facility_name }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $facility->ppb_licence }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $facility->ward }}</td>
                        <td class="px-5 py-3">
                            @if($facility->network_membership === 'NETWORK')
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Network</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Off-Network</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($facility->facility_status) {
                                    'ACTIVE'         => 'bg-green-100 text-green-700',
                                    'APPLIED'        => 'bg-gray-100 text-gray-600',
                                    'PPB_VERIFIED'   => 'bg-blue-100 text-blue-700',
                                    'ACCOUNT_LINKED' => 'bg-amber-100 text-amber-700',
                                    'SUSPENDED'      => 'bg-red-100 text-red-700',
                                    'PAUSED'         => 'bg-orange-100 text-orange-700',
                                    'CHURNED'        => 'bg-gray-100 text-gray-500',
                                    default          => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $facility->facility_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($facility->latitude)
                                <span class="text-green-600 text-xs font-medium">Captured</span>
                            @else
                                <a href="/field/gps/{{ $facility->ulid }}"
                                   class="text-amber-600 text-xs hover:underline">
                                    Capture GPS →
                                </a>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($facility->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3 flex gap-2">
                            <a href="/admin/facilities/{{ $facility->ulid }}"
                               class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg text-xs hover:bg-gray-50">
                                View
                            </a>
                            @if(! $facility->latitude)
                            <a href="/field/gps/{{ $facility->ulid }}"
                               class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs hover:bg-amber-200">
                                GPS
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No pharmacies found in {{ $county }} county
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $facilities->links() }}</div>

    @endif
</div>
@endsection
