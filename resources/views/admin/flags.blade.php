@extends('layouts.admin')
@section('title', 'Facility Flags — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Facility Flags</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Open Flags</p>
            <p class="text-2xl font-bold {{ $stats['open'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                {{ $stats['open'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Resolved</p>
            <p class="text-2xl font-bold text-gray-500">{{ $stats['resolved'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">Reason</th>
                    <th class="px-5 py-3 text-left">Flagged By</th>
                    <th class="px-5 py-3 text-left">Notes</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($flags as $flag)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $flag->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $flag->county }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                            {{ str_replace('_', ' ', $flag->reason) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $flag->flagged_by_name }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $flag->notes ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($flag->created_at)->format('d M Y') }}
                    </td>
                    <td class="px-5 py-3">
                        <button onclick="resolveFlag({{ $flag->id }})"
                                class="text-xs text-green-700 hover:underline">Resolve</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No open flags</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $flags->links() }}</div>
    </div>
</div>

<script>
function resolveFlag(id) {
    if (!confirm('Mark this flag as resolved?')) return;
    fetch('/api/v1/network/flags/' + id + '/resolve', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: 'Resolved via admin panel' })
    }).then(() => location.reload());
}
</script>
@endsection
