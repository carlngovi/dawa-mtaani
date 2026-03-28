@extends('layouts.county')

@section('title', 'County Coordinator Dashboard — Dawa Mtaani')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">County Coordinator Dashboard</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $county }} County</p>
    </div>

    @if($pendingDuplicates > 0 || $overdueFollowUps > 0)
    <div class="bg-gray-800 border border-yellow-400/30 rounded-2xl p-4">
        <div class="text-yellow-400 text-xs uppercase tracking-widest mb-3">Pending Actions</div>
        @if($pendingDuplicates > 0)
        <a href="{{ route('county.duplicates.index') }}" class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-white text-sm">CC Duplicate Reviews</span>
            <span class="bg-orange-400/10 text-orange-400 text-xs px-2 py-0.5 rounded-full">{{ $pendingDuplicates }}</span>
        </a>
        @endif
        @if($overdueFollowUps > 0)
        <a href="{{ route('county.followups.index') }}" class="flex items-center justify-between py-2">
            <span class="text-white text-sm">Overdue Follow-ups</span>
            <span class="bg-red-400/10 text-red-400 text-xs px-2 py-0.5 rounded-full">{{ $overdueFollowUps }}</span>
        </a>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ route('county.submissions.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Today</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $submissionsToday }}</p>
        </a>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <p class="text-sm text-gray-400">Total Submitted</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $totalSubmissions }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <p class="text-sm text-gray-400">Accepted</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $acceptedCount }}</p>
        </div>
        <a href="{{ route('county.duplicates.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Pending CC Reviews</p>
            <p class="text-3xl font-bold text-orange-400 mt-1">{{ $pendingDuplicates }}</p>
        </a>
        <a href="{{ route('county.followups.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Overdue</p>
            <p class="text-3xl font-bold text-red-400 mt-1">{{ $overdueFollowUps }}</p>
        </a>
    </div>

    @if($totalSubmissions > 0)
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-white text-sm font-medium">County Progress</span>
            <span class="text-gray-400 text-xs">{{ $acceptedCount }} / {{ $totalSubmissions }} accepted</span>
        </div>
        <div class="w-full bg-gray-700 rounded-full h-3">
            <div class="bg-yellow-400 h-3 rounded-full transition-all" style="width: {{ min(100, round($acceptedCount / max($totalSubmissions, 1) * 100)) }}%"></div>
        </div>
    </div>
    @endif
</div>
@endsection
