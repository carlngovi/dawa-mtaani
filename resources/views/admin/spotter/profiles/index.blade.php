@extends('layouts.admin')

@section('title', 'Spotter Profiles — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Spotter Profiles</h1>
            <p class="text-sm text-gray-400 mt-1">Assign county, ward, and sales rep to field agents</p>
        </div>
        <a href="{{ route('admin.spotter.profiles.create') }}"
           class="bg-yellow-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm hover:bg-yellow-300 transition">
            + New Profile
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-400/10 border border-green-400/30 text-green-400 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase tracking-widest">
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">County</th>
                    <th class="px-4 py-3 text-left">Ward</th>
                    <th class="px-4 py-3 text-left">Sales Rep</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($spotters as $spotter)
                    <tr class="hover:bg-gray-700/50 transition">
                        <td class="px-4 py-3 text-white font-medium">{{ $spotter->name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $spotter->email }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $spotter->spotterProfile?->county ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $spotter->spotterProfile?->ward ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $spotter->spotterProfile?->getSalesRepName() ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($spotter->spotterProfile)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-400/10 text-green-400 font-medium">Set</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-400/10 text-red-400 font-medium">Not Set</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($spotter->spotterProfile)
                                <a href="{{ route('admin.spotter.profiles.edit', $spotter->spotterProfile) }}"
                                   class="text-yellow-400 text-xs hover:underline">Edit</a>
                            @else
                                <a href="{{ route('admin.spotter.profiles.create') }}?user_id={{ $spotter->id }}"
                                   class="text-yellow-400 text-xs hover:underline">Set Up Profile</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No field agents found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
