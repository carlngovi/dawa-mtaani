@extends('layouts.county')
@section('title', 'Attendance — County Coordinator')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">County Attendance</h1>
    <form class="flex flex-wrap gap-3">
        <select name="spotter_id" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
            <option value="">All Spotters</option>
            @foreach($spotterUsers as $id => $name)<option value="{{ $id }}" {{ request('spotter_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
        <input type="date" name="to" value="{{ request('to') }}" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
        <button class="bg-yellow-400 text-white font-bold px-4 py-2 rounded-lg text-sm">Filter</button>
    </form>
    <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-700 text-gray-400 text-xs uppercase tracking-widest">
                <th class="px-4 py-3 text-left">Spotter</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Clock In</th><th class="px-4 py-3 text-left">Clock Out</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($attendance as $a)
                <tr><td class="px-4 py-3 text-white">{{ $a->spotter?->name ?? '—' }}</td><td class="px-4 py-3 text-gray-400">{{ $a->date }}</td><td class="px-4 py-3 text-gray-300">{{ $a->clock_in_at?->format('H:i') ?? '—' }}</td><td class="px-4 py-3 text-gray-300">{{ $a->clock_out_at?->format('H:i') ?? '—' }}</td></tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No attendance records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $attendance->links() }}</div>
</div>
@endsection
