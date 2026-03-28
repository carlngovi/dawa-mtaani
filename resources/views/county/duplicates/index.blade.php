@extends('layouts.county')
@section('title', 'CC Duplicate Reviews — County Coordinator')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Tier 2 — CC Duplicate Reviews</h1>
    <p class="text-sm text-gray-400">Verify submissions that have been cleared by Sales Reps</p>
    @forelse($reviews as $review)
        @php $sub = $review->submission; $match = $review->matchedSubmission; @endphp
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Submitted</p>
                    <p class="text-white font-medium">{{ $sub->pharmacy ?? '—' }}</p>
                    <p class="text-sm text-gray-400">{{ $sub->ward ?? '' }}, {{ $sub->county ?? '' }}</p>
                    <p class="text-xs text-gray-500">Spotter: {{ $sub->spotter->name ?? '—' }}</p>
                </div>
                <div class="flex flex-col items-center justify-center space-y-2 text-center">
                    @if($review->gps_distance_metres !== null)
                    <span class="text-xs px-3 py-1 rounded-full font-bold {{ $review->gps_distance_metres <= 50 ? 'bg-red-400/20 text-red-400' : 'bg-green-400/20 text-green-400' }}">{{ $review->gps_distance_metres }}m</span>
                    @endif
                    @if($review->name_edit_distance !== null)
                    <span class="text-xs px-3 py-1 rounded-full font-bold bg-orange-400/20 text-orange-400">Edit dist: {{ $review->name_edit_distance }}</span>
                    @endif
                </div>
                <div class="space-y-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Match</p>
                    @if($match)<p class="text-white font-medium">{{ $match->pharmacy }}</p><p class="text-sm text-gray-400">{{ $match->ward }}, {{ $match->county }}</p>@else <p class="text-gray-500">Unavailable</p>@endif
                </div>
            </div>
            <div class="flex gap-3 mt-4 pt-4 border-t border-gray-700">
                <form method="POST" action="{{ route('county.duplicates.decide', $review) }}">@csrf<input type="hidden" name="decision" value="confirmed_duplicate"><button class="px-4 py-2 bg-red-400/20 text-red-400 rounded-lg text-sm font-medium hover:bg-red-400/30">Confirm Duplicate</button></form>
                <form method="POST" action="{{ route('county.duplicates.decide', $review) }}">@csrf<input type="hidden" name="decision" value="not_duplicate"><button class="px-4 py-2 bg-green-400/20 text-green-400 rounded-lg text-sm font-medium hover:bg-green-400/30">Verify → Admin</button></form>
            </div>
        </div>
    @empty
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-8 text-center text-gray-500">No pending CC reviews.</div>
    @endforelse
    <div>{{ $reviews->links() }}</div>
</div>
@endsection
