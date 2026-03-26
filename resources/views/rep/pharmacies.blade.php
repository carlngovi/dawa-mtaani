@extends('layouts.app')
@section('title', 'Pharmacy List — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $county)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        No county assigned to your account. Contact super_admin to assign your county.
    </div>
    @else

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $county }} County Pharmacies</h1>
            <p class="text-xs text-gray-400 mt-1">
                Read-only activation view · No financial data shown · {{ $county }} county only
            </p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name or licence..."
               class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-green-500">
        <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All statuses</option>
            @foreach(['ACTIVE','APPLIED','PPB_VERIFIED','ACCOUNT_LINKED','SUSPENDED','PAUSED','CHURNED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $s) }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search', 'status']))
            <a href="/rep/pharmacies"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility Name</th>
                        <th class="px-5 py-3 text-left">PPB Licence</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Ward</th>
                        <th class="px-5 py-3 text-left">Network</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                    <tr class="hover:bg-gray-50 cursor-pointer"
                        onclick="window.location='/rep/pharmacies/{{ $facility->ulid }}'">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $facility->facility_name }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $facility->ppb_licence }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $facility->ward }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $facility->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $facility->network_membership === 'NETWORK' ? 'Network' : 'Off-Network' }}
                            </span>
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
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($facility->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
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
