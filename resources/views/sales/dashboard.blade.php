@extends('layouts.sales')

@section('title', 'Sales Rep Dashboard — Dawa Mtaani')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Sales Rep Dashboard</h1>
        <p class="text-sm text-gray-400 mt-1">{{ auth()->user()->name }} · {{ $spotterCount }} Spotter(s) assigned</p>
    </div>

    @if($pendingDuplicates > 0 || $overdueFollowUps > 0)
    <div class="bg-gray-800 border border-yellow-400/30 rounded-2xl p-4">
        <div class="text-yellow-400 text-xs uppercase tracking-widest mb-3">Pending Actions</div>
        @if($pendingDuplicates > 0)
        <a href="{{ route('sales.duplicates.index') }}" class="flex items-center justify-between py-2 border-b border-gray-700">
            <span class="text-white text-sm">Duplicate Reviews (Tier 1)</span>
            <span class="bg-orange-400/10 text-orange-400 text-xs px-2 py-0.5 rounded-full">{{ $pendingDuplicates }}</span>
        </a>
        @endif
        @if($overdueFollowUps > 0)
        <a href="{{ route('sales.followups.index') }}" class="flex items-center justify-between py-2">
            <span class="text-white text-sm">Overdue Follow-ups</span>
            <span class="bg-red-400/10 text-red-400 text-xs px-2 py-0.5 rounded-full">{{ $overdueFollowUps }}</span>
        </a>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('sales.submissions.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Submissions Today</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $submissionsToday }}</p>
        </a>
        <a href="{{ route('sales.duplicates.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Pending SR Reviews</p>
            <p class="text-3xl font-bold text-orange-400 mt-1">{{ $pendingDuplicates }}</p>
        </a>
        <a href="{{ route('sales.followups.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition">
            <p class="text-sm text-gray-400">Overdue Follow-ups</p>
            <p class="text-3xl font-bold text-red-400 mt-1">{{ $overdueFollowUps }}</p>
        </a>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <p class="text-sm text-gray-400">My Spotters</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $spotterCount }}</p>
        </div>
    </div>

    <div>
        <h2 class="text-white font-bold mb-3">My Spotters</h2>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase tracking-widest">
                        <th class="px-4 py-3 text-left">Spotter</th>
                        <th class="px-4 py-3 text-center">Today</th>
                        <th class="px-4 py-3 text-center">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($mySpotters as $spotter)
                    <tr>
                        <td class="px-4 py-3 text-white font-medium">{{ $spotter->name }}</td>
                        <td class="px-4 py-3 text-center {{ $spotter->today_submissions > 0 ? 'text-yellow-400' : 'text-gray-500' }}">{{ $spotter->today_submissions }}</td>
                        <td class="px-4 py-3 text-center text-gray-300">{{ $spotter->total_submissions }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($spotter->today_submissions > 0)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-400/10 text-green-400">Active</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-400">No activity</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No spotters assigned</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
