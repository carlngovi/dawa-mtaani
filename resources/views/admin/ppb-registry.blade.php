@extends('layouts.admin')
@section('title', 'PPB Registry — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">PPB Registry</h1>

    @if($isStale)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
            ⚠ Registry data is stale. Please upload a fresh PPB CSV export.
        </div>
    @endif

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Cached Records</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($cacheCount) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Last Upload</p>
            <p class="text-lg font-semibold text-gray-800 mt-1">
                {{ $lastUpload ? \Carbon\Carbon::parse($lastUpload->uploaded_at)->format('d M Y') : 'Never' }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400">Last Batch Size</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
                {{ $lastUpload ? number_format($lastUpload->row_count) : '—' }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Upload New Registry CSV</h3>
        <form action="/api/v1/admin/ppb-registry/upload" method="POST" enctype="multipart/form-data"
              class="flex items-center gap-3">
            @csrf
            <input type="file" name="file" accept=".csv,.txt"
                   class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4
                          file:rounded-lg file:border-0 file:text-sm file:font-medium
                          file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            <button type="submit"
                    class="px-5 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">
                Upload
            </button>
        </form>
        <p class="text-xs text-gray-400 mt-2">
            CSV format: licence_number, facility_name, ppb_type, licence_status, registered_address, licence_expiry_date
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Upload History</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">File</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Rows</th>
                    <th class="px-5 py-3 text-right">Inserted</th>
                    <th class="px-5 py-3 text-right">Updated</th>
                    <th class="px-5 py-3 text-right">Rejected</th>
                    <th class="px-5 py-3 text-left">Uploaded</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentUploads as $upload)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-xs text-gray-600">{{ $upload->file_name }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($upload->status) {
                                'COMPLETED'  => 'bg-green-100 text-green-700',
                                'PROCESSING' => 'bg-blue-100 text-blue-700',
                                'FAILED'     => 'bg-red-100 text-red-700',
                                default      => 'bg-gray-100 text-gray-600'
                            } }}">{{ $upload->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($upload->row_count) }}</td>
                    <td class="px-5 py-3 text-right text-green-600">{{ number_format($upload->rows_inserted) }}</td>
                    <td class="px-5 py-3 text-right text-blue-600">{{ number_format($upload->rows_updated) }}</td>
                    <td class="px-5 py-3 text-right text-red-500">{{ number_format($upload->rows_rejected) }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($upload->uploaded_at)->format('d M Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No uploads yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
