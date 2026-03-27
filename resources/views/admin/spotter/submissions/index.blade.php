@extends('layouts.admin')

@section('title', 'Spotter Submissions — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Submissions</h1>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Status</label>
            <select name="status" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach(\App\Enums\SpotterSubmissionStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">County</label>
            <input type="text" name="county" value="{{ request('county') }}"
                   class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm" placeholder="County">
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
                    <th class="px-4 py-3">Pharmacy</th>
                    <th class="px-4 py-3">Ward</th>
                    <th class="px-4 py-3">County</th>
                    <th class="px-4 py-3">Spotter</th>
                    <th class="px-4 py-3">Visit Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="text-gray-300">
                @forelse($submissions as $sub)
                    <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="px-4 py-3 font-medium text-white">{{ $sub->pharmacy }}</td>
                        <td class="px-4 py-3">{{ $sub->ward }}</td>
                        <td class="px-4 py-3">{{ $sub->county }}</td>
                        <td class="px-4 py-3">{{ $sub->spotter->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $sub->visit_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3"><span class="{{ $sub->status->badgeClass() }}">{{ $sub->status->label() }}</span></td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.spotter.submissions.show', $sub) }}" class="text-yellow-400 hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No submissions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $submissions->links() }}</div>

</div>

@endsection
