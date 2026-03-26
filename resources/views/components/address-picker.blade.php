@props([
    'placeholder' => 'Search for your delivery location...',
])

<div x-data="addressPicker()" x-init="init()">

    <div class="relative">
        <input type="text" x-model="searchText" x-ref="searchInput"
               @input.debounce.300ms="search(); $dispatch('address-typed', { address: searchText })"
               @focus="showDropdown = predictions.length > 0"
               @keydown.escape="showDropdown = false"
               @keydown.arrow-down.prevent="highlighted = Math.min(highlighted + 1, predictions.length - 1)"
               @keydown.arrow-up.prevent="highlighted = Math.max(highlighted - 1, 0)"
               @keydown.enter.prevent="if (highlighted >= 0 && predictions[highlighted]) selectPrediction(predictions[highlighted])"
               placeholder="{{ $placeholder }}" autocomplete="off"
               class="w-full bg-gray-900 border border-gray-600 text-white rounded-xl px-4 py-3 pr-10 text-sm placeholder-gray-500 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition-colors">
        <div class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg x-show="!loading" class="w-4 h-4" :class="selectedPlace ? 'text-yellow-400' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <svg x-show="loading" class="w-4 h-4 text-yellow-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Predictions dropdown --}}
        <div x-show="showDropdown && predictions.length > 0" @click.outside="showDropdown = false"
             x-transition class="absolute top-full left-0 right-0 mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl z-50 overflow-hidden max-h-64 overflow-y-auto" style="display:none">
            <template x-for="(pred, idx) in predictions" :key="pred.place_id">
                <button type="button" @click="selectPrediction(pred)"
                        :class="highlighted === idx ? 'bg-yellow-400/10 border-l-2 border-yellow-400' : 'hover:bg-gray-700'"
                        class="w-full text-left px-4 py-3 border-b border-gray-700 last:border-0 transition-colors">
                    <p class="text-white text-sm truncate" x-text="pred.structured_formatting?.main_text || pred.description"></p>
                    <p class="text-gray-400 text-xs truncate mt-0.5" x-text="pred.structured_formatting?.secondary_text || ''"></p>
                </button>
            </template>
        </div>
    </div>

    {{-- Confirmed badge + map --}}
    <div x-show="selectedPlace" x-transition class="mt-3 space-y-2" style="display:none">
        <div class="flex items-start gap-2 bg-green-900/20 border border-green-800 rounded-lg px-3 py-2">
            <svg class="w-4 h-4 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-gray-300 text-xs flex-1" x-text="selectedAddress"></p>
            <button type="button" @click="clearSelection()" class="text-gray-500 hover:text-red-400 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="rounded-xl overflow-hidden border border-gray-700" style="height: 220px;">
            <div x-ref="mapEl" class="w-full h-full"></div>
        </div>
        <p class="text-xs text-gray-500 text-center">Drag the pin to adjust exact location</p>
    </div>

    <p x-show="!selectedPlace && acService" class="text-xs text-gray-500 mt-1">Start typing to see address suggestions</p>
    <p x-show="!selectedPlace && !acService" class="text-xs text-amber-400/70 mt-1">Type your full delivery address (Google Maps autocomplete unavailable)</p>
</div>

<script>
function addressPicker() {
    return {
        searchText: '', predictions: [], highlighted: -1, showDropdown: false, loading: false,
        selectedPlace: null, selectedAddress: '', selectedLat: '', selectedLng: '', selectedPlaceId: '',
        map: null, marker: null, acService: null,

        init() {
            if (typeof google !== 'undefined' && google.maps?.places) this.acService = new google.maps.places.AutocompleteService();
            else window.addEventListener('google-maps-loaded', () => { this.acService = new google.maps.places.AutocompleteService(); });
        },

        search() {
            const q = this.searchText.trim();
            if (q.length < 2 || !this.acService) { this.predictions = []; this.showDropdown = false; return; }
            this.loading = true;
            this.acService.getPlacePredictions(
                { input: q, componentRestrictions: { country: 'ke' }, types: ['geocode', 'establishment'] },
                (preds, status) => {
                    this.loading = false;
                    if (status === google.maps.places.PlacesServiceStatus.OK && preds) { this.predictions = preds; this.showDropdown = true; this.highlighted = -1; }
                    else { this.predictions = []; this.showDropdown = false; }
                }
            );
        },

        selectPrediction(pred) {
            this.showDropdown = false; this.predictions = []; this.searchText = pred.description;
            const svc = new google.maps.places.PlacesService(document.createElement('div'));
            svc.getDetails({ placeId: pred.place_id, fields: ['geometry', 'formatted_address', 'place_id'] }, (place, status) => {
                if (status !== google.maps.places.PlacesServiceStatus.OK || !place.geometry) return;
                const lat = place.geometry.location.lat(), lng = place.geometry.location.lng();
                this.selectedPlace = true; this.selectedAddress = place.formatted_address; this.searchText = place.formatted_address;
                this.selectedLat = lat; this.selectedLng = lng; this.selectedPlaceId = place.place_id;
                this.emitUpdate();
                this.$nextTick(() => this.showMap(lat, lng));
            });
        },

        showMap(lat, lng) {
            const el = this.$refs.mapEl; if (!el) return;
            const pos = { lat, lng };
            this.map = new google.maps.Map(el, { center: pos, zoom: 16, disableDefaultUI: true, zoomControl: true,
                styles: [{ elementType: 'geometry', stylers: [{ color: '#1d2535' }] }, { elementType: 'labels.text.fill', stylers: [{ color: '#8ec3b9' }] }, { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#304a7d' }] }, { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1626' }] }] });
            this.marker = new google.maps.Marker({ position: pos, map: this.map, draggable: true, animation: google.maps.Animation.DROP,
                icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#FBBF24', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 } });
            this.marker.addListener('dragend', (e) => {
                this.selectedLat = e.latLng.lat(); this.selectedLng = e.latLng.lng();
                new google.maps.Geocoder().geocode({ location: { lat: this.selectedLat, lng: this.selectedLng } }, (results, st) => {
                    if (st === 'OK' && results[0]) { this.selectedAddress = results[0].formatted_address; this.searchText = results[0].formatted_address; }
                    this.emitUpdate();
                });
            });
            setTimeout(() => { google.maps.event.trigger(this.map, 'resize'); this.map.setCenter(pos); }, 100);
        },

        clearSelection() { this.selectedPlace = null; this.selectedAddress = ''; this.selectedLat = ''; this.selectedLng = ''; this.selectedPlaceId = ''; this.searchText = ''; this.emitUpdate(); },

        emitUpdate() {
            window.dispatchEvent(new CustomEvent('address-selected', { detail: { address: this.selectedAddress, lat: this.selectedLat, lng: this.selectedLng, place_id: this.selectedPlaceId } }));
        }
    }
}
</script>