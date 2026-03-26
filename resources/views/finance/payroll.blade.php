@extends('layouts.app')
@section('title', 'Recruiter Payroll — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recruiter Payroll</h1>
            <p class="text-sm text-gray-500 mt-1">Commission ledger entries</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
            Read Only
        </span>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Commissions are calculated by RecruiterCommissionService.
        Payment is processed by Network Admin. This is a read-only view.
    </div>

    {{-- Unpaid total --}}
    <div class="bg-white rounded-xl border border-{{ $unpaidTotal > 0 ? 'amber' : 'gray' }}-200 p-5">
        <p class="text-xs text-gray-400">Unpaid Commissions</p>
        <p class="text-3xl font-bold text-{{ $unpaidTotal > 0 ? 'amber-600' : 'gray-900' }} mt-1">
            {{ $currency['symbol'] }} {{ number_format($unpaidTotal, $currency['decimal_places']) }}
        </p>
    </div>

    @if($commissions->total() > 0)
    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Agent</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Firm</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($commissions as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $entry->agent_name }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $entry->firm_name }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">
                            {{ $currency['symbol'] }} {{ number_format($entry->amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($entry->status ?? '') {
                                    'UNPAID'     => 'bg-amber-100 text-amber-700',
                                    'PROCESSING' => 'bg-blue-100 text-blue-700',
                                    'PAID'       => 'bg-green-100 text-green-700',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $entry->status ?? '—' }}
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
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No commission entries yet.</p>
    </div>
    @endif

</div>
@endsection