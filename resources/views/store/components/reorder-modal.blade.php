{{-- Reorder Modal --}}
<div x-data="{ show: false, lines: [], loading: false }" x-cloak
     @show-reorder-modal.window="show = true; fetchLivePrices($event.detail.order_ulid)">

    <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center">
        {{-- Backdrop --}}
        <div @click="show = false" class="absolute inset-0 bg-black/40"></div>

        {{-- Modal --}}
        <div class="relative z-10 w-full max-w-lg rounded-xl bg-white shadow-2xl">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Reorder Previous Order</h3>
                <p class="text-sm text-gray-500">Prices shown are current live prices.</p>
            </div>

            {{-- Loading --}}
            <div x-show="loading" class="flex items-center justify-center py-12">
                <svg class="h-8 w-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>

            {{-- Order lines --}}
            <div x-show="!loading" class="max-h-80 overflow-y-auto px-6 py-4">
                <ul class="divide-y">
                    <template x-for="line in lines" :key="line.product_ulid">
                        <li class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-medium text-gray-900" x-text="line.generic_name"></p>
                                <p class="text-sm text-gray-500">Qty: <span x-text="line.quantity"></span></p>
                            </div>
                            <span class="font-semibold text-indigo-600" x-text="line.unit_price"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="flex justify-end gap-3 border-t px-6 py-4">
                <button @click="show = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="reorderAll(); show = false" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Reorder All
                </button>
            </div>
        </div>
    </div>
</div>
