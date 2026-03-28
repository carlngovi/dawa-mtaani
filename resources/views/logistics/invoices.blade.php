@extends('layouts.app')
@section('title', 'Invoices — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ open: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Invoices</h1>
            <p class="text-sm text-gray-400 mt-1">Submit periodic invoices for delivery services</p>
        </div>
        <button @click="open = true"
                class="px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            + Submit New Invoice
        </button>
    </div>

    {{-- Note --}}
    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Invoices require admin matching before processing.
        Matched invoices are processed in the next settlement cycle.
    </div>

    @if($invoices->total() > 0)
    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Period</th>
                        <th class="px-5 py-3 text-right">Deliveries</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Reference</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 text-xs text-gray-400">
                            @if(isset($invoice->period_from) && isset($invoice->period_to))
                                {{ \Carbon\Carbon::parse($invoice->period_from)->format('d M') }}
                                –
                                {{ \Carbon\Carbon::parse($invoice->period_to)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-300">
                            {{ isset($invoice->total_deliveries) ? number_format($invoice->total_deliveries) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            @isset($invoice->total_amount)
                                {{ $currency['symbol'] }} {{ number_format($invoice->total_amount, $currency['decimal_places']) }}
                            @else
                                —
                            @endisset
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400 hidden md:table-cell">
                            {{ $invoice->reference_number ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($invoice->status ?? '') {
                                    'SUBMITTED'  => 'bg-amber-900/30 text-amber-400',
                                    'MATCHED'    => 'bg-blue-900/30 text-blue-400',
                                    'PROCESSED'  => 'bg-green-900/30 text-green-400',
                                    'DISPUTED'   => 'bg-red-900/30 text-red-400',
                                    default      => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $invoice->status ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            @isset($invoice->created_at)
                                {{ \Carbon\Carbon::parse($invoice->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
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
    <div>{{ $invoices->links() }}</div>

    @else
    {{-- Empty state --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No invoices submitted yet. Use the button above to submit your first invoice.</p>
    </div>
    @endif

    {{-- Submit Invoice modal --}}
    <div x-show="open"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-base font-semibold text-white">Submit New Invoice</h3>
            <form method="POST" action="/api/v1/logistics/invoices" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Period From</label>
                        <input type="date" name="period_from" required
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Period To</label>
                        <input type="date" name="period_to" required
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Total Deliveries</label>
                    <input type="number" name="total_deliveries" min="1" required
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Total Amount</label>
                    <input type="number" name="total_amount" step="0.01" min="0" required
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" required
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500">
                        Submit Invoice
                    </button>
                    <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
