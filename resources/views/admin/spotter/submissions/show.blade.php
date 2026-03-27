@extends('layouts.admin')

@section('title', $submission->pharmacy . ' — Submission')

@section('content')

<div class="space-y-6">

    {{-- Status timeline --}}
    @php
        $steps = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'held' => 'Held',
            'sr_reviewed' => 'SR Reviewed',
            'cc_verified' => 'CC Verified',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ];
        $current = $submission->status->value;
        $reached = false;
    @endphp
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
        <div class="flex items-center gap-1 overflow-x-auto">
            @foreach($steps as $val => $label)
                @php
                    $isCurrent = $val === $current;
                    if ($isCurrent) $reached = true;
                    $color = $isCurrent ? 'bg-yellow-400 text-gray-900' : ($reached ? 'bg-gray-700 text-gray-500' : 'bg-gray-700 text-gray-300');
                @endphp
                <span class="px-3 py-1 rounded text-xs font-medium whitespace-nowrap {{ $color }}">{{ $label }}</span>
                @if(!$loop->last)
                    <span class="text-gray-600">&rarr;</span>
                @endif
            @endforeach
        </div>
    </div>

    <h1 class="text-2xl font-bold text-white">{{ $submission->pharmacy }}</h1>
    <p class="text-sm text-gray-400">Submitted by {{ $submission->spotter->name ?? '—' }} on {{ $submission->visit_date?->format('d M Y') }}</p>

    {{-- Two-column detail --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: Location & Identity --}}
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Location & Identity</h2>
            @foreach([
                'County' => $submission->county,
                'Ward' => $submission->ward,
                'Town' => $submission->town,
                'Address' => $submission->address,
                'GPS' => $submission->lat . ', ' . $submission->lng,
                'GPS Accuracy' => $submission->gps_accuracy ? $submission->gps_accuracy . ' m' : '—',
                'Owner Name' => $submission->owner_name,
                'Owner Phone' => $submission->owner_phone,
                'Pharmacy Phone' => $submission->pharmacy_phone ?? '—',
                'Owner Email' => $submission->owner_email ?? '—',
                'Owner Present' => $submission->owner_present ? 'Yes' : 'No',
            ] as $label => $value)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">{{ $label }}</span>
                    <span class="text-white">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        {{-- Right: Engagement & Follow-up --}}
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Engagement & Follow-up</h2>
            @foreach([
                'Open Time' => $submission->open_time,
                'Close Time' => $submission->close_time,
                'Days/Week' => $submission->days_per_week,
                'Potential' => $submission->potential?->value ?? '—',
                'Follow Up' => $submission->follow_up ? 'Yes' : 'No',
                'Callback Time' => $submission->callback_time ?? '—',
                'Next Step' => $submission->next_step ?? '—',
                'Follow-up Date' => $submission->follow_up_date?->format('d M Y') ?? '—',
                'Brochure Left' => $submission->brochure ? 'Yes' : 'No',
                'Rep Notes' => $submission->rep_notes ?? '—',
            ] as $label => $value)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">{{ $label }}</span>
                    <span class="text-white">{{ $value }}</span>
                </div>
            @endforeach
        </div>

    </div>

    {{-- Confidential section --}}
    @if(auth()->user()->hasAnyRole(['admin', 'super_admin', 'sales_rep', 'county_coordinator']))
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 space-y-3">
            <div class="flex items-center gap-2 mb-2">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Confidential</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-400/20 text-red-400">Internal Only</span>
            </div>
            @foreach([
                'Foot Traffic' => $submission->foot_traffic ?? '—',
                'Stock Level' => $submission->stock_level ?? '—',
                'Notes' => $submission->notes ?? '—',
            ] as $label => $value)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">{{ $label }}</span>
                    <span class="text-white">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Photo --}}
    @if($submission->photo_path)
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Photo</h2>
            <img src="{{ Storage::url($submission->photo_path) }}" alt="Pharmacy photo" class="rounded-lg max-h-96 object-contain">
        </div>
    @endif

    {{-- Duplicate reviews --}}
    @if($submission->duplicateReviews->count())
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Duplicate Review History</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 text-left border-b border-gray-700">
                        <th class="pb-2">Tier</th>
                        <th class="pb-2">Reviewer</th>
                        <th class="pb-2">Decision</th>
                        <th class="pb-2">Match</th>
                        <th class="pb-2">GPS Dist</th>
                        <th class="pb-2">Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @foreach($submission->duplicateReviews as $rev)
                        <tr class="border-b border-gray-700/50">
                            <td class="py-2">{{ $rev->tier->label() }}</td>
                            <td class="py-2">{{ $rev->reviewer->name ?? 'Pending' }}</td>
                            <td class="py-2">{{ $rev->decision->value }}</td>
                            <td class="py-2">{{ $rev->match_name ?? '—' }}</td>
                            <td class="py-2">{{ $rev->gps_distance_metres ? $rev->gps_distance_metres . ' m' : '—' }}</td>
                            <td class="py-2">{{ $rev->reviewed_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>

@endsection
