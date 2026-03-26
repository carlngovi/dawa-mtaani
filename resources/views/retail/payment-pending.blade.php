@extends('layouts.app')
@section('title', 'Waiting for Payment — Dawa Mtaani')
@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
<div class="w-full max-w-md space-y-4"
     x-data="paymentPoller('{{ $order->ulid }}', '/retail/orders/{{ $order->ulid }}/payment-status')"
     x-init="init()">

    <div class="rounded-xl bg-gray-800 border border-gray-700 p-8 text-center space-y-5">

        {{-- WAITING --}}
        <div x-show="!paid && !failed">
            <div class="w-20 h-20 rounded-full border-2 border-yellow-600 bg-yellow-900/20 flex items-center justify-center mx-auto animate-pulse">
                <svg class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white mt-4">Waiting for Payment</h1>
            <p class="text-gray-400 text-sm">Check your phone for the M-Pesa prompt. Enter your PIN to pay.</p>
        </div>

        {{-- SUCCESS --}}
        <div x-show="paid" x-cloak>
            <div class="w-20 h-20 rounded-full border-2 border-green-600 bg-green-900/20 flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white mt-4">Payment Confirmed!</h1>
            <p class="text-green-400 text-sm font-mono" x-show="receipt" x-text="'Receipt: ' + receipt"></p>
            <p class="text-gray-400 text-xs mt-1">Redirecting to your order...</p>
        </div>

        {{-- FAILED --}}
        <div x-show="failed" x-cloak>
            <div class="w-20 h-20 rounded-full border-2 border-red-700 bg-red-900/20 flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white mt-4">Payment Failed</h1>
            <p class="text-red-400 text-sm font-medium mt-2" x-text="failureReason || 'Payment was not completed'"></p>
            <div class="rounded-lg bg-red-900/20 border border-red-800 px-4 py-3 text-xs text-red-300 text-left mt-4">
                Your order was <strong>not confirmed</strong>. No funds were deducted. You can try again.
            </div>
        </div>

        {{-- Order ref --}}
        <div class="bg-gray-900 rounded-xl px-4 py-3 border border-gray-700">
            <p class="text-xs text-gray-400">Order Reference</p>
            <p class="text-white font-mono text-sm mt-0.5">{{ strtoupper(substr($order->ulid, -12)) }}</p>
        </div>

        {{-- Countdown --}}
        <p x-show="!paid && !failed && !errorMsg" class="text-gray-500 text-sm">
            Checking in <span class="text-yellow-400 font-mono" x-text="countdown">5</span>s
        </p>

        {{-- Error --}}
        <div x-show="errorMsg" class="rounded-lg bg-yellow-900/20 border border-yellow-800 px-3 py-2 text-xs text-yellow-300" x-text="errorMsg"></div>

        {{-- Buttons --}}
        <div class="space-y-2">
            <button x-show="!paid && !failed" @click="check()" :disabled="checking"
                    class="w-full py-3 bg-gray-700 hover:bg-gray-600 disabled:opacity-40 text-white text-sm font-medium rounded-xl transition-colors">
                <span x-show="!checking">Check Payment Status</span>
                <span x-show="checking">Checking...</span>
            </button>
            <a x-show="paid" x-cloak :href="redirectUrl || '/retail/orders'" class="block w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl text-center transition-colors">View Order →</a>
            <a x-show="failed" x-cloak href="/retail/basket" class="block w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl text-center transition-colors">Try Again →</a>
            <a x-show="!paid" href="/retail/orders" class="block text-xs text-gray-600 hover:text-gray-400 text-center transition-colors">View orders without waiting</a>
        </div>
    </div>

    {{-- Instructions --}}
    <div x-show="!paid && !failed" class="rounded-xl bg-gray-800 border border-gray-700 p-5">
        <h3 class="text-white font-medium text-sm mb-3">How to complete payment</h3>
        <ol class="space-y-2">
            <li class="flex items-start gap-3 text-sm"><span class="w-6 h-6 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">1</span><span class="text-gray-400">Check your phone for the M-Pesa prompt</span></li>
            <li class="flex items-start gap-3 text-sm"><span class="w-6 h-6 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">2</span><span class="text-gray-400">Enter your M-Pesa PIN</span></li>
            <li class="flex items-start gap-3 text-sm"><span class="w-6 h-6 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">3</span><span class="text-gray-400">This page updates automatically once confirmed</span></li>
        </ol>
    </div>
</div>
</div>

<script>
function paymentPoller(orderUlid, statusUrl) {
    return {
        paid: false, failed: false, checking: false,
        receipt: null, failureReason: null, redirectUrl: null,
        countdown: 5, pollTimer: null, countTimer: null,
        errorMsg: null, pollCount: 0, maxPolls: 24,

        init() { this.schedule(); },

        schedule() {
            if (this.pollCount >= this.maxPolls) {
                this.errorMsg = 'Check timed out. Please check your M-Pesa messages or refresh.';
                return;
            }
            this.countdown = 5;
            clearInterval(this.countTimer);
            this.countTimer = setInterval(() => { if (this.countdown > 0) this.countdown--; }, 1000);
            clearTimeout(this.pollTimer);
            this.pollTimer = setTimeout(() => this.check(), 5000);
        },

        async check() {
            if (this.checking || this.paid || this.failed) return;
            this.checking = true;
            this.errorMsg = null;
            this.pollCount++;
            try {
                const res = await fetch(statusUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.paid = data.paid || false;
                this.failed = data.failed || false;
                this.receipt = data.receipt || null;
                this.failureReason = data.failure_reason || null;
                this.redirectUrl = data.redirect_url || null;
                if (this.paid) {
                    setTimeout(() => { window.location.href = this.redirectUrl || '/retail/orders'; }, 2000);
                } else if (!this.failed) {
                    this.schedule();
                }
            } catch(e) {
                this.errorMsg = 'Connection error — retrying...';
                this.schedule();
            }
            this.checking = false;
        }
    }
}
</script>
@endsection
