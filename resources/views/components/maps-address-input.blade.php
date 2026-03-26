@props([
    'name'        => 'delivery_address',
    'label'       => 'Delivery Address',
    'placeholder' => 'Start typing your address...',
    'latName'     => 'delivery_lat',
    'lngName'     => 'delivery_lng',
    'placeIdName' => 'delivery_place_id',
])

<div x-data="mapsAddr_{{ str_replace('-','_',$name) }}()" x-init="init()" class="space-y-3">

    <label class="block text-xs text-gray-400 mb-1.5">{{ $label }}</label>

    <div class="relative">
        <input type="text" name="{{ $name }}" x-ref="addrInput" placeholder="{{ $placeholder }}" autocomplete="off"
               class="w-full bg-gray-900 border border-gray-600 text-white rounded-xl px-4 py-3 text-sm pr-10 placeholder-gray-500 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition-colors">
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-5 h-5 transition-colors" :class="selected ? 'text-yellow-400' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
    </div>

    <input type="hidden" name="{{ $latName }}" x-model="lat">
    <input type="hidden" name="{{ $lngName }}" x-model="lng">
    <input type="hidden" name="{{ $placeIdName }}" x-model="placeId">

    <div x-show="selected" x-transition class="flex items-center gap-2 text-xs text-green-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Location confirmed on map</span>
    </div>

    <div x-show="selected" x-transition class="rounded-xl overflow-hidden border border-gray-700" style="height: 220px; display: none;">
        <div x-ref="mapPreview" class="w-full h-full"></div>
    </div>

    <p x-show="!selected" class="text-xs text-gray-500">Start typing to see address suggestions</p>
</div>

<script>
function mapsAddr_{{ str_replace('-','_',$name) }}() {
    return {
        lat: '', lng: '', placeId: '', selected: false, map: null, marker: null, ac: null,
        init() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) this.setup();
            else window.addEventListener('google-maps-loaded', () => this.setup());
        },
        setup() {
            const inp = this.$refs.addrInput;
            if (!inp) return;
            this.ac = new google.maps.places.Autocomplete(inp, {
                componentRestrictions: { country: 'ke' },
                fields: ['formatted_address', 'geometry', 'place_id', 'name'],
                types: ['address', 'establishment']
            });
            this.ac.addListener('place_changed', () => this.onPlace());
        },
        onPlace() {
            const p = this.ac.getPlace();
            if (!p.geometry?.location) return;
            this.lat = p.geometry.location.lat();
            this.lng = p.geometry.location.lng();
            this.placeId = p.place_id || '';
            this.selected = true;
            this.$nextTick(() => this.showMap(this.lat, this.lng, p.formatted_address || p.name || 'Delivery'));
        },
        showMap(lat, lng, title) {
            const el = this.$refs.mapPreview;
            if (!el) return;
            const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            if (!this.map) {
                this.map = new google.maps.Map(el, { zoom: 16, center: pos, disableDefaultUI: true, zoomControl: true,
                    styles: [
                        { elementType: 'geometry', stylers: [{ color: '#1d2535' }] },
                        { elementType: 'labels.text.fill', stylers: [{ color: '#8ec3b9' }] },
                        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#304a7d' }] },
                        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1626' }] },
                    ] });
                this.marker = new google.maps.Marker({ position: pos, map: this.map, title,
                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#FBBF24', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 } });
            } else {
                this.map.setCenter(pos);
                this.marker.setPosition(pos);
            }
            setTimeout(() => { google.maps.event.trigger(this.map, 'resize'); this.map.setCenter(pos); }, 100);
        }
    }
}
</script>