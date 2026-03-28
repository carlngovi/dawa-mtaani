<x-guest-layout>
<div class="min-h-screen bg-gray-900 flex items-center justify-center px-4 py-10">
  <div class="w-full max-w-lg">

    <div class="text-center mb-8">
      <div class="text-yellow-400 font-bold text-xl tracking-widest">DAWA MTAANI</div>
      <div class="text-gray-400 text-sm mt-1">Register your pharmacy</div>
    </div>

    <form method="POST" action="{{ route('register.facility.store') }}" class="bg-gray-800 border border-gray-700 rounded-2xl p-6 space-y-6">
      @csrf

      {{-- ── Section: Facility Details ── --}}
      <div>
        <h3 class="text-yellow-400 text-xs font-semibold uppercase tracking-widest mb-4">Facility Details</h3>
        <div class="space-y-4">
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Pharmacy / Facility Name</label>
            <input type="text" name="facility_name" value="{{ old('facility_name') }}" required
              class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
              placeholder="e.g. Afya Chemist">
            @error('facility_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Phone</label>
              <input type="tel" name="phone" value="{{ old('phone') }}" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
                placeholder="0712 345 678">
              @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="text-gray-400 text-xs mb-1 block">PPB Licence <span class="text-gray-500">(Optional)</span></label>
              <input type="text" name="ppb_licence_number" value="{{ old('ppb_licence_number') }}"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
                placeholder="PPB/XXXX">
              <p class="text-gray-500 text-xs mt-1">Can be added later after approval</p>
              @error('ppb_licence_number') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>
      </div>

      {{-- ── Section: Location ── --}}
      <div x-data="locationPicker({ initCounty: {{ json_encode(old('kenya_county_id')) }}, initConstituency: {{ json_encode(old('kenya_constituency_id')) }}, initWard: {{ json_encode(old('kenya_ward_id')) }} })" x-init="init()">
        <h3 class="text-yellow-400 text-xs font-semibold uppercase tracking-widest mb-4">Location</h3>
        <div class="space-y-4">

          {{-- County --}}
          <div>
            <label class="text-gray-400 text-xs mb-1 block">County</label>
            <div class="relative">
              <select name="kenya_county_id" x-model="selectedCountyId" @change="onCountyChange()" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400 pr-10">
                <option value="">Select county…</option>
                <template x-for="c in counties" :key="c.id">
                  <option :value="c.id" x-text="c.name"></option>
                </template>
              </select>
              <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              <span x-show="loadingCounties" class="absolute right-8 top-1/2 -translate-y-1/2 w-4 h-4 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
            </div>
            @error('kenya_county_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Constituency --}}
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Constituency</label>
            <div class="relative">
              <select name="kenya_constituency_id" x-model="selectedConstituencyId" @change="onConstituencyChange()" required
                :disabled="!selectedCountyId"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400 pr-10 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Select constituency…</option>
                <template x-for="c in constituencies" :key="c.id">
                  <option :value="c.id" x-text="c.name"></option>
                </template>
              </select>
              <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              <span x-show="loadingCons" class="absolute right-8 top-1/2 -translate-y-1/2 w-4 h-4 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
            </div>
            @error('kenya_constituency_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Ward --}}
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Ward</label>
            <div class="relative">
              <select name="kenya_ward_id" x-model="selectedWardId" required
                :disabled="!selectedConstituencyId"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400 pr-10 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Select ward…</option>
                <template x-for="w in wards" :key="w.id">
                  <option :value="w.id" x-text="w.name"></option>
                </template>
              </select>
              <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              <span x-show="loadingWards" class="absolute right-8 top-1/2 -translate-y-1/2 w-4 h-4 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
            </div>
            @error('kenya_ward_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Village / Town --}}
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Village / Town Centre</label>
            <input type="text" name="village_town" value="{{ old('village_town') }}"
              class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
              placeholder="e.g. Kawangware, Tom Mboya St">
            @error('village_town') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

        </div>
      </div>

      {{-- ── Section: Account Credentials ── --}}
      <div>
        <h3 class="text-yellow-400 text-xs font-semibold uppercase tracking-widest mb-4">Account Credentials</h3>
        <div class="space-y-4">
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required
              class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
              placeholder="you@example.com">
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Password</label>
              <input type="password" name="password" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
                placeholder="Min 8 characters">
              @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Confirm Password</label>
              <input type="password" name="password_confirmation" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400"
                placeholder="Repeat password">
            </div>
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-white font-bold py-3 rounded-xl transition-colors">
        Register Pharmacy
      </button>

      <p class="text-center text-sm text-gray-400">Already registered? <a href="{{ route('login') }}" class="text-yellow-400 hover:underline">Sign in</a></p>
    </form>

  </div>
</div>

@push('scripts')
<script>
function locationPicker({ initCounty, initConstituency, initWard }) {
  return {
    counties: [],
    constituencies: [],
    wards: [],
    selectedCountyId: initCounty || '',
    selectedConstituencyId: initConstituency || '',
    selectedWardId: initWard || '',
    loadingCounties: false,
    loadingCons: false,
    loadingWards: false,

    async init() {
      this.loadingCounties = true;
      try {
        const res = await fetch('/api/kenya/counties');
        this.counties = await res.json();
      } catch (e) { console.error('Failed to load counties', e); }
      this.loadingCounties = false;

      // Restore cascaded old() selections
      if (this.selectedCountyId) {
        await this.fetchConstituencies(this.selectedCountyId);
        if (this.selectedConstituencyId) {
          await this.fetchWards(this.selectedConstituencyId);
        }
      }
    },

    async onCountyChange() {
      this.constituencies = [];
      this.wards = [];
      this.selectedConstituencyId = '';
      this.selectedWardId = '';
      if (this.selectedCountyId) {
        await this.fetchConstituencies(this.selectedCountyId);
      }
    },

    async onConstituencyChange() {
      this.wards = [];
      this.selectedWardId = '';
      if (this.selectedConstituencyId) {
        await this.fetchWards(this.selectedConstituencyId);
      }
    },

    async fetchConstituencies(countyId) {
      this.loadingCons = true;
      try {
        const res = await fetch(`/api/kenya/constituencies/${countyId}`);
        this.constituencies = await res.json();
      } catch (e) { console.error('Failed to load constituencies', e); }
      this.loadingCons = false;
    },

    async fetchWards(constituencyId) {
      this.loadingWards = true;
      try {
        const res = await fetch(`/api/kenya/wards/${constituencyId}`);
        this.wards = await res.json();
      } catch (e) { console.error('Failed to load wards', e); }
      this.loadingWards = false;
    },
  };
}
</script>
@endpush
</x-guest-layout>
