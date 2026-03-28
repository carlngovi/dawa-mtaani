@extends('layouts.app')
@section('title', 'Placer Management — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Placer Management</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $group->group_name }}</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Only an admin (Tier 3+) can add or remove authorised placers.
        Contact Network Admin to make changes.
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Placer Name</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Outlet</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($placers as $placer)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $placer->name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $placer->email }}</td>
                        <td class="px-5 py-3 text-gray-300">{{ $placer->facility_name }}</td>
                        <td class="px-5 py-3">
                            @if($placer->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400">Active</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($placer->added_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No authorised placers found for your outlets
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-400">
        To add a new placer, contact Network Admin. Only admins (Tier 3+) can manage placer authorisation.
    </p>

</div>
@endsection