@extends('layouts.admin')
@section('title', 'Registrations — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registrations</h1>
            <p class="text-sm text-gray-500 mt-1">Admin review is mandatory before any facility becomes ACTIVE</p>
        </div>
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
            {{ $counts['applied'] + $counts['ppb_verified'] + $counts['acct_linked'] }} Pending
        </span>
    </div>

    {{-- Spec requirement alert --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        No facility is activated automatically. PPB verification is a prerequisite for admin review — not a substitute for it.
        An admin (Tier 3 or above) must approve every registration before it reaches ACTIVE status.
    </div>

    {{-- Pipeline stage cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="/admin/registrations?stage=APPLIED"
           class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow block {{ request('stage') === 'APPLIED' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Applied</p>
            <p class="text-3xl font-bold text-gray-600 mt-1">{{ $counts['applied'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Awaiting PPB verification</p>
        </a>
        <a href="/admin/registrations?stage=PPB_VERIFIED"
           class="bg-white rounded-xl border border-blue-200 p-5 hover:shadow-md transition-shadow block {{ request('stage') === 'PPB_VERIFIED' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-xs text-blue-500 uppercase tracking-wider">PPB Verified</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $counts['ppb_verified'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Ready for admin review</p>
        </a>
        <a href="/admin/registrations?stage=ACCOUNT_LINKED"
           class="bg-white rounded-xl border border-amber-200 p-5 hover:shadow-md transition-shadow block {{ request('stage') === 'ACCOUNT_LINKED' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-xs text-amber-500 uppercase tracking-wider">Account Linked</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $counts['acct_linked'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Ready for activation</p>
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="stage" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All stages</option>
            <option value="APPLIED"        {{ request('stage') === 'APPLIED'        ? 'selected' : '' }}>Applied</option>
            <option value="PPB_VERIFIED"   {{ request('stage') === 'PPB_VERIFIED'   ? 'selected' : '' }}>PPB Verified</option>
            <option value="ACCOUNT_LINKED" {{ request('stage') === 'ACCOUNT_LINKED' ? 'selected' : '' }}>Account Linked</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Name, PPB licence, owner, phone..."
               class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm w-72 focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['stage', 'search']))
            <a href="/admin/registrations"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1000px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left">Owner</th>
                        <th class="px-5 py-3 text-left">PPB Licence</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Phone</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Submitted</th>
                        <th class="px-5 py-3 text-left">PPB Status</th>
                        <th class="px-5 py-3 text-left">Stage</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pending as $facility)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $facility->facility_name }}</p>
                            <p class="text-xs text-gray-400">{{ $facility->ward }}</p>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700">
                            {{ $facility->owner_name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">
                            {{ $facility->ppb_licence_number ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $facility->county }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell">
                            {{ $facility->phone ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ $facility->created_at->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $ppbBadge = match($facility->ppb_licence_status) {
                                    'VALID'     => 'bg-green-100 text-green-700',
                                    'EXPIRED'   => 'bg-red-100 text-red-700',
                                    'SUSPENDED' => 'bg-orange-100 text-orange-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $ppbBadge }}">
                                {{ $facility->ppb_licence_status ?? 'PENDING' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $stageClass = match($facility->onboarding_status) {
                                    'APPLIED'        => 'bg-gray-100 text-gray-600',
                                    'PPB_VERIFIED'   => 'bg-blue-100 text-blue-700',
                                    'ACCOUNT_LINKED' => 'bg-amber-100 text-amber-700',
                                    default          => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $stageClass }}">
                                {{ str_replace('_', ' ', $facility->onboarding_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="/admin/registrations/{{ $facility->ulid }}"
                               class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800 transition-colors">
                                Review →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No registrations pending review
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $pending->links() }}</div>

</div>
@endsection