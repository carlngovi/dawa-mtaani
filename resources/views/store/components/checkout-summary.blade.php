{{-- Checkout Summary --}}
<div x-data="{ subtotal: '{{ $subtotal ?? '' }}', discount: '{{ $discount ?? '' }}', total: '{{ $total ?? '' }}', paying: false }" class="rounded-xl border bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

    <div class="space-y-3">
        {{-- Subtotal --}}
        <div class="flex items-center justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span x-text="subtotal"></span>
        </div>

        {{-- Discount (hidden when 0) --}}
        <div x-show="discount && discount !== '0'" class="flex items-center justify-between text-sm text-green-600">
            <span>Discount</span>
            <span>- <span x-text="discount"></span></span>
        </div>

        {{-- Divider --}}
        <hr class="border-gray-200">

        {{-- Total --}}
        <div class="flex items-center justify-between text-lg font-bold text-gray-900">
            <span>Total</span>
            <span x-text="total"></span>
        </div>
    </div>

    {{-- M-Pesa Pay Button --}}
    <button @click="paying = true; $dispatch('initiate-mpesa-payment')"
            :disabled="paying"
            class="mt-6 w-full rounded-lg bg-green-600 py-3 text-center font-semibold text-white hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
        <span x-show="!paying">Pay with M-Pesa</span>
        <span x-show="paying" class="flex items-center justify-center gap-2">
            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            Waiting for M-Pesa prompt...
        </span>
    </button>
</div>
