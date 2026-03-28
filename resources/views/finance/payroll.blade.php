@extends('layouts.app')
@section('title', 'Recruiter Payroll — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Recruiter Payroll</h1>
            <p class="text-sm text-gray-400 mt-1">Commission ledger entries</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
            Read Only
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Commissions are calculated by RecruiterCommissionService.
        Payment is processed by Network Admin. This is a read-only view.
    </div>

    {{-- Unpaid total --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <p class="text-xs text-gray-400">Unpaid Commissions</p>
        <p class="text-3xl font-bold text-{{ $unpaidTotal > 0 ? 'amber-400' : 'white' }} mt-1">
            {{ $currency['symbol'] }} {{ number_format($unpaidTotal, $currency['decimal_places']) }}
        </p>
    </div>

    @if($commissions->total() > 0)
    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Agent</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Firm</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($commissions as $entry)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $entry->agent_name }}</td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">{{ $entry->firm_name }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($entry->amount_kes, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($entry->entry_type ?? '') {
                                    'COMMISSION' => 'bg-green-900/30 text-green-400 border border-gray-700',
                                    'BONUS'      => 'bg-blue-900/30 text-blue-400 border border-gray-700',
                                    'CLAWBACK'   => 'bg-red-900/30 text-red-400 border border-gray-700',
                                    default      => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $entry->entry_type ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($entry->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $commissions->links() }}</div>

    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No commission entries yet.</p>
    </div>
    @endif

</div>
@endsection