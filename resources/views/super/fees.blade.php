@extends('layouts.app')
@section('title', 'Platform Fees — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Platform Fees</h1>
            <p class="text-sm text-gray-400 mt-1">Fee configuration for platform operations</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
            Tier 1
        </span>
    </div>

    @if($fees->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Fee Type</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Description</th>
                        <th class="px-5 py-3 text-right">Value</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Unit</th>
                        <th class="px-5 py-3 text-left">Active</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($fees as $fee)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 font-medium text-gray-200">
                            {{ str_replace('_', ' ', $fee->fee_type ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $fee->description ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $fee->value ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $fee->unit ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            @if(isset($fee->is_active))
                                @if($fee->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">Inactive</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($fee->updated_at)
                                {{ \Carbon\Carbon::parse($fee->updated_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else
                                —
                            @endisset
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">
            No fee configuration entries. Contact technical admin to seed the platform_fee_config table.
        </p>
    </div>
    @endif

    <p class="text-xs text-gray-400">
        Fee changes apply to new transactions only. Historical orders retain original fee calculations.
    </p>

</div>
@endsection
