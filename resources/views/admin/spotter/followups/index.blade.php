@extends('layouts.admin')

@section('title', 'Spotter Follow-ups — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Follow-ups</h1>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Status</label>
            <select name="status" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach(\App\Enums\SpotterFollowUpStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst($s->value) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Spotter</label>
            <select name="spotter_id" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach($spotters as $s)
                    <option value="{{ $s->id }}" @selected(request('spotter_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
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
                    <th class="px-4 py-3">Pharmacy</th>
                    <th class="px-4 py-3">Ward</th>
                    <th class="px-4 py-3">Spotter</th>
                    <th class="px-4 py-3">Follow-up Date</th>
                    <th class="px-4 py-3">Days Overdue</th>
                    <th class="px-4 py-3">Next Step</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-300">
                @forelse($followUps as $fu)
                    @php
                        $daysOverdue = $fu->follow_up_date->isPast() ? (int) $fu->follow_up_date->diffInDays(now()) : 0;
                    @endphp
                    <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="px-4 py-3 font-medium text-white">{{ $fu->submission->pharmacy ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $fu->submission->ward ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $fu->spotter->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $fu->follow_up_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 {{ $daysOverdue > 0 ? 'text-red-400 font-bold' : '' }}">{{ $daysOverdue > 0 ? $daysOverdue : '—' }}</td>
                        <td class="px-4 py-3">{{ str_replace('_', ' ', $fu->next_step) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $fu->status->color() }}/20 text-{{ $fu->status->color() }}">
                                {{ ucfirst($fu->status->value) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No follow-ups found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $followUps->links() }}</div>

</div>

@endsection
