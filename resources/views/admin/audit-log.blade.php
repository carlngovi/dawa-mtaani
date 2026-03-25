@extends('layouts.admin')
@section('title', 'Audit Log — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Audit Log</h1>

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
            <input type="text" name="action" value="{{ request('action') }}"
                   placeholder="Filter by action..."
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <div></div>
            <button type="submit" class="px-4 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Action</th>
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">Model</th>
                    <th class="px-5 py-3 text-left">IP</th>
                    <th class="px-5 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $log->action }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $log->user_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $log->facility_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        @if($log->model_type)
                            {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No audit log entries</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
