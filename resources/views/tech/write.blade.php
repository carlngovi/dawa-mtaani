@extends('layouts.app')
@section('title', 'Write Operations — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Write Operations</h1>
            <p class="text-sm text-gray-500 mt-1">Submit a request — super_admin approval required before execution</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
            Tier 0
        </span>
    </div>

    {{-- Warning --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        Write operations are NOT executed here. Submit a request below,
        then ask a super_admin to review at
        <a href="/super/t0-approvals" class="underline font-medium">/super/t0-approvals</a>.
        Operations are only executed after approval.
    </div>

    {{-- Pending approvals KPI --}}
    <a href="/super/t0-approvals"
       class="bg-white rounded-xl border border-{{ $pendingApprovals > 0 ? 'amber' : 'gray' }}-200 p-5 block hover:shadow-md transition-shadow">
        <p class="text-xs text-gray-400">Pending Approvals</p>
        <p class="text-3xl font-bold text-{{ $pendingApprovals > 0 ? 'amber-600' : 'gray-900' }} mt-1">
            {{ $pendingApprovals }}
        </p>
        <p class="text-xs text-green-700 mt-2 font-medium">View at /super/t0-approvals →</p>
    </a>

    {{-- Request form --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Submit Write Request</h3>
        </div>
        <form method="POST" action="/api/v1/tech/write-request" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Operation Type</label>
                <select name="operation_type" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select type...</option>
                    <option value="SCHEMA_CHANGE">Schema Change</option>
                    <option value="DATA_CORRECTION">Data Correction</option>
                    <option value="CONFIG_OVERRIDE">Config Override</option>
                    <option value="EMERGENCY_FIX">Emergency Fix</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" required
                          placeholder="Explain what needs to be changed and why..."
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Affected Tables</label>
                <input type="text" name="affected_tables"
                       placeholder="e.g. facilities, orders"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit"
                    class="px-6 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                Submit Request
            </button>
        </form>
    </div>

</div>
@endsection
