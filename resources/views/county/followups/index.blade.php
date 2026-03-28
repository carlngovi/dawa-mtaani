@extends('layouts.county')
@section('title', 'Follow-ups — County Coordinator')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">County Follow-ups</h1>
    <form class="flex flex-wrap gap-3">
        <select name="status" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
            <option value="">All</option>
            @foreach(['open','overdue','completed'] as $s)<option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="bg-yellow-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm">Filter</button>
    </form>
    <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-700 text-gray-400 text-xs uppercase tracking-widest">
                <th class="px-4 py-3 text-left">Pharmacy</th><th class="px-4 py-3 text-left">Ward</th><th class="px-4 py-3 text-left">Spotter</th><th class="px-4 py-3 text-left">Due Date</th><th class="px-4 py-3 text-center">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($followups as $f)
                <tr><td class="px-4 py-3 text-white">{{ $f->submission?->pharmacy ?? '—' }}</td><td class="px-4 py-3 text-gray-400">{{ $f->submission?->ward ?? '—' }}</td><td class="px-4 py-3 text-gray-400">{{ $f->spotter?->name ?? '—' }}</td><td class="px-4 py-3 {{ ($f->status->value ?? $f->status) === 'overdue' ? 'text-red-400' : 'text-gray-400' }}">{{ $f->follow_up_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-4 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full {{ match($f->status->value ?? $f->status) { 'overdue' => 'bg-red-400/10 text-red-400', 'completed' => 'bg-green-400/10 text-green-400', default => 'bg-yellow-400/10 text-yellow-400' } }}">{{ $f->status->value ?? $f->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No follow-ups</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $followups->links() }}</div>
</div>
@endsection
