@extends('layouts.app')
@section('title', 'Activation Summary — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    @if(! $county)
    <div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-sm px-4 py-3 rounded-lg">
        No county assigned to your account. Contact super_admin to assign your county.
    </div>
    @else

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Activation Summary</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $county }} County</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Total</p>
            <p class="text-2xl font-semibold text-yellow-400 mt-1">{{ $counts->total ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Active</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $counts->active ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Pending</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $counts->pending ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Churned</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $counts->churned ?? 0 }}</p>
        </div>
    </div>

    {{-- Network split --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Network Members</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $counts->network_count ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-sm text-gray-400">Off-Network</p>
            <p class="text-2xl font-semibold text-white mt-1">{{ $counts->off_network_count ?? 0 }}</p>
        </div>
    </div>

    {{-- Activation rate --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-sm text-gray-400">Activation Rate</p>
                <p class="text-xs text-gray-500 mt-0.5">Active / Total facilities</p>
            </div>
            <p class="text-3xl font-bold text-yellow-400">{{ $activationRate }}%</p>
        </div>
        <div class="w-full bg-gray-700 rounded-full h-3">
            <div class="h-3 rounded-full transition-all
                {{ $activationRate >= 70 ? 'bg-green-500' : ($activationRate >= 40 ? 'bg-amber-400' : 'bg-red-500') }}"
                 style="width: {{ min($activationRate, 100) }}%">
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            {{ $counts->active ?? 0 }} of {{ $counts->total ?? 0 }} pharmacies are active
        </p>
    </div>

    {{-- By-ward table --}}
    @if($byWard->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Breakdown by Ward</h3>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Ward</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3 text-right">Active</th>
                    <th class="px-5 py-3 text-left">Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($byWard as $ward)
                @php
                    $rate = $ward->total > 0 ? round(($ward->active_count / $ward->total) * 100) : 0;
                    $barColor = $rate >= 70 ? 'bg-green-500' : ($rate >= 40 ? 'bg-amber-400' : 'bg-red-400');
                @endphp
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 font-medium text-gray-200">{{ $ward->ward }}</td>
                    <td class="px-5 py-3 text-right text-gray-400">{{ $ward->total }}</td>
                    <td class="px-5 py-3 text-right text-green-400 font-medium">{{ $ward->active_count }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-700 rounded-full h-2 max-w-[80px]">
                                <div class="h-2 rounded-full {{ $barColor }}"
                                     style="width: {{ $rate }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 w-8">{{ $rate }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
    @endif

    <p class="text-xs text-gray-400">
        Activation data reflects current status. No financial data is shown in this view.
    </p>

    @endif
</div>
@endsection
