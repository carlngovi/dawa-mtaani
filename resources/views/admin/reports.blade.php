@extends('layouts.admin')
@section('title', 'Reports — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Reports & Exports</h1>
    </div>

    {{-- Export request form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Request New Export</h3>
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
                <select id="export_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="facility_performance">Facility Performance</option>
                    <option value="order_summary">Order Summary</option>
                    <option value="credit_health">Credit Health</option>
                </select>
                <input type="date" id="date_from" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <input type="date" id="date_to" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <button type="submit" class="px-4 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">
                    Request Export
                </button>
            </div>
        </form>
    </div>

    {{-- Export history --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Export History</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Requested By</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Rows</th>
                    <th class="px-5 py-3 text-left">Created</th>
                    <th class="px-5 py-3 text-left">Download</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($exports as $export)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-700">{{ str_replace('_',' ', ucfirst($export->export_type)) }}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $export->exported_by_name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($export->status) {
                                'READY'      => 'bg-green-100 text-green-700',
                                'GENERATING' => 'bg-blue-100 text-blue-700',
                                'FAILED'     => 'bg-red-100 text-red-700',
                                default      => 'bg-gray-100 text-gray-600'
                            } }}">{{ $export->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-500">{{ number_format($export->row_count) }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($export->created_at)->format('d M Y H:i') }}
                    </td>
                    <td class="px-5 py-3">
                        @if($export->status === 'READY' && $export->download_url)
                            <a href="{{ $export->download_url }}" target="_blank"
                               class="text-green-700 text-xs hover:underline">Download</a>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No exports yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $exports->links() }}</div>
    </div>
</div>
@endsection
