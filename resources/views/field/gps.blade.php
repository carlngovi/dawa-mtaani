@extends('layouts.app')
@section('title', 'GPS Capture — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         tab: 'device',
         lat: null, lng: null, accuracy: null,
         locating: false, locError: null,
         getLocation() {
             if (!navigator.geolocation) {
                 this.locError = 'Geolocation is not supported by your browser.';
                 return;
             }
             this.locating = true;
             this.locError = null;
             navigator.geolocation.getCurrentPosition(
                 p => {
                     this.lat      = p.coords.latitude.toFixed(6);
                     this.lng      = p.coords.longitude.toFixed(6);
                     this.accuracy = Math.round(p.coords.accuracy);
                     this.locating = false;
                 },
                 e => {
                     this.locating = false;
                     if (e.code === 1) this.locError = 'Location permission denied. Please allow location access in your browser settings.';
                     else if (e.code === 2) this.locError = 'Location unavailable. Ensure GPS is enabled on your device.';
                     else if (e.code === 3) this.locError = 'Location request timed out. Please try again.';
                     else this.locError = 'Location error: ' + e.message;
                 },
                 { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
             );
         }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">
            @if($facility) GPS — {{ $facility->facility_name }} @else GPS Capture @endif
        </h1>
        @if($facility)
        <p class="text-sm text-gray-400 mt-1">
            {{ $facility->ward ?? '' }}
            @if($facility->latitude)
                · <span class="text-green-400 font-medium">GPS already captured</span>
            @else
                · <span class="text-amber-400">GPS not yet captured</span>
            @endif
        </p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Tabs --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Tab buttons --}}
            <div class="flex gap-2 flex-wrap">
                @foreach(['device' => 'Device', 'map' => 'Map Pin', 'manual' => 'Manual', 'bulk' => 'Bulk'] as $t => $label)
                <button type="button" @click="tab = '{{ $t }}'"
                        :class="tab === '{{ $t }}' ? 'bg-yellow-400 text-gray-900' : 'border border-gray-600 text-gray-400 hover:bg-gray-900'"
                        class="px-4 py-2 rounded-lg text-sm transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Tab: Device Auto --}}
            <div x-show="tab === 'device'" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Device Auto-Detect</h3>
                <p class="text-xs text-gray-400">50m accuracy threshold required. Locations over 50m will be flagged for review.</p>
                <button type="button" @click="getLocation()" :disabled="locating"
                        class="px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors disabled:opacity-50">
                    <span x-show="!locating">Request My Location</span>
                    <span x-show="locating" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Locating...
                    </span>
                </button>
                <div x-show="locError" class="rounded-lg bg-red-900/20 border border-red-800 px-4 py-3 text-sm text-red-300">
                    <span x-text="locError"></span>
                </div>
                <div x-show="lat !== null" class="space-y-2">
                    <p class="text-sm text-gray-400">
                        Lat: <span class="font-mono" x-text="lat"></span>
                        · Lng: <span class="font-mono" x-text="lng"></span>
                    </p>
                    <span :class="accuracy <= 50 ? 'bg-green-900/30 text-green-400' : accuracy <= 100 ? 'bg-amber-900/30 text-amber-400' : 'bg-red-900/30 text-red-400'"
                          class="inline-flex px-2 py-0.5 rounded text-xs font-medium">
                        Accuracy: <span x-text="accuracy"></span>m
                    </span>
                    <p x-show="accuracy > 50" class="text-xs text-amber-400">
                        Accuracy exceeds 50m threshold. Location will be saved but flagged for review.
                    </p>
                    @if($facility)
                    <form method="POST" action="/api/v1/facilities/{{ $facility->ulid }}/gps" class="pt-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="method"    value="DEVICE_AUTO">
                        <input type="hidden" name="latitude"  :value="lat">
                        <input type="hidden" name="longitude" :value="lng">
                        <input type="hidden" name="accuracy"  :value="accuracy">
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                            Save Location
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Tab: Map Pin --}}
            <div x-show="tab === 'map'" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Map Pin</h3>
                <div class="bg-gray-700 rounded-lg h-48 flex items-center justify-center">
                    <p class="text-gray-400 text-sm">Interactive map — Phase 2 (Leaflet.js integration)</p>
                </div>
                <p class="text-xs text-gray-400">Use Manual Entry below as a fallback until the map is available.</p>
            </div>

            {{-- Tab: Manual Entry --}}
            <div x-show="tab === 'manual'" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Manual Entry</h3>
                @if($facility)
                <form method="POST" action="/api/v1/facilities/{{ $facility->ulid }}/gps" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="method" value="MANUAL_ENTRY">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Latitude</label>
                            <input type="number" name="latitude" step="0.000001"
                                   placeholder="-3.3731" required
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm font-mono focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Longitude</label>
                            <input type="number" name="longitude" step="0.000001"
                                   placeholder="36.6860" required
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm font-mono focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        </div>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500">
                        Save Location
                    </button>
                </form>
                @else
                <p class="text-sm text-gray-400">Select a pharmacy from the list on the right to capture GPS.</p>
                @endif
            </div>

            {{-- Tab: Bulk Upload --}}
            <div x-show="tab === 'bulk'" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Bulk CSV Upload</h3>
                <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-sm px-4 py-3 rounded-lg">
                    Bulk GPS upload via CSV is an admin-only operation and is not available in this portal.
                </div>
                <a href="/admin/facilities"
                   class="inline-flex px-4 py-2 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-900">
                    Go to Admin Panel →
                </a>
            </div>
        </div>

        {{-- Pending list --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden h-fit">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-300">
                    {{ $pending->count() }} without GPS in {{ $county }}
                </h3>
            </div>
            @if($pending->isEmpty())
            <p class="px-5 py-6 text-center text-gray-400 text-sm">All pharmacies have GPS captured</p>
            @else
            <ul class="divide-y divide-gray-700 max-h-96 overflow-y-auto">
                @foreach($pending as $p)
                <li>
                    <a href="/field/gps/{{ $p->ulid }}"
                       class="flex items-center justify-between px-5 py-3 hover:bg-gray-900 transition-colors
                              {{ isset($facility) && $facility->ulid === $p->ulid ? 'bg-green-900/20' : '' }}">
                        <div>
                            <p class="text-sm font-medium text-gray-200">{{ $p->facility_name }}</p>
                            <p class="text-xs text-gray-400">{{ $p->ward }}</p>
                        </div>
                        <span class="text-amber-500 text-xs">→</span>
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>
</div>
@endsection
