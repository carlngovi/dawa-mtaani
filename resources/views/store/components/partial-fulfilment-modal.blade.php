{{-- Partial Fulfilment Modal --}}
<div x-data="{ show: false, items: [] }" x-cloak
     @show-partial-modal.window="show = true; items = $event.detail.unavailable_items">

    <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center">
        {{-- Backdrop --}}
        <div @click="show = false" class="absolute inset-0 bg-black/40"></div>

        {{-- Modal --}}
        <div class="relative z-10 w-full max-w-lg rounded-xl bg-gray-800 p-6 shadow-2xl">
            <h3 class="text-lg font-semibold text-white">Some Items Unavailable</h3>
            <p class="mt-1 text-sm text-gray-400">The following items cannot be fully fulfilled:</p>

            <ul class="mt-4 divide-y">
                <template x-for="item in items" :key="item.product_name">
                    <li class="flex items-center justify-between py-3">
                        <span class="font-medium text-gray-200" x-text="item.product_name"></span>
                        <span class="text-sm text-gray-400">
                            Requested: <span class="font-semibold" x-text="item.requested_qty"></span>
                            &middot; Available: <span class="font-semibold text-red-400" x-text="item.available_qty"></span>
                        </span>
                    </li>
                </template>
            </ul>

            <div class="mt-6 flex justify-end gap-3">
                <button @click="show = false" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-900">
                    Cancel
                </button>
                <button @click="show = false; $dispatch('proceed-partial')" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-500">
                    Proceed Anyway
                </button>
            </div>
        </div>
    </div>
</div>
