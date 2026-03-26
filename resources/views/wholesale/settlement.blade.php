@extends('layouts.wholesale')
@section('title', 'Settlement — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settlement</h1>
        <p class="text-sm text-gray-500 mt-1">Weekly NILA settlement cycles — read-only view</p>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Settlement runs weekly every Friday. Funds are transferred to your registered
        I&amp;M Bank account within 2 business days of settlement confirmation.
    </div>

    @if($latestRecord)
    {{-- Latest cycle summary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-4">Latest Settlement</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400">Period</p>
                <p class="text-sm font-semibold text-gray-800 mt-1">
                    @if(isset($latestRecord->period_start) && isset($latestRecord->period_end))
                        {{ \Carbon\Carbon::parse($latestRecord->period_start)->format('d M') }}
                        –
                        {{ \Carbon\Carbon::parse($latestRecord->period_end)->format('d M Y') }}
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Gross Amount</p>
                <p class="text-sm font-semibold text-gray-800 mt-1">
                    @isset($latestRecord->gross_amount)
                        {{ $currency['symbol'] }} {{ number_format($latestRecord->gross_amount, $currency['decimal_places']) }}
                    @else
                        —
                    @endisset
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Net Payable</p>
                <p class="text-sm font-semibold text-green-700 mt-1">
                    @isset($latestRecord->net_amount)
                        {{ $currency['symbol'] }} {{ number_format($latestRecord->net_amount, $currency['decimal_places']) }}
                    @else
                        —
                    @endisset
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                @isset($latestRecord->status)
                    @php
                        $sc = match($latestRecord->status) {
                            'SETTLED'    => 'bg-green-100 text-green-700',
                            'PROCESSING' => 'bg-blue-100 text-blue-700',
                            'PENDING'    => 'bg-amber-100 text-amber-700',
                            default      => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">
                        {{ $latestRecord->status }}
                    </span>
                @endisset
            </div>
        </div>
    </div>
    @endif

    @if($records->total() > 0)
    {{-- Settlement history table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Settlement History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
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
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-gray-600">
                            @if(isset($record->period_start) && isset($record->period_end))
                                {{ \Carbon\Carbon::parse($record->period_start)->format('d M') }}
                                –
                                {{ \Carbon\Carbon::parse($record->period_end)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-700">
                            @isset($record->gross_amount)
                                {{ $currency['symbol'] }} {{ number_format($record->gross_amount, $currency['decimal_places']) }}
                            @else
                                —
                            @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 hidden md:table-cell">
                            @isset($record->deductions)
                                {{ $currency['symbol'] }} {{ number_format($record->deductions, $currency['decimal_places']) }}
                            @else
                                —
                            @endisset
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">
                            @isset($record->net_amount)
                                {{ $currency['symbol'] }} {{ number_format($record->net_amount, $currency['decimal_places']) }}
                            @else
                                —
                            @endisset
                        </td>
                        <td class="px-5 py-3">
                            @isset($record->status)
                                @php
                                    $sc = match($record->status) {
                                        'SETTLED'    => 'bg-green-100 text-green-700',
                                        'PROCESSING' => 'bg-blue-100 text-blue-700',
                                        'PENDING'    => 'bg-amber-100 text-amber-700',
                                        'DISPUTED'   => 'bg-red-100 text-red-700',
                                        default      => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">
                                    {{ $record->status }}
                                </span>
                            @endisset
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($record->settled_at)
                                {{ \Carbon\Carbon::parse($record->settled_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else
                                —
                            @endisset
                        </td>
                        <td class="px-5 py-3">
                            @if(isset($record->status) && $record->status === 'SETTLED')
                                <a href="/api/v1/settlements/{{ $record->id }}/export"
                                   class="text-green-700 text-xs hover:underline font-medium">
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
    {{-- Empty state --}}
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <p class="text-4xl mb-4">⏳</p>
        <h3 class="text-base font-semibold text-gray-800">No settlement cycles yet</h3>
        <p class="text-sm text-gray-400 mt-2 max-w-sm mx-auto">
            Your first settlement will appear here after your first confirmed delivery.
            Settlement runs automatically every Friday.
        </p>
    </div>
    @endif

</div>
@endsection