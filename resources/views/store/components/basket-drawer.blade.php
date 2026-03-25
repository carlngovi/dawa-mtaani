{{-- Basket Drawer — slide-out panel --}}
<div x-data="{ open: false }" x-cloak>
    {{-- Toggle button --}}
    <button @click="open = true" class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-3 text-white shadow-lg hover:bg-indigo-700 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
        Basket (<span x-text="basketCount">0</span>)
    </button>

    {{-- Drawer overlay --}}
    <div x-show="open" @click="open = false" class="fixed inset-0 z-40 bg-black/30 transition-opacity"></div>

    {{-- Drawer panel --}}
    <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white shadow-xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="text-lg font-semibold text-gray-900">Your Basket</h2>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Basket items --}}
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
            <template x-for="item in basketItems" :key="item.product_ulid">
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900" x-text="item.generic_name"></p>
                        <p class="text-sm text-gray-500" x-text="item.brand_name + ' — ' + item.unit_size"></p>
                        <p class="text-sm font-semibold text-indigo-600" x-text="item.unit_price"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="updateQty(item, -1)" class="rounded bg-gray-100 px-2 py-1 text-sm hover:bg-gray-200">-</button>
                        <span class="w-8 text-center text-sm font-medium" x-text="item.quantity"></span>
                        <button @click="updateQty(item, 1)" class="rounded bg-gray-100 px-2 py-1 text-sm hover:bg-gray-200">+</button>
                        <button @click="removeItem(item)" class="ml-2 text-red-400 hover:text-red-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Promo code & subtotal --}}
        <div class="border-t px-4 py-4 space-y-3">
            <div class="flex gap-2">
                <input x-model="promoCode" type="text" placeholder="Promo code" class="flex-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <button @click="applyPromo" class="rounded-md bg-gray-100 px-3 py-2 text-sm font-medium hover:bg-gray-200">Apply</button>
            </div>
            <div class="flex items-center justify-between text-lg font-semibold">
                <span>Subtotal</span>
                <span x-text="subtotal"></span>
            </div>
            <button @click="checkout" class="w-full rounded-lg bg-indigo-600 py-3 text-center font-semibold text-white hover:bg-indigo-700 transition">
                Proceed to Checkout
            </button>
        </div>
    </div>
</div>
