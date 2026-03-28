@extends('layouts.sales')
@section('title', 'Submissions — Sales Rep')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Submissions</h1>
    <form class="flex flex-wrap gap-3">
        <select name="status" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
            <option value="">All Statuses</option>
            @foreach(['submitted','held','sr_reviewed','cc_verified','accepted','rejected'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
        <input type="date" name="to" value="{{ request('to') }}" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:border-yellow-400 focus:outline-none">
        <button class="bg-yellow-400 text-white font-bold px-4 py-2 rounded-lg text-sm">Filter</button>
    </form>
    <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-700 text-gray-400 text-xs uppercase tracking-widest">
                <th class="px-4 py-3 text-left">Pharmacy</th><th class="px-4 py-3 text-left">Ward</th><th class="px-4 py-3 text-left">Spotter</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-right">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($submissions as $sub)
                <tr class="hover:bg-gray-700/50"><td class="px-4 py-3 text-white">{{ $sub->pharmacy }}</td><td class="px-4 py-3 text-gray-400">{{ $sub->ward }}</td><td class="px-4 py-3 text-gray-400">{{ $sub->spotter?->name ?? '—' }}</td><td class="px-4 py-3 text-gray-400">{{ $sub->visit_date }}</td>
                    <td class="px-4 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full {{ match($sub->status->value ?? $sub->status) { 'submitted' => 'bg-yellow-400/10 text-yellow-400', 'held' => 'bg-orange-400/10 text-orange-400', 'accepted' => 'bg-green-400/10 text-green-400', 'rejected' => 'bg-red-400/10 text-red-400', default => 'bg-gray-700 text-gray-400' } }}">{{ str_replace('_',' ',$sub->status->value ?? $sub->status) }}</span></td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('sales.submissions.show', $sub) }}" class="text-yellow-400 text-xs hover:underline">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No submissions</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $submissions->links() }}</div>
</div>
@endsection
