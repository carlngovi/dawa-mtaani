@extends('layouts.admin')

@section('title', 'Duplicate Reviews — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Pending Duplicate Reviews</h1>

    @if(session('success'))
        <div class="bg-green-900/20 border border-gray-700 text-green-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    @forelse($reviews as $review)
        @php
            $sub = $review->submission;
            $match = $review->matchedSubmission;
            $daysInQueue = $review->created_at->diffInDays(now());
        @endphp
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Submitted --}}
                <div class="space-y-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Submitted Pharmacy</p>
                    <p class="text-white font-medium">{{ $sub->pharmacy ?? '—' }}</p>
                    <p class="text-sm text-gray-400">{{ $sub->ward ?? '' }}, {{ $sub->county ?? '' }}</p>
                    <p class="text-sm text-gray-400">{{ $sub->address ?? '' }}</p>
                    <p class="text-xs text-gray-500">{{ $sub->lat ?? '' }}, {{ $sub->lng ?? '' }}</p>
                    @if($sub->potential)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $sub->potential->color() }}/20 text-{{ $sub->potential->color() }}">
                            {{ $sub->potential->value }}
                        </span>
                    @endif
                </div>

                {{-- Middle: distance info --}}
                <div class="flex flex-col items-center justify-center space-y-2 text-center">
                    @if($review->gps_distance_metres !== null)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $review->gps_distance_metres <= 50 ? 'bg-red-400/20 text-red-400' : 'bg-green-400/20 text-green-400' }}">
                            {{ $review->gps_distance_metres }} m
                        </span>
                    @endif
                    @if($review->name_edit_distance !== null)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-400/20 text-orange-400">
                            Edit dist: {{ $review->name_edit_distance }}
                        </span>
                    @endif
                    <p class="text-xs text-gray-500">Spotter: {{ $sub->spotter->name ?? '—' }}</p>
                    <p class="text-xs text-gray-500">{{ $daysInQueue }} day{{ $daysInQueue !== 1 ? 's' : '' }} in queue</p>
                    <p class="text-xs text-gray-500">Tier: {{ $review->tier->label() }}</p>
                </div>

                {{-- Matched --}}
                <div class="space-y-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Matched Pharmacy</p>
                    @if($match)
                        <p class="text-white font-medium">{{ $match->pharmacy }}</p>
                        <p class="text-sm text-gray-400">{{ $match->ward }}, {{ $match->county }}</p>
                        <p class="text-sm text-gray-400">{{ $match->address }}</p>
                        <p class="text-xs text-gray-500">{{ $match->lat }}, {{ $match->lng }}</p>
                        @if($match->potential)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $match->potential->color() }}/20 text-{{ $match->potential->color() }}">
                                {{ $match->potential->value }}
                            </span>
                        @endif
                    @else
                        <p class="text-gray-500">Match record unavailable</p>
                    @endif
                </div>

            </div>

            {{-- Action buttons --}}
            <div class="flex gap-3 mt-4 pt-4 border-t border-gray-700">
                @if(auth()->user()->hasRole('sales_rep') && $review->tier->value === 'sr')
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="confirmed_duplicate">
                        <button class="px-4 py-2 bg-red-400/20 text-red-400 rounded-lg text-sm font-medium hover:bg-red-400/30 transition">Confirm Duplicate</button>
                    </form>
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="not_duplicate">
                        <button class="px-4 py-2 bg-green-400/20 text-green-400 rounded-lg text-sm font-medium hover:bg-green-400/30 transition">Not a Duplicate → CC</button>
                    </form>
                @elseif(auth()->user()->hasRole('county_coordinator') && $review->tier->value === 'cc')
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="confirmed_duplicate">
                        <button class="px-4 py-2 bg-red-400/20 text-red-400 rounded-lg text-sm font-medium hover:bg-red-400/30 transition">Confirm Duplicate</button>
                    </form>
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="not_duplicate">
                        <button class="px-4 py-2 bg-green-400/20 text-green-400 rounded-lg text-sm font-medium hover:bg-green-400/30 transition">Verify → Admin</button>
                    </form>
                @elseif(auth()->user()->hasAnyRole(['admin', 'super_admin']) && $review->tier->value === 'admin')
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="not_duplicate">
                        <button class="px-4 py-2 bg-green-400/20 text-green-400 rounded-lg text-sm font-medium hover:bg-green-400/30 transition">Accept Submission</button>
                    </form>
                    <form method="POST" action="{{ route('admin.spotter.duplicates.decide', $review) }}">
                        @csrf
                        <input type="hidden" name="decision" value="confirmed_duplicate">
                        <button class="px-4 py-2 bg-red-400/20 text-red-400 rounded-lg text-sm font-medium hover:bg-red-400/30 transition">Reject Submission</button>
                    </form>
                @endif
            </div>

        </div>
    @empty
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-8 text-center text-gray-500">
            No pending duplicate reviews.
        </div>
    @endforelse

    <div>{{ $reviews->links() }}</div>

</div>

@endsection
