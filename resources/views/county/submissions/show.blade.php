@extends('layouts.county')
@section('title', $submission->pharmacy . ' — County Coordinator')
@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('county.submissions.index') }}" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <h1 class="text-2xl font-bold text-white">{{ $submission->pharmacy }}</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 space-y-2">
            <p class="text-gray-400 text-xs uppercase tracking-widest">Location</p>
            <p class="text-white">{{ $submission->town }}, {{ $submission->ward }}</p>
            <p class="text-gray-400 text-sm">{{ $submission->county }} County</p>
            <p class="text-gray-400 text-sm">{{ $submission->address }}</p>
            @if($submission->lat)<p class="text-gray-500 text-xs font-mono">{{ $submission->lat }}, {{ $submission->lng }}</p>@endif
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 space-y-2">
            <p class="text-gray-400 text-xs uppercase tracking-widest">Owner</p>
            <p class="text-white">{{ $submission->owner_name }}</p>
            <p class="text-gray-400 text-sm">{{ $submission->owner_phone }}</p>
            @if($submission->owner_email)<p class="text-gray-400 text-sm">{{ $submission->owner_email }}</p>@endif
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 space-y-2">
            <p class="text-gray-400 text-xs uppercase tracking-widest">Assessment</p>
            <p class="text-gray-300 text-sm">Potential: <span class="{{ match($submission->potential?->value ?? '') { 'high' => 'text-green-400', 'low' => 'text-red-400', default => 'text-yellow-400' } }}">{{ $submission->potential?->value ?? '—' }}</span></p>
            <p class="text-gray-300 text-sm">Foot Traffic: {{ $submission->foot_traffic ?? '—' }}</p>
            <p class="text-gray-300 text-sm">Stock Level: {{ str_replace('_', ' ', $submission->stock_level ?? '—') }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 space-y-2">
            <p class="text-gray-400 text-xs uppercase tracking-widest">Status</p>
            <p class="text-white font-medium">{{ str_replace('_', ' ', $submission->status->value ?? $submission->status) }}</p>
            <p class="text-gray-400 text-sm">Visit: {{ $submission->visit_date }}</p>
            <p class="text-gray-400 text-sm">Spotter: {{ $submission->spotter?->name ?? '—' }}</p>
        </div>
    </div>
</div>
@endsection
