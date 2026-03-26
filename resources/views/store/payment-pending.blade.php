@extends('layouts.app')
@section('title', 'Waiting for Payment — Dawa Mtaani')
@section('content')
<div class="max-w-lg mx-auto space-y-6" x-data="paymentPoll('{{ $order->ulid }}')" x-init="start()">

    <div class="rounded-xl bg-gray-800 border border-gray-700 p-8 text-center space-y-5">

        <div class="w-20 h-20 border-2 rounded-full flex items-center justify-center mx-auto"
             :class="paid ? 'bg-green-900/50 border-green-500' : 'bg-green-900/30 border-green-700 animate-pulse'">
            <svg x-show="!paid" class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg x-show="paid" class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-xl font-semibold text-white" x-text="paid ? 'Payment Confirmed!' : 'Waiting for Payment'"></h1>
            <p class="text-gray-400 text-sm mt-2" x-text="paid ? 'Your order has been confirmed.' : 'Check your phone for the M-Pesa prompt. Enter your PIN to pay.'"></p>
        </div>

        <div class="bg-gray-900 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400">Order Reference</p>
            <p class="text-white font-mono text-sm mt-1">{{ strtoupper(substr($order->ulid, -12)) }}</p>
            <p class="text-yellow-400 font-mono text-sm mt-1">{{ $currency['symbol'] }} {{ number_format($order->total_amount, $currency['decimal_places']) }}</p>
        </div>

        <div x-show="!paid" class="text-gray-500 text-sm">
            Checking in <span class="text-yellow-400 font-mono" x-text="countdown">5</span>s
        </div>

        <div x-show="paid && receipt" class="bg-green-900/20 border border-green-800 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400">M-Pesa Receipt</p>
            <p class="text-green-400 font-mono text-sm mt-1" x-text="receipt"></p>
        </div>

        <div class="space-y-3">
            <div x-show="paid">
                <a href="/store/orders" class="block w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl text-center transition-colors">View My Orders →</a>
            </div>
            <div x-show="!paid">
                <button @click="checkNow()" :disabled="checking" class="w-full py-3 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
                    <span x-show="!checking">Check Payment Status</span>
                    <span x-show="checking">Checking...</span>
                </button>
            </div>
            <div x-show="!paid">
                <a href="/store/orders" class="block text-sm text-gray-500 hover:text-gray-400 text-center">View orders without waiting</a>
            </div>
        </div>
    </div>

    <div x-show="!paid" class="rounded-xl bg-gray-800 border border-gray-700 p-5 space-y-3">
        <h3 class="text-white font-medium text-sm">How to complete payment</h3>
        <ol class="space-y-2 text-sm text-gray-400">
            <li class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">1</span> Check your phone for the M-Pesa prompt</li>
            <li class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">2</span> Enter your M-Pesa PIN</li>
            <li class="flex items-start gap-2"><span class="w-5 h-5 rounded-full bg-yellow-400/20 text-yellow-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">3</span> This page updates automatically once payment is confirmed</li>
        </ol>
    </div>
</div>

<script>
function paymentPoll(ulid) {
    return {
        paid: false, checking: false, receipt: null, countdown: 5,
        pollTimeout: null, countdownInterval: null,

        start() { this.scheduleNext(); },
        scheduleNext() {
            this.countdown = 5;
            this.countdownInterval = setInterval(() => { this.countdown--; if (this.countdown <= 0) clearInterval(this.countdownInterval); }, 1000);
            this.pollTimeout = setTimeout(() => this.checkNow(), 5000);
        },
        async checkNow() {
            if (this.checking) return;
            this.checking = true;
            clearTimeout(this.pollTimeout);
            clearInterval(this.countdownInterval);
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const res = await fetch('/store/orders/' + ulid + '/check-payment', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                this.paid = data.paid === true;
                this.receipt = data.receipt || null;
                if (!this.paid) { this.scheduleNext(); }
            } catch(e) { this.scheduleNext(); }
            this.checking = false;
        }
    }
}
</script>
@endsection