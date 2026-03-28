@extends('layouts.app')
@section('title', 'Data & DPA — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-white">Data & DPA Compliance</h1><p class="text-sm text-gray-400 mt-1">Kenya Data Protection Act compliance centre</p></div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Pending Deletions</p><p class="text-2xl font-bold text-red-400">{{ number_format($stats['pending_deletions']) }}</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Pending Exports</p><p class="text-2xl font-bold text-amber-400">{{ number_format($stats['pending_exports']) }}</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Anonymisation Runs</p><p class="text-2xl font-bold text-blue-400">{{ number_format($stats['total_anonymised']) }}</p></div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5"><p class="text-xs text-gray-400 mb-1">Retention Policies</p><p class="text-2xl font-bold text-green-400">{{ number_format($stats['policies']) }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Deletion Requests</h3></div>
            <div class="overflow-x-auto"><table class="w-full text-sm min-w-[400px]"><thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-4 py-3 text-left">ULID</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($deletionRequests as $req)
                <tr class="hover:bg-gray-900"><td class="px-4 py-3 font-mono text-xs text-gray-400">{{ substr($req->ulid ?? '', -8) ?: $req->id }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $req->status === 'COMPLETED' ? 'bg-green-900/30 text-green-400 border border-gray-700' : ($req->status === 'PENDING' ? 'bg-amber-900/30 text-amber-400 border border-amber-800' : 'bg-gray-700 text-gray-400') }}">{{ $req->status }}</span></td><td class="px-4 py-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</td></tr>
                @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No deletion requests</td></tr>@endforelse
            </tbody></table></div>
            <div class="px-5 py-3 border-t border-gray-700">{{ $deletionRequests->links() }}</div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Export Requests (DSAR)</h3></div>
            <div class="overflow-x-auto"><table class="w-full text-sm min-w-[400px]"><thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-4 py-3 text-left">ULID</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($exportRequests as $req)
                <tr class="hover:bg-gray-900"><td class="px-4 py-3 font-mono text-xs text-gray-400">{{ substr($req->ulid ?? '', -8) ?: $req->id }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $req->status === 'READY' ? 'bg-green-900/30 text-green-400 border border-gray-700' : ($req->status === 'PENDING' ? 'bg-amber-900/30 text-amber-400 border border-amber-800' : 'bg-gray-700 text-gray-400') }}">{{ $req->status }}</span></td><td class="px-4 py-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</td></tr>
                @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No export requests</td></tr>@endforelse
            </tbody></table></div>
            <div class="px-5 py-3 border-t border-gray-700">{{ $exportRequests->links() }}</div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700"><h3 class="text-sm font-semibold text-gray-200">Data Retention Policies</h3></div>
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[500px]"><thead class="bg-gray-900/50 text-xs text-gray-400 uppercase"><tr><th class="px-5 py-3 text-left">Category</th><th class="px-5 py-3 text-left">Retention</th><th class="px-5 py-3 text-left">Action</th><th class="px-5 py-3 text-left">Status</th></tr></thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($retentionPolicies as $p)
            <tr class="hover:bg-gray-900"><td class="px-5 py-3 font-medium text-gray-200">{{ $p->data_category }}</td><td class="px-5 py-3 text-gray-400">{{ $p->retention_years }} years</td><td class="px-5 py-3 text-gray-400">{{ $p->action_on_expiry }}</td><td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $p->is_active ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-gray-700 text-gray-400' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
            @empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No policies</td></tr>@endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
