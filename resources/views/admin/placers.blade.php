@extends('layouts.admin')
@section('title', 'Placer Approvals — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ confirmId: null, confirmName: '', confirmFacility: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Authorised Order Placers</h1>
            <p class="text-sm text-gray-400 mt-1">Managed exclusively by admin. Every order is logged to a specific placer.</p>
        </div>
    </div>

    {{-- Spec info --}}
    <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Group owners are <strong>not</strong> automatically authorised as placers on account creation.
        Each outlet must be explicitly added by an admin (Tier 3+).
        To add a new placer, use <a href="/admin/invitations" class="underline font-medium">Invitations</a>.
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Total Placers</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-green-800 p-5">
            <p class="text-xs text-green-400">Active</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Inactive</p>
            <p class="text-3xl font-bold text-gray-400 mt-1">{{ $stats['inactive'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="active" class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All placers</option>
            <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active only</option>
            <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive only</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search placer or facility..."
               class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm w-64 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['active', 'search']))
            <a href="/admin/placers"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Placer Name</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Facility</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Added</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($placers as $placer)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $placer->user_name }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $placer->email }}</td>
                        <td class="px-5 py-3">
                            <a href="/admin/facilities/{{ $placer->facility_ulid }}"
                               class="text-green-400 hover:underline text-xs font-medium">
                                {{ $placer->facility_name }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $placer->county }}</td>
                        <td class="px-5 py-3">
                            @if($placer->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400 border border-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($placer->added_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3">
                            @if($placer->is_active)
                                <button
                                    @click="confirmId = {{ $placer->id }}; confirmName = '{{ addslashes($placer->user_name) }}'; confirmFacility = '{{ addslashes($placer->facility_name) }}'"
                                    class="px-3 py-1.5 border border-red-800 text-red-400 rounded-lg text-xs hover:bg-red-900/20 transition-colors">
                                    Deactivate
                                </button>
                            @else
                                <button
                                    @click="confirmId = {{ $placer->id }}; confirmName = '{{ addslashes($placer->user_name) }}'; confirmFacility = '{{ addslashes($placer->facility_name) }}'"
                                    class="px-3 py-1.5 border border-green-800 text-green-400 rounded-lg text-xs hover:bg-green-900/20 transition-colors">
                                    Reinstate
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No placers found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $placers->links() }}</div>

    <p class="text-xs text-gray-400">
        To add a new placer, invite the user via
        <a href="/admin/invitations" class="text-green-400 hover:underline">Invitations</a>
        and assign their facility after account creation.
    </p>

    {{-- Confirm modal --}}
    <div x-show="confirmId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-white">Confirm Action</h3>
            <p class="text-sm text-gray-400 mt-2">
                You are about to change the status of
                <strong x-text="confirmName"></strong> at
                <strong x-text="confirmFacility"></strong>.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST"
                      :action="'/api/v1/placers/' + confirmId + '/toggle'"
                      class="flex-1">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                        Confirm
                    </button>
                </form>
                <button @click="confirmId = null"
                        class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection