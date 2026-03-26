@extends('layouts.app')
@section('title', 'Settlement — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Settlement</h1>
            <p class="text-sm text-gray-400 mt-1">Weekly settlement cycles</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Month summary --}}
    @if($summary && $summary->cycle_count > 0)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Cycles This Month</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $summary->cycle_count }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Gross Total</p>
            <p class="text-3xl font-bold text-white mt-1">
                {{ $currency['symbol'] }} {{ number_format($summary->monthly_gross ?? 0, $currency['decimal_places']) }}
            </p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-green-800 p-5">
            <p class="text-xs text-green-400">Net Total</p>
            <p class="text-3xl font-bold text-green-400 mt-1">
                {{ $currency['symbol'] }} {{ number_format($summary->monthly_net ?? 0, $currency['decimal_places']) }}
            </p>
        </div>
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['PENDING','PROCESSING','SETTLED','DISPUTED'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
            Filter
        </button>
        @if(request('status'))
            <a href="/finance/settlement"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    @if($records->total() > 0)
    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Period</th>
                        <th class="px-5 py-3 text-right">Gross</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Deductions</th>
                        <th class="px-5 py-3 text-right">Net</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Settled At</th>
                        <th class="px-5 py-3 text-left">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($records as $record)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 text-xs text-gray-400">
                            @if(isset($record->period_start) && isset($record->period_end))
                                {{ \Carbon\Carbon::parse($record->period_start)->format('d M') }}
                                –
                                {{ \Carbon\Carbon::parse($record->period_end)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-300">
                            @isset($record->gross_amount)
                                {{ $currency['symbol'] }} {{ number_format($record->gross_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400 hidden md:table-cell">
                            @isset($record->deductions)
                                {{ $currency['symbol'] }} {{ number_format($record->deductions, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            @isset($record->net_amount)
                                {{ $currency['symbol'] }} {{ number_format($record->net_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3">
                            @isset($record->status)
                            @php
                                $badge = match($record->status) {
                                    'SETTLED'    => 'bg-green-900/30 text-green-400',
                                    'PROCESSING' => 'bg-blue-900/30 text-blue-400',
                                    'PENDING'    => 'bg-amber-900/30 text-amber-400',
                                    'DISPUTED'   => 'bg-red-900/30 text-red-400',
                                    default      => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $record->status }}
                            </span>
                            @endisset
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($record->settled_at)
                                {{ \Carbon\Carbon::parse($record->settled_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3">
                            @if(isset($record->status) && $record->status === 'SETTLED')
                                <a href="/api/v1/settlements/{{ $record->id }}/export"
                                   class="text-green-400 text-xs hover:underline font-medium">
                                    CSV ↓
                                </a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $records->links() }}</div>

    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">
            No settlement records yet. Automated settlement activates in Phase 2.
        </p>
    </div>
    @endif

</div>
@endsection