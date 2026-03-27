@extends('layouts.admin')

@section('title', 'Spotter Dashboard — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Spotter Field Agent</h1>
        <p class="text-sm text-gray-400 mt-1">Overview of field agent activity</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <a href="{{ route('admin.spotter.submissions.index') }}"
           class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Submissions Today</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $submissionsToday }}</p>
        </a>

        <a href="{{ route('admin.spotter.duplicates.index') }}"
           class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Pending Duplicates</p>
            <p class="text-3xl font-bold text-orange-400 mt-1">{{ $pendingDuplicates }}</p>
        </a>

        <a href="{{ route('admin.spotter.followups.index') }}"
           class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Overdue Follow-ups</p>
            <p class="text-3xl font-bold text-red-400 mt-1">{{ $overdueFollowUps }}</p>
        </a>

        <a href="{{ route('admin.spotter.activations.index') }}"
           class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Active Tokens</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $activeTokens }}</p>
        </a>

    </div>

</div>

@endsection
