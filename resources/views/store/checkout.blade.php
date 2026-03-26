@extends('layouts.app')
@section('title', 'Checkout — Dawa Mtaani')
@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{
         phone: '{{ $user->phone ?? '' }}',
         promoCode: '',
         paying: false,
         error: null,

         async pay() {
             this.error = null;
             if (!this.phone || this.phone.length < 10) {
                 this.error = 'Please enter a valid phone number.';
                 return;
             }
             this.paying = true;

             const csrf = document.querySelector('meta[name=&quot;csrf-token&quot;]').content;
             try {
                 const res = await fetch('/api/store/orders/checkout', {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': csrf,
                         'Content-Type': 'application/json',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         session_token: '{{ $basket->session_token }}',
                         patient_phone: this.phone,
                         patient_name: '{{ addslashes($user->name ?? '') }}',
                         promo_code: this.promoCode || null,
                     })
                 });
                 const json = await res.json();
                 if (json.status === 'success') {
                     window.location.href = '/store/orders/' + json.data.order_ulid + '?pending=true';
                 } else {
                     this.error = json.message || 'Checkout failed. Please try again.';
                     this.paying = false;
                 }
             } catch (e) {
                 this.error = 'Network error. Please check your connection and try again.';
                 this.paying = false;
             }
         }
     }">

    {{-- Header --}}
    <div>
        <a href="/store/{{ $facility->ulid }}" class="text-xs text-green-700 hover:underline mb-2 inline-block">← Back to {{ $facility->facility_name }}</a>
        <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $facility->facility_name }} — {{ $facility->county }}</p>
    </div>

    {{-- Error --}}
    <div x-show="error" x-cloak class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
        <span x-text="error"></span>
    </div>

    {{-- Order summary --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Order Summary</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Item</th>
                    <th class="px-5 py-3 text-right">Qty</th>
                    <th class="px-5 py-3 text-right">Price</th>
                    <th class="px-5 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($lines as $line)
                <tr>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $line->generic_name }}</p>
                        <p class="text-xs text-gray-400">{{ $line->brand_name }} — {{ $line->unit_size }}</p>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-700">{{ $line->quantity }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">
                        {{ $currency['symbol'] }} {{ number_format($line->unit_price, $currency['decimal_places']) }}
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $currency['symbol'] }} {{ number_format($line->line_total, $currency['decimal_places']) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100 space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span>{{ $currency['symbol'] }} {{ number_format($subtotal, $currency['decimal_places']) }}</span>
            </div>
            @if($platformFee > 0)
            <div class="flex justify-between text-sm text-gray-500">
                <span>Platform fee ({{ $platformFeePct }}%)</span>
                <span>{{ $currency['symbol'] }} {{ number_format($platformFee, $currency['decimal_places']) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-100">
                <span>Total</span>
                <span>{{ $currency['symbol'] }} {{ number_format($total, $currency['decimal_places']) }}</span>
            </div>
        </div>
    </div>

    {{-- Promo code --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <label class="block text-xs font-medium text-gray-700 mb-2">Promo Code (optional)</label>
        <div class="flex gap-2">
            <input type="text" x-model="promoCode" placeholder="Enter code"
                   class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <p class="text-xs text-gray-400 mt-1">Discount will be applied when you pay</p>
    </div>

    {{-- Payment --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <h3 class="text-sm font-semibold text-gray-700">Payment</h3>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">M-Pesa Phone Number</label>
            <input type="tel" x-model="phone"
                   placeholder="0712345678 or +254712345678"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <button @click="pay()"
                :disabled="paying"
                class="w-full py-3 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!paying">Pay {{ $currency['symbol'] }} {{ number_format($total, $currency['decimal_places']) }} via M-Pesa</span>
            <span x-show="paying" class="flex items-center justify-center gap-2">
                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Sending M-Pesa prompt...
            </span>
        </button>

        <p class="text-xs text-gray-500 text-center">
            You will receive an M-Pesa STK push on your phone. Enter your PIN to complete payment.
        </p>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs px-3 py-2 rounded-lg">
            Your items will be held for 15 minutes after payment is initiated.
        </div>
    </div>

</div>
@endsection