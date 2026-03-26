@extends('layouts.admin')
@section('title', 'Reports — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Reports & Exports</h1>
    </div>

    {{-- Export request form --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-300 mb-4">Request New Export</h3>
        <form method="POST" action="/api/v1/network/reports/export"
              x-data="{}" @submit.prevent="
                fetch('/api/v1/network/reports/export', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify({
                        export_type: document.getElementById('export_type').value,
                        date_from: document.getElementById('date_from').value,
                        date_to: document.getElementById('date_to').value,
                    })
                }).then(r => r.json()).then(d => { alert(d.message); location.reload(); })
              ">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                <select id="export_type" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                    <option value="facility_performance">Facility Performance</option>
                    <option value="order_summary">Order Summary</option>
                    <option value="credit_health">Credit Health</option>
                </select>
                <input type="date" id="date_from" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <input type="date" id="date_to" class="px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                <button type="submit" class="px-4 py-2 bg-yellow-400 text-gray-900 text-sm rounded-lg hover:bg-yellow-500">
                    Request Export
                </button>
            </div>
        </form>
    </div>

    {{-- Export history --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Export History</h3>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Requested By</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Rows</th>
                    <th class="px-5 py-3 text-left">Created</th>
                    <th class="px-5 py-3 text-left">Download</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($exports as $export)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 text-gray-300">{{ str_replace('_',' ', ucfirst($export->export_type)) }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $export->exported_by_name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($export->status) {
                                'READY'      => 'bg-green-900/30 text-green-400 border border-green-800',
                                'GENERATING' => 'bg-blue-900/30 text-blue-400 border border-blue-800',
                                'FAILED'     => 'bg-red-900/30 text-red-400 border border-red-800',
                                default      => 'bg-gray-700 text-gray-400'
                            } }}">{{ $export->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-400">{{ number_format($export->row_count) }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($export->created_at)->format('d M Y H:i') }}
                    </td>
                    <td class="px-5 py-3">
                        @if($export->status === 'READY' && $export->download_url)
                            <a href="{{ $export->download_url }}" target="_blank"
                               class="text-green-400 text-xs hover:underline">Download</a>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No exports yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $exports->links() }}</div>
    </div>
</div>
@endsection
