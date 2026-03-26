@extends('layouts.app')
@section('title', 'Operations Dashboard — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Operations Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Assistant Admin — operational access, no financial data</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
            Tier 3
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Financial data is visible from Tier 2 (admin) and above.
        This dashboard shows operational metrics only.
    </div>

    {{-- Action cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Pending registrations --}}
        <a href="/admin/registrations"
           class="bg-white rounded-xl border border-{{ $pendingRegistrations > 0 ? 'amber' : 'gray' }}-200 p-5 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Pending Registrations</p>
            <p class="text-3xl font-bold text-{{ $pendingRegistrations > 0 ? 'amber-600' : 'gray-900' }} mt-2">
                {{ $pendingRegistrations }}
            </p>
            <p class="text-xs text-gray-400 mt-2">Awaiting admin review</p>
            <p class="text-xs text-green-700 mt-3 font-medium">Review →</p>
        </a>

        {{-- Placer approvals --}}
        <a href="/admin/placers"
           class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Active Placers</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ $activePlacers }}
                <span class="text-base font-normal text-gray-400">/ {{ $totalPlacers }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-2">Authorised order placers</p>
            <p class="text-xs text-green-700 mt-3 font-medium">Manage →</p>
        </a>

        {{-- Open disputes --}}
        <a href="/admin/disputes"
           class="bg-white rounded-xl border border-{{ $openDisputes > 0 ? 'red' : 'gray' }}-200 p-5 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Open Disputes</p>
            <p class="text-3xl font-bold text-{{ $openDisputes > 0 ? 'red-600' : 'gray-900' }} mt-2">
                {{ $openDisputes }}
            </p>
            <p class="text-xs text-gray-400 mt-2">Unresolved delivery disputes</p>
            <p class="text-xs text-green-700 mt-3 font-medium">Review →</p>
        </a>

        {{-- Active alerts --}}
        <a href="/admin/monitoring"
           class="bg-white rounded-xl border border-{{ $activeAlerts > 0 ? 'red' : 'green' }}-200 p-5 hover:shadow-md transition-shadow block">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Active Alerts</p>
            @if($activeAlerts > 0)
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $activeAlerts }}</p>
                <p class="text-xs text-gray-400 mt-2">Critical / Warning alerts</p>
                <p class="text-xs text-red-600 mt-3 font-medium">Review →</p>
            @else
                <p class="text-3xl font-bold text-green-700 mt-2">All Clear</p>
                <p class="text-xs text-gray-400 mt-2">No active alerts</p>
                <p class="text-xs text-green-700 mt-3 font-medium">View monitoring →</p>
            @endif
        </a>

    </div>

    {{-- Quick links --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Quick Links</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach([
                '/admin/facilities'    => 'Pharmacies',
                '/admin/orders'        => 'Orders',
                '/admin/quality-flags' => 'Quality Flags',
                '/admin/ppb-registry'  => 'PPB Registry',
                '/admin/invitations'   => 'Invitations',
                '/admin/recruiter'     => 'Recruiter',
            ] as $href => $label)
            <a href="{{ $href }}"
               class="px-3 py-2 border border-gray-200 rounded-lg text-xs text-center text-gray-600 hover:bg-gray-50 hover:border-green-300 transition-colors">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Recent facilities --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Recently Registered</h3>
            <a href="/admin/registrations" class="text-xs text-green-700 hover:underline">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left hidden lg:table-cell">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentFacilities as $facility)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $facility->facility_name }}</td>
                    <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $facility->county }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($facility->facility_status) {
                                'ACTIVE'         => 'bg-green-100 text-green-700',
                                'APPLIED'        => 'bg-gray-100 text-gray-600',
                                'PPB_VERIFIED'   => 'bg-blue-100 text-blue-700',
                                'ACCOUNT_LINKED' => 'bg-amber-100 text-amber-700',
                                'SUSPENDED'      => 'bg-red-100 text-red-700',
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
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">
                        No facilities registered yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection