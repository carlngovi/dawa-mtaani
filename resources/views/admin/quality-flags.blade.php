@extends('layouts.admin')
@section('title', 'Quality Flags — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Pharmacovigilance</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Open</p>
            <p class="text-2xl font-bold {{ $stats['open'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Under Review</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['under_review'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400">Confirmed</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['confirmed'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">Flag Type</th>
                    <th class="px-5 py-3 text-left">Batch Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($flags as $flag)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $flag->generic_name }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $flag->sku_code }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800">{{ $flag->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $flag->county }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $flag->flag_type === 'SUSPECTED_COUNTERFEIT' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ str_replace('_', ' ', $flag->flag_type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $flag->batch_reference ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ match($flag->status) {
                                'OPEN' => 'bg-amber-100 text-amber-700',
                                'CONFIRMED' => 'bg-red-100 text-red-700',
                                'DISMISSED' => 'bg-gray-100 text-gray-500',
                                default => 'bg-blue-100 text-blue-700'
                            } }}">
                            {{ $flag->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($flag->created_at)->format('d M Y') }}
                    </td>
                    <td class="px-5 py-3">
                        @if(in_array($flag->status, ['OPEN','UNDER_REVIEW']))
                            <a href="#" class="text-green-700 text-xs hover:underline">Review</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No quality flags</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $flags->links() }}</div>
    </div>
</div>
@endsection
