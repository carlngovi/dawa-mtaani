@extends('layouts.app')
@section('title', 'NILA Sweeps — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">NILA Weekly Sweeps</h1>
            <p class="text-sm text-gray-500 mt-1">Weekly atomic settlement with NILA Pharmaceuticals</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Weekly atomic settlement with NILA Pharmaceuticals. Processed every Friday.
    </div>

    {{-- Month total --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-400">Net Settled This Month</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            {{ $currency['symbol'] }} {{ number_format($monthTotal, $currency['decimal_places']) }}
        </p>
    </div>

    @if($sweeps->total() > 0)
    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
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
                <tbody class="divide-y divide-gray-100">
                    @foreach($sweeps as $sweep)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-700">
                            @isset($sweep->sweep_date)
                                {{ \Carbon\Carbon::parse($sweep->sweep_date)->timezone('Africa/Nairobi')->format('d M Y') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-500 hidden md:table-cell">
                            {{ $sweep->reference ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-600 hidden md:table-cell">
                            @isset($sweep->gross_amount)
                                {{ $currency['symbol'] }} {{ number_format($sweep->gross_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 hidden md:table-cell">
                            @isset($sweep->fees)
                                {{ $currency['symbol'] }} {{ number_format($sweep->fees, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">
                            @isset($sweep->net_amount)
                                {{ $currency['symbol'] }} {{ number_format($sweep->net_amount, $currency['decimal_places']) }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3">
                            @isset($sweep->status)
                            @php
                                $badge = match($sweep->status) {
                                    'PROCESSED' => 'bg-green-100 text-green-700',
                                    'PENDING'   => 'bg-amber-100 text-amber-700',
                                    'FAILED'    => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
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
    <div>{{ $sweeps->links() }}</div>

    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">
            Automated NILA sweeps activate in Phase 2. Manual settlement records will appear here.
        </p>
    </div>
    @endif

</div>
@endsection