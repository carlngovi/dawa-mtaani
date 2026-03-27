@extends('layouts.admin')

@section('title', 'Spotter Attendance — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Attendance</h1>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Spotter</label>
            <select name="spotter_id" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach($spotters as $s)
                    <option value="{{ $s->id }}" @selected(request('spotter_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-yellow-400/20 text-yellow-400 rounded-lg text-sm font-medium hover:bg-yellow-400/30 transition">
            Filter
        </button>
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-gray-400 text-left">
                    <th class="px-4 py-3">Spotter</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Clock In</th>
                    <th class="px-4 py-3">Clock In GPS</th>
                    <th class="px-4 py-3">Clock Out</th>
                    <th class="px-4 py-3">Clock Out GPS</th>
                    <th class="px-4 py-3">Hours</th>
                    <th class="px-4 py-3">Auto-closed</th>
                </tr>
            </thead>
            <tbody class="text-gray-300">
                @forelse($attendances as $att)
                    <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="px-4 py-3">{{ $att->spotter->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $att->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $att->clock_in_at->format('H:i') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $att->clock_in_lat }}, {{ $att->clock_in_lng }}</td>
                        <td class="px-4 py-3">{{ $att->clock_out_at?->format('H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $att->clock_out_lat ? $att->clock_out_lat . ', ' . $att->clock_out_lng : '—' }}</td>
                        <td class="px-4 py-3">{{ $att->totalHours() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($att->auto_closed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-400/20 text-red-400">Yes</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $attendances->links() }}</div>

</div>

@endsection
