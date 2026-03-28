@extends('layouts.app')
@section('title', 'Delivery Queue — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         confirmOpen: false,
         confirmId:   null,
         method:      'STAFF_NAME',
         value:       '',
         open(id) { this.confirmId = id; this.method = 'STAFF_NAME'; this.value = ''; this.confirmOpen = true; }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Delivery Queue</h1>
        <p class="text-sm text-gray-400 mt-1">Manage and confirm active deliveries</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Awaiting Delivery</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $stats['awaiting_delivery'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Delivered Today</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['delivered_today'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
            <p class="text-xs text-gray-400">Open Disputes</p>
            <p class="text-3xl font-bold text-{{ $stats['open_disputes'] > 0 ? 'red-400' : 'white' }} mt-1">
                {{ $stats['open_disputes'] }}
            </p>
        </div>
    </div>

    {{-- Delivery Map --}}
    @if($googleMapsKey)
    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden"
         x-data="deliveryMap()" x-init="init()">
        <div class="px-5 py-3 border-b border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-white text-sm">Delivery Locations</h2>
            <span class="text-xs text-gray-400" x-text="pinCount + ' GPS-confirmed orders'"></span>
        </div>
        <div x-ref="mapEl" style="height: 320px;" class="w-full"></div>
    </div>
    <script>
    function deliveryMap() {
        return {
            map: null, pinCount: 0,
            pins: {!! $mapPinsJson !!},
            init() {
                this.pinCount = this.pins.length;
                if (this.pins.length === 0) { this.$el.style.display = 'none'; return; }
                const render = () => {
                    const el = this.$refs.mapEl;
                    if (!el) return;
                    this.map = new google.maps.Map(el, {
                        center: { lat: -1.2921, lng: 36.8219 },
                        zoom: 7, disableDefaultUI: true, zoomControl: true,
                        styles: [
                            { elementType: 'geometry', stylers: [{ color: '#1d2535' }] },
                            { elementType: 'labels.text.fill', stylers: [{ color: '#8ec3b9' }] },
                            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#304a7d' }] },
                            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1626' }] }
                        ]
                    });
                    const bounds = new google.maps.LatLngBounds();
                    this.pins.forEach(p => {
                        const pos = { lat: p.lat, lng: p.lng };
                        const color = p.type === 'B2C' ? '#FBBF24' : '#818CF8';
                        const marker = new google.maps.Marker({
                            position: pos, map: this.map,
                            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: color, fillOpacity: 1, strokeColor: '#fff', strokeWeight: 1.5 }
                        });
                        const info = new google.maps.InfoWindow({
                            content: `<div style="color:#1f2937;font-size:12px;max-width:200px">
                                <strong>${p.name}</strong><br>
                                <span style="color:#6b7280">${p.address || 'No address'}</span><br>
                                <span style="color:#d97706">Ref: ${p.ulid} · KES ${p.amount.toLocaleString()}</span><br>
                                <span style="background:${color};color:#fff;padding:1px 6px;border-radius:4px;font-size:10px">${p.type} · ${p.status}</span>
                            </div>`
                        });
                        marker.addListener('click', () => info.open(this.map, marker));
                        bounds.extend(pos);
                    });
                    if (this.pins.length > 1) this.map.fitBounds(bounds, 40);
                    else this.map.setCenter({ lat: this.pins[0].lat, lng: this.pins[0].lng });
                };
                if (typeof google !== 'undefined' && google.maps) render();
                else window.addEventListener('google-maps-loaded', render);
            }
        }
    }
    </script>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="county"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All counties</option>
            @foreach($counties as $county)
                <option value="{{ $county }}" {{ request('county') === $county ? 'selected' : '' }}>
                    {{ $county }}
                </option>
            @endforeach
        </select>
        <select name="status"
                class="px-3 py-2.5 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All statuses</option>
            @foreach(['CONFIRMED', 'PACKED', 'DISPATCHED', 'DELIVERED', 'PREPARING', 'READY'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['county', 'status']))
            <a href="/logistics/deliveries"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Order Ref</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Destination</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">County</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Delivery Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-900">
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">
                            {{ substr($delivery->order_ulid, -8) }}
                        </td>
                        <td class="px-5 py-3">
                            @if($delivery->order_type === 'B2C')
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400">Customer</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-indigo-900/30 text-indigo-400">Retail</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-200">{{ $delivery->patient_name ?? $delivery->facility_name }}</p>
                            @if($delivery->ward)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $delivery->ward }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-400 hidden md:table-cell">
                            {{ $delivery->county }}
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-200">
                            {{ $currency['symbol'] }} {{ number_format($delivery->total_amount, $currency['decimal_places']) }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($delivery->status) {
                                    'DISPATCHED' => 'bg-blue-900/30 text-blue-400',
                                    'DELIVERED'  => 'bg-green-900/30 text-green-400',
                                    'PACKED'     => 'bg-cyan-900/30 text-cyan-400',
                                    'CONFIRMED'  => 'bg-purple-900/30 text-purple-400',
                                    'PREPARING'  => 'bg-amber-900/30 text-amber-400',
                                    'READY'      => 'bg-green-900/30 text-green-300',
                                    default      => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $delivery->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs hidden lg:table-cell max-w-[200px]">
                            <p class="text-gray-400 truncate">{{ $delivery->delivery_address ?? '—' }}</p>
                            @if($delivery->delivery_lat && $delivery->delivery_lng)
                                <a href="https://www.google.com/maps?q={{ $delivery->delivery_lat }},{{ $delivery->delivery_lng }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-green-400 hover:text-green-300 mt-0.5">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    GPS
                                </a>
                            @else
                                <span class="text-gray-600 text-[10px]">No GPS</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No deliveries in the queue
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $deliveries->links() }}</div>

    {{-- Confirm Delivery modal --}}
    <div x-show="confirmOpen"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-white">Confirm Delivery</h3>
            <p class="text-sm text-gray-400 mt-1">Select confirmation method and enter details.</p>
            <form method="POST"
                  :action="'/api/v1/deliveries/' + confirmId + '/confirm'"
                  class="mt-4 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-300">Confirmation Method</label>
                    <div class="flex gap-3">
                        @foreach(['STAFF_NAME' => 'Staff Name', 'PHOTO' => 'Photo', 'OTP' => 'OTP'] as $val => $label)
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="confirmation_type"
                                   value="{{ $val }}" x-model="method"
                                   class="text-green-400">
                            <span class="text-sm text-gray-400">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">
                        <span x-text="method === 'STAFF_NAME' ? 'Staff Name' : (method === 'PHOTO' ? 'Photo Reference' : 'OTP Code')"></span>
                    </label>
                    <input type="text" name="confirmation_value" x-model="value"
                           required
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                        Confirm Delivery
                    </button>
                    <button type="button" @click="confirmOpen = false"
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
