@extends('layouts.app')
@section('title', 'Register Pharmacy — Dawa Mtaani')
@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{
         step: 1,
         lat: null, lng: null, accuracy: null,
         gpsMethod: 'skip',
         getLocation() {
             navigator.geolocation.getCurrentPosition(
                 p => {
                     this.lat      = p.coords.latitude.toFixed(6);
                     this.lng      = p.coords.longitude.toFixed(6);
                     this.accuracy = Math.round(p.coords.accuracy);
                     this.gpsMethod = 'device';
                 },
                 e => { alert('Location unavailable: ' + e.message); }
             );
         }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Register New Pharmacy</h1>
        <p class="text-sm text-gray-500 mt-1">
            Pharmacy owners may also self-register at /register.
            Admin review is mandatory before activation regardless of who submits.
        </p>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        After submission the application will be queued for PPB verification and then mandatory admin review.
        No facility goes ACTIVE automatically.
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center gap-2">
        @foreach([1 => 'Basic Info', 2 => 'Location', 3 => 'Review'] as $n => $label)
        <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold"
                 :class="step >= {{ $n }}
                     ? 'bg-yellow-400 text-gray-900'
                     : 'bg-gray-200 text-gray-500'">
                {{ $n }}
            </div>
            <span class="text-xs text-gray-500 hidden sm:block">{{ $label }}</span>
        </div>
        @if($n < 3)
        <div class="flex-1 h-px bg-gray-200 max-w-[40px]"></div>
        @endif
        @endforeach
    </div>

    <form method="POST" action="/api/v1/facilities">
        @csrf

        {{-- Step 1 --}}
        <div x-show="step === 1" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-800">Basic Information</h2>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Owner Name <span class="text-red-500">*</span></label>
                <input type="text" name="owner_name" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    PPB Licence Number <span class="text-red-500">*</span>
                    <span class="text-gray-400 font-normal ml-1">— verified against PPB registry on submission</span>
                </label>
                <input type="text" name="ppb_licence" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="phone" placeholder="+254..."
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="pt-2">
                <button type="button" @click="step = 2"
                        class="w-full px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                    Next: Location →
                </button>
            </div>
        </div>

        {{-- Step 2 --}}
        <div x-show="step === 2" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-800">Location</h2>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">County</label>
                @if($counties->isNotEmpty())
                <select name="county"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select county...</option>
                    @foreach($counties as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                @else
                <input type="text" name="county" placeholder="e.g. Kilifi"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sub-County</label>
                    <input type="text" name="sub_county"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Ward</label>
                    <input type="text" name="ward"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Physical Address</label>
                <textarea name="physical_address" rows="2"
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-2">
                    Network Membership
                    <span class="text-gray-400 font-normal ml-1">— confirm with pharmacy owner</span>
                </label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="network_membership" value="NETWORK" class="text-green-700">
                        <span class="text-sm text-gray-700">Network</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="network_membership" value="OFF_NETWORK" class="text-green-700">
                        <span class="text-sm text-gray-700">Off-Network</span>
                    </label>
                </div>
            </div>

            {{-- GPS --}}
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <p class="text-xs font-medium text-gray-700">GPS Capture (encouraged, not mandatory)</p>
                <div class="flex gap-3 flex-wrap">
                    <button type="button" @click="getLocation()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-50">
                        Use My Location
                    </button>
                    <button type="button" @click="gpsMethod = 'manual'"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-50">
                        Enter Manually
                    </button>
                    <button type="button" @click="gpsMethod = 'skip'; lat = null; lng = null"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-xs text-gray-500 hover:bg-gray-50">
                        Skip for Now
                    </button>
                </div>
                <div x-show="lat !== null" class="text-xs space-y-1">
                    <p class="text-gray-600">Lat: <span class="font-mono" x-text="lat"></span>
                       · Lng: <span class="font-mono" x-text="lng"></span></p>
                    <span :class="accuracy <= 50 ? 'bg-green-100 text-green-700' : accuracy <= 100 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'"
                          class="inline-flex px-2 py-0.5 rounded text-xs font-medium">
                        Accuracy: <span x-text="accuracy"></span>m
                    </span>
                    <p x-show="accuracy > 50" class="text-amber-600">
                        Accuracy exceeds 50m threshold. GPS will be flagged for review.
                    </p>
                </div>
                <div x-show="gpsMethod === 'manual'" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Latitude</label>
                        <input type="number" name="latitude" step="0.000001"
                               placeholder="-3.3731" x-model="lat"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Longitude</label>
                        <input type="number" name="longitude" step="0.000001"
                               placeholder="36.6860" x-model="lng"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                <input type="hidden" name="latitude"  :value="lat">
                <input type="hidden" name="longitude" :value="lng">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="step = 1"
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                    ← Back
                </button>
                <button type="button" @click="step = 3"
                        class="flex-1 px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                    Next: Review →
                </button>
            </div>
        </div>

        {{-- Step 3 --}}
        <div x-show="step === 3" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-800">Review & Submit</h2>
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600 space-y-2">
                <p>Please review the details in the form above before submitting.</p>
                <p>After submission the application will be queued for <strong>PPB verification</strong>
                   followed by mandatory <strong>admin review</strong> before activation.</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="step = 2"
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                    ← Back
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                    Submit Registration
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
