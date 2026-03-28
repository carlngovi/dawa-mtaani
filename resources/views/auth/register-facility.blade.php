<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register your Pharmacy — Dawa Mtaani</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const t = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
                document.body && document.body.classList.add('dark', 'bg-gray-900');
            }
        })();
    </script>
</head>
<body class="h-full bg-gray-900">

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">

    {{-- Logo --}}
    <a href="/" class="flex items-center gap-2 mb-8">
        <div class="h-8 w-8 rounded-lg bg-yellow-400 flex items-center justify-center">
            <span class="text-white font-bold text-sm">DM</span>
        </div>
        <span class="text-lg font-bold text-white">
            Dawa<span class="text-yellow-400">Mtaani</span>
        </span>
    </a>

    <div class="w-full max-w-lg">

        <h1 class="text-2xl font-bold text-white mb-2 text-center">
            Register your Pharmacy
        </h1>
        <p class="text-sm text-gray-400 mb-6 text-center">
            For pharmacy owners &amp; operators
        </p>

        {{-- Notice --}}
        <div class="rounded-lg bg-blue-900/20 border border-gray-700 p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-blue-400">
                    After submitting, your PPB licence will be verified automatically.
                    Your account will be reviewed by our team before activation.
                    This usually takes 1–2 business days.
                </p>
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
        <div class="alert-error mb-5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        @if (session('status'))
        <div class="alert-info mb-5">
            {{ session('status') }}
        </div>
        @endif

        {{-- Google prefill badge --}}
        @if ($prefill)
        <div class="flex items-center gap-3 rounded-lg border border-gray-700 bg-green-900/20 p-3 mb-6">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm text-green-400">
                Google account linked: <strong>{{ $prefill['email'] }}</strong>. Complete the form below.
            </span>
        </div>
        @endif

        {{-- Registration form --}}
        <form method="POST" action="{{ route('register.facility.store') }}" class="space-y-5">
            @csrf

            {{-- Owner full name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Owner full name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $prefill['name'] ?? '') }}"
                       required
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>

            {{-- PPB Licence Number (optional) --}}
            <div>
                <label for="ppb_licence_number" class="block text-sm font-medium text-gray-300 mb-1.5">
                    PPB Licence Number <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <input type="text" id="ppb_licence_number" name="ppb_licence_number"
                       value="{{ old('ppb_licence_number') }}"
                       placeholder="PPB/PH/XXXX/XXXX"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
                <p class="mt-1 text-xs text-gray-400">Can be added later after approval</p>
            </div>

            {{-- Facility name --}}
            <div>
                <label for="facility_name" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Facility / Pharmacy name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="facility_name" name="facility_name"
                       value="{{ old('facility_name') }}"
                       required
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Phone number <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone" name="phone"
                       value="{{ old('phone') }}"
                       required
                       placeholder="+254XXXXXXXXX"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Email address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $prefill['email'] ?? '') }}"
                       required
                       {{ $prefill ? 'readonly' : '' }}
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 {{ $prefill ? 'bg-gray-800' : '' }}"/>
            </div>

            {{-- Location (cascading county → constituency → ward) --}}
            <div x-data="locationPicker({
                initCounty: {{ json_encode(old('kenya_county_id')) }},
                initConstituency: {{ json_encode(old('kenya_constituency_id')) }},
                initWard: {{ json_encode(old('kenya_ward_id')) }}
            })" x-init="init()" class="space-y-5">

                {{-- County --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        County <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <input type="hidden" name="kenya_county_id" :value="selectedCountyId">
                        <button type="button" @click="open = !open"
                            class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 flex items-center justify-between text-left"
                            :class="!selectedCountyId ? 'text-gray-400' : 'text-gray-200'">
                            <span x-text="selectedCountyId ? counties.find(c => c.id == selectedCountyId)?.name : 'Select county...'"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                            <div class="sticky top-0 bg-gray-800 px-3 py-2 border-b border-gray-700">
                                <input type="text" x-model="countySearch" placeholder="Search county..."
                                    class="w-full bg-gray-700 border border-gray-600 rounded text-white text-sm px-3 py-1.5 focus:outline-none focus:border-yellow-400" @click.stop>
                            </div>
                            <div x-show="loadingCounties" class="px-4 py-3 text-sm text-yellow-400">Loading...</div>
                            <template x-for="county in counties" :key="county.id">
                                <div x-show="!countySearch || county.name.toLowerCase().includes(countySearch.toLowerCase())"
                                    @click="selectedCountyId = county.id; open = false; countySearch = ''; onCountyChange()"
                                    class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-700 hover:text-yellow-400"
                                    :class="county.id == selectedCountyId ? 'bg-gray-700 text-yellow-400' : 'text-white'">
                                    <span x-text="county.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Constituency --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Constituency <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <input type="hidden" name="kenya_constituency_id" :value="selectedConstituencyId">
                        <button type="button" @click="if (selectedCountyId && !loadingCons) open = !open"
                            class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 flex items-center justify-between text-left"
                            :class="[!selectedCountyId || loadingCons ? 'opacity-50 cursor-not-allowed' : '', !selectedConstituencyId ? 'text-gray-400' : 'text-gray-200']">
                            <span x-text="selectedConstituencyId ? constituencies.find(c => c.id == selectedConstituencyId)?.name : (selectedCountyId ? 'Select constituency...' : 'Select county first')"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                            <div x-show="loadingCons" class="px-4 py-3 text-sm text-yellow-400">Loading...</div>
                            <template x-for="con in constituencies" :key="con.id">
                                <div @click="selectedConstituencyId = con.id; open = false; onConstituencyChange()"
                                    class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-700 hover:text-yellow-400"
                                    :class="con.id == selectedConstituencyId ? 'bg-gray-700 text-yellow-400' : 'text-white'">
                                    <span x-text="con.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Ward --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Ward <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <input type="hidden" name="kenya_ward_id" :value="selectedWardId">
                        <button type="button" @click="if (selectedConstituencyId && !loadingWards) open = !open"
                            class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 flex items-center justify-between text-left"
                            :class="[!selectedConstituencyId || loadingWards ? 'opacity-50 cursor-not-allowed' : '', !selectedWardId ? 'text-gray-400' : 'text-gray-200']">
                            <span x-text="selectedWardId ? wards.find(w => w.id == selectedWardId)?.name : (selectedConstituencyId ? 'Select ward...' : 'Select constituency first')"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                            <div x-show="loadingWards" class="px-4 py-3 text-sm text-yellow-400">Loading...</div>
                            <template x-for="ward in wards" :key="ward.id">
                                <div @click="selectedWardId = ward.id; open = false"
                                    class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-700 hover:text-yellow-400"
                                    :class="ward.id == selectedWardId ? 'bg-gray-700 text-yellow-400' : 'text-white'">
                                    <span x-text="ward.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Village / Town Centre --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Village / Town Centre
                    </label>
                    <input type="text" name="village_town"
                           value="{{ old('village_town') }}"
                           placeholder="e.g. Kawangware, Tom Mboya St"
                           class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
                </div>

            </div>

            {{-- Password fields (hidden if Google prefill) --}}
            @unless ($prefill)
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password"
                       required
                       minlength="8"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
                <p class="mt-1 text-xs text-gray-400">Minimum 8 characters</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Confirm password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required
                       minlength="8"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>
            @endunless

            {{-- Terms --}}
            <label class="flex items-start gap-3 text-sm text-gray-300 cursor-pointer">
                <input type="checkbox" name="terms" value="1" required
                       class="mt-0.5 h-4 w-4 rounded border-gray-600 text-yellow-400 focus:ring-yellow-400"/>
                <span>
                    I agree to the <a href="#" class="text-yellow-600 hover:underline">Terms of Service</a>
                    and <a href="#" class="text-yellow-600 hover:underline">Privacy Policy</a>.
                </span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                    class="flex w-full items-center justify-center rounded-lg bg-yellow-400 px-4 py-3 text-sm font-bold text-white hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-colors">
                Submit Registration
            </button>
        </form>

        {{-- Google registration option (only if no prefill) --}}
        @unless ($prefill)
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-gray-800 px-4 text-gray-400">Or register with</span>
            </div>
        </div>

        <a href="{{ route('auth.google.facility') }}"
           class="flex w-full items-center justify-center gap-3 rounded-lg border border-gray-600 bg-gray-800 px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-900 transition-colors">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M19.6 10.23c0-.68-.06-1.36-.18-2H10v3.79h5.38a4.6 4.6 0 01-2 3.02v2.5h3.22c1.89-1.74 2.98-4.3 2.98-7.31z" fill="#4285F4"/>
                <path d="M10 20c2.7 0 4.96-.89 6.62-2.42l-3.22-2.5c-.9.6-2.04.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H1.07v2.58A10 10 0 0010 20z" fill="#34A853"/>
                <path d="M4.39 11.91A6.01 6.01 0 014.18 10c0-.66.11-1.3.21-1.91V5.51H1.07A10 10 0 000 10c0 1.61.39 3.14 1.07 4.49l3.32-2.58z" fill="#FBBC04"/>
                <path d="M10 3.96c1.47 0 2.79.51 3.83 1.49l2.85-2.85C14.96.99 12.7 0 10 0A10 10 0 001.07 5.51l3.32 2.58C5.18 5.72 7.39 3.96 10 3.96z" fill="#EA4335"/>
            </svg>
            Continue with Google
        </a>
        @endunless

        <p class="mt-6 text-center text-sm text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-yellow-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function locationPicker({ initCounty, initConstituency, initWard }) {
    return {
        counties: [], constituencies: [], wards: [],
        countySearch: '',
        selectedCountyId: initCounty ?? '',
        selectedConstituencyId: initConstituency ?? '',
        selectedWardId: initWard ?? '',
        loadingCounties: false, loadingCons: false, loadingWards: false,
        async init() {
            this.loadingCounties = true;
            const r = await fetch('/api/kenya/counties');
            this.counties = await r.json();
            this.loadingCounties = false;
            if (this.selectedCountyId) {
                await this.fetchConstituencies();
                if (this.selectedConstituencyId) await this.fetchWards();
            }
        },
        async fetchConstituencies() {
            this.loadingCons = true;
            const r = await fetch('/api/kenya/constituencies/' + this.selectedCountyId);
            this.constituencies = await r.json();
            this.loadingCons = false;
        },
        async fetchWards() {
            this.loadingWards = true;
            const r = await fetch('/api/kenya/wards/' + this.selectedConstituencyId);
            this.wards = await r.json();
            this.loadingWards = false;
        },
        async onCountyChange() {
            this.constituencies = []; this.wards = [];
            this.selectedConstituencyId = ''; this.selectedWardId = '';
            if (this.selectedCountyId) await this.fetchConstituencies();
        },
        async onConstituencyChange() {
            this.wards = []; this.selectedWardId = '';
            if (this.selectedConstituencyId) await this.fetchWards();
        }
    };
}
</script>
</body>
</html>
