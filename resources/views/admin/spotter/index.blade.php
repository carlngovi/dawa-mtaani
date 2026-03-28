@extends('layouts.admin')

@section('title', 'Spotter Dashboard — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">{{ $roleLabel ?? 'Spotter Field Agent' }}</h1>
        <p class="text-sm text-gray-400 mt-1">
            @if($isSalesRep ?? false)
                Showing data for your {{ $spotterCount ?? 0 }} assigned Spotter(s)
            @elseif($isCC ?? false)
                Showing data for {{ $county ?? '' }} County
            @else
                Showing data across all counties
            @endif
        </p>
    </div>

    {{-- Pending Actions panel for Sales Rep and CC --}}
    @if(($isSalesRep ?? false) || ($isCC ?? false))
    <div class="bg-gray-800 border border-yellow-400/30 rounded-2xl p-4">
        <div class="text-yellow-400 text-xs uppercase tracking-widest mb-3">Pending Actions</div>
        @if($pendingDuplicates > 0)
            <a href="{{ route('admin.spotter.duplicates.index') }}" class="flex items-center justify-between py-2 border-b border-gray-700">
                <span class="text-white text-sm">Duplicate Reviews Awaiting</span>
                <span class="bg-orange-400/10 text-orange-400 text-xs px-2 py-0.5 rounded-full">{{ $pendingDuplicates }}</span>
            </a>
        @endif
        @if($overdueFollowUps > 0)
            <a href="{{ route('admin.spotter.followups.index') }}" class="flex items-center justify-between py-2">
                <span class="text-white text-sm">Overdue Follow-ups</span>
                <span class="bg-red-400/10 text-red-400 text-xs px-2 py-0.5 rounded-full">{{ $overdueFollowUps }}</span>
            </a>
        @endif
        @if($pendingDuplicates === 0 && $overdueFollowUps === 0)
            <p class="text-gray-500 text-sm">No pending actions</p>
        @endif
    </div>
    @endif

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ ($isAdmin ?? true) ? '4' : '3' }} gap-4">

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

        @if($isAdmin ?? true)
        <a href="{{ route('admin.spotter.activations.index') }}"
           class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Active Tokens</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $activeTokens ?? 0 }}</p>
        </a>
        @endif

    </div>

</div>

@endsection
