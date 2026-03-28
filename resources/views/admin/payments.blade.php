@extends('layouts.admin')
@section('title', 'Payments — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Payments</h1>

    {{-- Co-pay alert banner --}}
    @if($copay['failed'] > 0 || $copay['escalated'] > 0)
    <div class="bg-red-900/20 border border-gray-700 text-red-300 text-sm px-4 py-3 rounded-lg">
        {{ $copay['failed'] }} co-pay payment(s) failed, {{ $copay['escalated'] }} escalated. Review required.
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-3xl font-bold text-{{ $stats['pending'] > 0 ? 'amber-400' : 'white' }} mt-1">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Needs processing</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">In Processing</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Manual Review</p>
            <p class="text-3xl font-bold text-{{ $stats['manual_review'] > 0 ? 'red-400' : 'white' }} mt-1">{{ $stats['manual_review'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Needs human action</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-green-400">Completed Today</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['completed_today'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <select name="status" class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['PENDING','PROCESSING','COMPLETED','FAILED','MANUAL_REVIEW'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', $s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 bg-yellow-400 text-white font-medium rounded-lg text-sm hover:bg-yellow-500">Filter</button>
        @if(request('status'))
            <a href="/admin/payments" class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">Clear</a>
        @endif
    </form>

    {{-- Payment Instructions table --}}
    @if($instructions instanceof \Illuminate\Pagination\LengthAwarePaginator && $instructions->total() > 0)
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Payment Instructions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Reference</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Sent</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Retries</th>
                        <th class="px-5 py-3 text-left">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($instructions as $pi)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $pi->ulid ? substr($pi->ulid, -8) : $pi->id }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($pi->instruction_amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($pi->status) {
                                    'PENDING'       => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                    'PROCESSING'    => 'bg-blue-900/30 text-blue-400 border border-gray-700',
                                    'COMPLETED'     => 'bg-green-900/30 text-green-400 border border-gray-700',
                                    'FAILED'        => 'bg-red-900/30 text-red-400 border border-gray-700',
                                    'MANUAL_REVIEW' => 'bg-orange-900/30 text-orange-400 border border-gray-700',
                                    default         => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $pi->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $pi->sent_at ? \Carbon\Carbon::parse($pi->sent_at)->timezone('Africa/Nairobi')->format('d M, H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $pi->retry_count ?? 0 }}</td>
                        <td class="px-5 py-3">
                            @if($pi->status === 'MANUAL_REVIEW')
                                <span class="text-xs text-orange-600 font-medium">Needs manual processing</span>
                            @elseif($pi->status === 'FAILED' && $pi->failure_reason)
                                <span class="text-xs text-red-500" title="{{ $pi->failure_reason }}">{{ \Illuminate\Support\Str::limit($pi->failure_reason, 30) }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $instructions->links() }}</div>

    @else
    {{-- Empty state --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center space-y-3">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <h3 class="text-sm font-semibold text-gray-300">No payment instructions yet</h3>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">
            Payment instructions will appear here once M-Pesa transactions are initiated.
            Configure your M-Pesa credentials to get started.
        </p>
    </div>
    @endif

    {{-- Recent Repayments --}}
    @if($repayments->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Recent Repayments</h3>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">M-Pesa Ref</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left hidden lg:table-cell">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($repayments as $rp)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3 text-gray-200">{{ $rp->facility_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-200">
                        {{ $currency['symbol'] }} {{ number_format($rp->amount_paid ?? 0, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400 hidden md:table-cell">{{ $rp->mpesa_reference ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @php
                            $rpBadge = match($rp->status ?? '') {
                                'PAID'    => 'bg-green-900/30 text-green-400 border border-gray-700',
                                'PENDING' => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                'OVERDUE' => 'bg-red-900/30 text-red-400 border border-gray-700',
                                default   => 'bg-gray-700 text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $rpBadge }}">{{ $rp->status ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                        {{ $rp->paid_at ? \Carbon\Carbon::parse($rp->paid_at)->timezone('Africa/Nairobi')->format('d M Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
    @endif

    {{-- M-Pesa Integration Status --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-300 mb-3">M-Pesa Integration Status</h3>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-400">Environment</dt>
                <dd>
                    @php $mpesaEnv = config('daraja.env', 'not set'); @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $mpesaEnv === 'production' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-amber-900/30 text-amber-400 border border-amber-800' }}">
                        {{ strtoupper($mpesaEnv) }}
                    </span>
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-400">Shortcode</dt>
                <dd class="text-gray-200">{{ config('daraja.shortcode') ? 'Configured' : 'Not set' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-400">API Credentials</dt>
                <dd class="text-gray-200">{{ config('daraja.consumer_key') ? 'Configured' : 'Not set' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-400">Callback URL</dt>
                <dd class="text-xs text-gray-400 truncate max-w-[200px]">
                    @if($isTechAdmin)
                        {{ config('daraja.callback_url', 'Not set') }}
                    @else
                        {{ config('daraja.callback_url') ? 'Configured' : 'Not set' }}
                    @endif
                </dd>
            </div>
        </dl>
    </div>

</div>
@endsection