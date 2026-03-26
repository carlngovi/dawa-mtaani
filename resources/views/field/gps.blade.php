@extends('layouts.app')
@section('title', 'GPS Capture — Dawa Mtaani')
@section('content')
<div class="space-y-6"
     x-data="{
         tab: 'device',
         lat: null, lng: null, accuracy: null,
         getLocation() {
             navigator.geolocation.getCurrentPosition(
                 p => {
                     this.lat      = p.coords.latitude.toFixed(6);
                     this.lng      = p.coords.longitude.toFixed(6);
                     this.accuracy = Math.round(p.coords.accuracy);
                 },
                 e => { alert('Location unavailable: ' + e.message); }
             );
         }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            @if($facility) GPS — {{ $facility->facility_name }} @else GPS Capture @endif
        </h1>
        @if($facility)
        <p class="text-sm text-gray-500 mt-1">
            {{ $facility->ward ?? '' }}
            @if($facility->latitude)
                · <span class="text-green-600 font-medium">GPS already captured</span>
            @else
                · <span class="text-amber-600">GPS not yet captured</span>
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
                        :class="tab === '{{ $t }}' ? 'bg-green-700 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Tab: Device Auto --}}
            <div x-show="tab === 'device'" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Device Auto-Detect</h3>
                <p class="text-xs text-gray-400">50m accuracy threshold required. Locations over 50m will be flagged for review.</p>
                <button type="button" @click="getLocation()"
                        class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                    Request My Location
                </button>
                <div x-show="lat !== null" class="space-y-2">
                    <p class="text-sm text-gray-600">
                        Lat: <span class="font-mono" x-text="lat"></span>
                        · Lng: <span class="font-mono" x-text="lng"></span>
                    </p>
                    <span :class="accuracy <= 50 ? 'bg-green-100 text-green-700' : accuracy <= 100 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'"
                          class="inline-flex px-2 py-0.5 rounded text-xs font-medium">
                        Accuracy: <span x-text="accuracy"></span>m
                    </span>
                    <p x-show="accuracy > 50" class="text-xs text-amber-600">
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
                                class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                            Save Location
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Tab: Map Pin --}}
            <div x-show="tab === 'map'" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Map Pin</h3>
                <div class="bg-gray-100 rounded-lg h-48 flex items-center justify-center">
                    <p class="text-gray-400 text-sm">Interactive map — Phase 2 (Leaflet.js integration)</p>
                </div>
                <p class="text-xs text-gray-400">Use Manual Entry below as a fallback until the map is available.</p>
            </div>

            {{-- Tab: Manual Entry --}}
            <div x-show="tab === 'manual'" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Manual Entry</h3>
                @if($facility)
                <form method="POST" action="/api/v1/facilities/{{ $facility->ulid }}/gps" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="method" value="MANUAL_ENTRY">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="number" name="latitude" step="0.000001"
                                   placeholder="-3.3731" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="number" name="longitude" step="0.000001"
                                   placeholder="36.6860" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                        Save Location
                    </button>
                </form>
                @else
                <p class="text-sm text-gray-500">Select a pharmacy from the list on the right to capture GPS.</p>
                @endif
            </div>

            {{-- Tab: Bulk Upload --}}
            <div x-show="tab === 'bulk'" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Bulk CSV Upload</h3>
                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
                    Bulk GPS upload via CSV is an admin-only operation and is not available in this portal.
                </div>
                <a href="/admin/facilities"
                   class="inline-flex px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                    Go to Admin Panel →
                </a>
            </div>
        </div>

        {{-- Pending list --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden h-fit">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    {{ $pending->count() }} without GPS in {{ $county }}
                </h3>
            </div>
            @if($pending->isEmpty())
            <p class="px-5 py-6 text-center text-gray-400 text-sm">All pharmacies have GPS captured</p>
            @else
            <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @foreach($pending as $p)
                <li>
                    <a href="/field/gps/{{ $p->ulid }}"
                       class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors
                              {{ isset($facility) && $facility->ulid === $p->ulid ? 'bg-green-50' : '' }}">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $p->facility_name }}</p>
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
