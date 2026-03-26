@extends('layouts.app')
@section('title', 'NILA Sweeps — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">NILA Weekly Sweeps</h1>
            <p class="text-sm text-gray-400 mt-1">Weekly atomic settlement with NILA Pharmaceuticals</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Weekly atomic settlement with NILA Pharmaceuticals. Processed every Friday.
    </div>

    {{-- Month total --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <p class="text-xs text-gray-400">Net Settled This Month</p>
        <p class="text-3xl font-bold text-white mt-1">
            {{ $currency['symbol'] }} {{ number_format($monthTotal, $currency['decimal_places']) }}
        </p>
    </div>

    @if($sweeps->total() > 0)
    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Sweep Date</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Reference</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Gross</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Fees</th>
                        <th class="px-5 py-3 text-right">Net</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($sweeps as $sweep)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 text-gray-300">
                            @isset($sweep->sweep_date)
                                {{ \Carbon\Carbon::parse($sweep->sweep_date)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400 hidden md:table-cell">
                            {{ $sweep->reference ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400 hidden md:table-cell">
                            @isset($sweep->gross_amount)
                                {{ $currency['symbol'] }} {{ number_format($sweep->gross_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-gray-400 hidden md:table-cell">
                            @isset($sweep->fees)
                                {{ $currency['symbol'] }} {{ number_format($sweep->fees, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            @isset($sweep->net_amount)
                                {{ $currency['symbol'] }} {{ number_format($sweep->net_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3">
                            @isset($sweep->status)
                            @php
                                $badge = match($sweep->status) {
                                    'PROCESSED' => 'bg-green-900/30 text-green-400',
                                    'PENDING'   => 'bg-amber-900/30 text-amber-400',
                                    'FAILED'    => 'bg-red-900/30 text-red-400',
                                    default     => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $sweep->status }}
                            </span>
                            @endisset
                        </td>
                        <td class="px-5 py-3">
                            @if(isset($sweep->status) && $sweep->status === 'PROCESSED')
                                <a href="/api/v1/sweeps/{{ $sweep->id }}/export"
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
    <div>{{ $sweeps->links() }}</div>

    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">
            Automated NILA sweeps activate in Phase 2. Manual settlement records will appear here.
        </p>
    </div>
    @endif

</div>
@endsection