{{-- Favourites Panel --}}
<div x-data="{ favourites: [] }" x-init="fetchFavourites()" class="rounded-xl border bg-gray-800 p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-white mb-4">Your Favourites</h3>

    <div x-show="favourites.length === 0" class="py-8 text-center text-sm text-gray-400">
        No favourites yet. Star a product to save it here.
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <template x-for="fav in favourites" :key="fav.product_ulid">
            <div class="flex flex-col rounded-lg border p-3 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-white line-clamp-2" x-text="fav.generic_name"></p>
                    <button @click="removeFavourite(fav)" class="ml-1 text-yellow-400 hover:text-yellow-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-400" x-text="fav.brand_name"></p>
                <p class="mt-auto pt-2 text-sm font-semibold text-yellow-600" x-text="fav.unit_price"></p>
                <button @click="addToBasket(fav)" class="mt-2 rounded-md bg-yellow-400 px-3 py-1.5 text-xs font-medium text-gray-900 hover:bg-yellow-500 transition">
                    Add to Basket
                </button>
            </div>
        </template>
    </div>
</div>
