@extends('layouts.county')
@section('title', 'Leaderboard — County Coordinator')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Leaderboard — {{ $scopeLabel }}</h1>
    <div class="flex gap-2">
        @foreach(['week' => 'This Week', 'month' => 'This Month', 'programme' => 'All Time'] as $key => $label)
        <a href="{{ route('county.leaderboard.index', ['period' => $key]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $period === $key ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400' }}">{{ $label }}</a>
        @endforeach
    </div>
    @if($leaderboard->isNotEmpty())
    <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-700 text-gray-400 text-xs uppercase tracking-widest"><th class="px-4 py-3 text-center w-16">Rank</th><th class="px-4 py-3 text-left">Spotter</th><th class="px-4 py-3 text-center">Submissions</th><th class="px-4 py-3 text-center">Activations</th></tr></thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($leaderboard as $row)
                <tr class="{{ $loop->even ? 'bg-gray-900' : 'bg-gray-800' }}">
                    <td class="px-4 py-3 text-center font-bold {{ match($row['rank']) { 1 => 'text-yellow-400', 2 => 'text-gray-300', 3 => 'text-orange-400', default => 'text-gray-500' } }}">{{ $row['rank'] }}</td>
                    <td class="px-4 py-3 text-white font-medium">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-center {{ $row['submissions'] > 0 ? 'text-yellow-400' : 'text-gray-500' }}">{{ $row['submissions'] }}</td>
                    <td class="px-4 py-3 text-center text-lg font-bold {{ $row['activations'] > 0 ? 'text-green-400' : 'text-gray-500' }}">{{ $row['activations'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-8 text-center text-gray-500">No submissions for this period.</div>
    @endif
</div>
@endsection
