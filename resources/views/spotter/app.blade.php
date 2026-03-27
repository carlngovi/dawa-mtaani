<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#facc15">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Spotter">
  <meta name="api-base" content="{{ rtrim(config('app.url'), '/') }}/api/v1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="manifest" href="/spotter-assets/manifest.json">
  <link rel="apple-touch-icon" href="/spotter-assets/icon-192.png">
  <title>Dawa Mtaani — Spotter</title>

  @vite(['resources/css/app.css'])

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    .pb-safe { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
    .pt-safe { padding-top: max(0px, env(safe-area-inset-top)); }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .field-input {
      width: 100%;
      background-color: #111827;
      border: 1px solid #374151;
      color: #fff;
      border-radius: 0.75rem;
      padding: 0.625rem 0.75rem;
      font-size: 0.875rem;
      outline: none;
    }
    .field-input:focus { border-color: #facc15; }
  </style>
</head>
<body class="bg-gray-900 text-white antialiased" x-data="spotterApp()" x-cloak>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- ACTIVATION SCREEN                                      --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div x-show="!activated" class="min-h-screen flex flex-col items-center justify-center px-4 pt-safe">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <div class="text-yellow-400 font-bold text-2xl tracking-widest">DAWA MTAANI</div>
        <div class="text-gray-400 text-sm mt-1">Spotter Field App</div>
      </div>

      <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
        <h2 class="text-white text-lg font-medium mb-1">Enter Activation Code</h2>
        <p class="text-gray-400 text-sm mb-6">Provided by your Sales Rep or Admin</p>

        <input
          type="text"
          x-model="activationCode"
          @input="formatActivationCode"
          @keydown.enter="handleActivate"
          placeholder="XXXX-XXXX-XXXX"
          maxlength="14"
          class="w-full bg-gray-900 border border-gray-700 text-white text-center text-xl tracking-widest rounded-xl px-4 py-3 focus:border-yellow-400 focus:outline-none uppercase"
          :class="activationError ? 'border-red-400' : ''"
          autocomplete="off" autocorrect="off" spellcheck="false"
        >

        <p x-show="activationError" x-text="activationError" class="text-red-400 text-sm mt-2 text-center"></p>

        <button
          @click="handleActivate"
          :disabled="activationCode.replace(/-/g,'').length < 12 || activating"
          class="w-full mt-4 bg-yellow-400 text-gray-900 font-bold py-3 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <span x-show="activating" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
          <span x-text="activating ? 'Activating...' : 'Activate Device'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- MAIN APP (post-activation)                              --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div x-show="activated" class="flex flex-col min-h-screen min-h-dvh">

    {{-- TOP BAR --}}
    <div class="bg-gray-800 border-b border-gray-700 px-4 py-3 flex items-center justify-between flex-shrink-0 pt-safe">
      <div class="flex items-center gap-3">
        <button x-show="currentView !== 'home'" @click="goBack" class="text-gray-400 hover:text-white mr-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span class="text-yellow-400 font-bold text-sm tracking-widest" x-text="viewTitle()"></span>
      </div>
      <div class="flex items-center gap-2">
        <button x-show="pendingCount > 0 && online && !syncing" @click="handleSync" class="text-xs text-yellow-400 border border-yellow-400/30 rounded-full px-2 py-0.5" x-text="`${pendingCount} pending`"></button>
        <span x-show="syncing" class="w-3 h-3 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
        <div class="flex items-center gap-1">
          <div class="w-2 h-2 rounded-full" :class="online ? 'bg-green-400' : 'bg-gray-600'"></div>
          <span class="text-xs text-gray-400" x-text="online ? 'Online' : 'Offline'"></span>
        </div>
      </div>
    </div>

    {{-- SYNC MESSAGE BANNER --}}
    <div x-show="syncMsg" x-transition class="bg-gray-700 px-4 py-2 text-sm text-gray-300 text-center flex-shrink-0" x-text="syncMsg"></div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex-1 overflow-y-auto no-scrollbar">

      {{-- ─── HOME SCREEN ─── --}}
      <div x-show="currentView === 'home'" class="px-4 py-6 space-y-4">

        {{-- Profile card --}}
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4">
          <div class="text-white text-lg font-medium" x-text="greeting()"></div>
          <div class="text-gray-400 text-sm mt-0.5" x-text="`${profile.county} County · ${profile.ward}`"></div>
          <div x-show="profile.salesRep" class="text-gray-500 text-xs mt-1" x-text="`Sales Rep: ${profile.salesRep}`"></div>
        </div>

        {{-- Clock status card --}}
        <div class="bg-gray-800 border rounded-2xl p-4" :class="clockedIn ? 'border-green-400/30' : 'border-yellow-400/30'">
          <template x-if="!clockedIn">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 110 20 10 10 0 010-20z"/></svg>
                <span class="text-white font-medium">Clock in to begin your day</span>
              </div>
              <p class="text-gray-400 text-sm mb-3">Submissions are locked until you clock in</p>
              <button @click="navigate('clock')" class="bg-yellow-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm">Clock In Now</button>
            </div>
          </template>
          <template x-if="clockedIn">
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span class="text-white font-medium">Clocked In</span>
                </div>
                <div class="text-gray-400 text-sm mt-0.5" x-text="`Since ${formatTime(clockRecord?.clockInAt)}`"></div>
              </div>
              <button @click="navigate('clock')" class="border border-gray-600 text-gray-300 px-3 py-1.5 rounded-lg text-sm">Clock Out</button>
            </div>
          </template>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 text-center">
            <div class="text-white font-bold text-xl" x-text="todaySubmissions()"></div>
            <div class="text-gray-500 text-xs">Today</div>
          </div>
          <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 text-center">
            <div class="font-bold text-xl" :class="pendingCount > 0 ? 'text-yellow-400' : 'text-white'" x-text="pendingCount"></div>
            <div class="text-gray-500 text-xs">Pending</div>
          </div>
          <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 text-center">
            <div class="font-bold text-xl" :class="overdueCount() > 0 ? 'text-red-400' : 'text-white'" x-text="overdueCount()"></div>
            <div class="text-gray-500 text-xs">Overdue</div>
          </div>
        </div>

        {{-- Quick actions --}}
        <div>
          <div class="text-gray-400 text-xs uppercase tracking-widest mb-3">Quick Actions</div>
          <div class="space-y-3">
            <button @click="clockedIn ? navigate('submit') : null" class="w-full py-4 rounded-2xl font-bold text-center transition-all" :class="clockedIn ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 border border-gray-700 text-gray-500 cursor-not-allowed opacity-50'">+ New Submission</button>
            <button @click="navigate('submissions')" class="w-full py-4 rounded-2xl font-bold text-center bg-gray-800 border border-gray-700 text-white">My Submissions</button>
          </div>
        </div>

        {{-- Overdue alerts --}}
        <div x-show="overdueCount() > 0">
          <div class="text-red-400 text-xs uppercase tracking-widest mb-2">Overdue Follow-ups</div>
          <template x-for="task in overdueFollowups().slice(0,3)" :key="task.id">
            <div class="bg-gray-800 border border-red-400/30 rounded-xl p-3 mb-2">
              <div class="text-white text-sm font-medium" x-text="task.pharmacyName"></div>
              <div class="text-red-400 text-xs" x-text="`${daysOverdue(task.followUpDate)} days overdue`"></div>
            </div>
          </template>
        </div>
      </div>

      {{-- ─── CLOCK SCREEN ─── --}}
      <div x-show="currentView === 'clock'" class="px-4 py-6">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 text-center mt-8">
          <template x-if="!clockedIn">
            <div>
              <svg class="w-16 h-16 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <h2 class="text-white text-2xl font-bold mb-1">Start Your Day</h2>
              <p class="text-gray-400 mb-2" x-text="todayDate()"></p>
              <p class="text-gray-500 text-sm mb-6" x-text="gpsStatus"></p>
              <button @click="handleClockIn" :disabled="clockingIn" class="w-full bg-yellow-400 text-gray-900 font-bold py-4 rounded-xl disabled:opacity-50 flex items-center justify-center gap-2">
                <span x-show="clockingIn" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
                <span x-text="clockingIn ? 'Getting location...' : 'Clock In'"></span>
              </button>
            </div>
          </template>
          <template x-if="clockedIn">
            <div>
              <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <h2 class="text-green-400 text-2xl font-bold mb-1">Clocked In</h2>
              <p class="text-gray-400 mb-1" x-text="`Since ${formatTime(clockRecord?.clockInAt)}`"></p>
              <p class="text-gray-500 text-sm mb-6" x-text="activeDuration()"></p>
              <template x-if="!confirmingClockOut">
                <button @click="confirmingClockOut = true" class="w-full border border-gray-600 text-white font-bold py-4 rounded-xl">Clock Out</button>
              </template>
              <template x-if="confirmingClockOut">
                <div class="space-y-3">
                  <p class="text-gray-300 text-sm">End your working day?</p>
                  <div class="flex gap-3">
                    <button @click="confirmingClockOut = false" class="flex-1 border border-gray-600 text-gray-300 py-3 rounded-xl">Cancel</button>
                    <button @click="handleClockOut" class="flex-1 bg-red-500 text-white font-bold py-3 rounded-xl">Confirm</button>
                  </div>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>

      {{-- ─── SUBMISSION WIZARD ─── --}}
      <div x-show="currentView === 'submit'" class="px-4 py-4">

        {{-- Progress indicator --}}
        <div class="flex items-center justify-between mb-6 px-2">
          <template x-for="n in [1,2,3,4,5]" :key="n">
            <div class="flex items-center">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all" :class="{'bg-yellow-400 text-gray-900': wizardStep === n, 'bg-green-400 text-gray-900': wizardStep > n, 'bg-gray-700 text-gray-500': wizardStep < n}" x-text="wizardStep > n ? '✓' : n"></div>
              <div x-show="n < 5" class="h-0.5 w-8 mx-1" :class="wizardStep > n ? 'bg-green-400' : 'bg-gray-700'"></div>
            </div>
          </template>
        </div>
        <div class="text-center text-gray-400 text-xs mb-6" x-text="['','Location','Ownership','Engagement','Follow-up','Review'][wizardStep]"></div>

        {{-- STEP 1: Location & Identity --}}
        <div x-show="wizardStep === 1" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-gray-400 text-xs mb-1 block">County</label>
              <input type="text" :value="profile.county" readonly class="field-input cursor-not-allowed" style="color:#6b7280">
            </div>
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Ward</label>
              <input type="text" :value="profile.ward" readonly class="field-input cursor-not-allowed" style="color:#6b7280">
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Town / Market Centre <span class="text-red-400">*</span></label>
            <input type="text" x-model="form.town" class="field-input" :style="formErrors.town ? 'border-color:#f87171' : ''">
            <p x-show="formErrors.town" class="text-red-400 text-xs mt-1" x-text="formErrors.town"></p>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Pharmacy Name <span class="text-red-400">*</span></label>
            <input type="text" x-model="form.pharmacy" @blur="checkDuplicate" class="field-input" :style="formErrors.pharmacy ? 'border-color:#f87171' : ''">
            <p x-show="formErrors.pharmacy" class="text-red-400 text-xs mt-1" x-text="formErrors.pharmacy"></p>
            <div x-show="duplicateWarning" class="mt-2 bg-yellow-400/10 border border-yellow-400/30 rounded-lg px-3 py-2">
              <p class="text-yellow-400 text-xs" x-text="`Similar pharmacy found: ${duplicateWarning}. Verify before continuing.`"></p>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Physical Address <span class="text-red-400">*</span></label>
            <input type="text" x-model="form.address" class="field-input" :style="formErrors.address ? 'border-color:#f87171' : ''">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">GPS Coordinates <span class="text-red-400">*</span></label>
            <div class="bg-gray-900 border rounded-xl px-3 py-3" :style="gpsCoords ? 'border-color:rgba(74,222,128,0.5)' : 'border-color:#374151'">
              <template x-if="!gpsCoords">
                <div class="flex items-center justify-between">
                  <span class="text-gray-500 text-sm" x-text="gpsStatus"></span>
                  <button @click="getGPS" :disabled="gettingGPS" class="bg-yellow-400 text-gray-900 font-bold px-3 py-1.5 rounded-lg text-sm disabled:opacity-50">
                    <span x-text="gettingGPS ? 'Getting...' : 'Get GPS'"></span>
                  </button>
                </div>
              </template>
              <template x-if="gpsCoords">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-green-400 text-sm font-mono" x-text="`${gpsCoords.lat}, ${gpsCoords.lng}`"></div>
                    <div class="text-gray-500 text-xs" x-text="`±${gpsCoords.accuracy}m accuracy`"></div>
                  </div>
                  <button @click="gpsCoords = null; gpsStatus = 'Tap to capture'" class="text-gray-400 text-xs border border-gray-700 px-2 py-1 rounded-lg">Retake</button>
                </div>
              </template>
            </div>
            <p x-show="formErrors.gps" class="text-red-400 text-xs mt-1">GPS is required</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Opens <span class="text-red-400">*</span></label>
              <input type="time" x-model="form.openTime" class="field-input">
            </div>
            <div>
              <label class="text-gray-400 text-xs mb-1 block">Closes <span class="text-red-400">*</span></label>
              <input type="time" x-model="form.closeTime" class="field-input">
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Days per Week <span class="text-red-400">*</span></label>
            <input type="number" x-model="form.daysPerWeek" min="1" max="7" class="field-input" style="width:6rem">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Exterior Photo <span class="text-red-400">*</span></label>
            <div class="flex items-center gap-4">
              <label class="bg-gray-700 border border-gray-600 text-white px-4 py-2.5 rounded-xl text-sm cursor-pointer hover:bg-gray-600 transition">
                <span x-text="form.photoData ? 'Change Photo' : 'Take / Choose Photo'"></span>
                <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoCapture($event)">
              </label>
              <div x-show="photoPreview" class="w-16 h-16 rounded-xl overflow-hidden border border-gray-600">
                <img :src="photoPreview" class="w-full h-full object-cover">
              </div>
            </div>
            <p x-show="formErrors.photo" class="text-red-400 text-xs mt-1">Photo is required</p>
          </div>
        </div>

        {{-- STEP 2: Ownership --}}
        <div x-show="wizardStep === 2" class="space-y-4">
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Name <span class="text-red-400">*</span></label>
            <input type="text" x-model="form.ownerName" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Phone <span class="text-red-400">*</span></label>
            <input type="tel" x-model="form.ownerPhone" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Pharmacy Phone</label>
            <input type="tel" x-model="form.pharmacyPhone" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Email</label>
            <input type="email" x-model="form.ownerEmail" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Present at Visit <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.ownerPresent = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.ownerPresent === 'Yes' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.ownerPresent = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.ownerPresent === 'No' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">No</button>
            </div>
          </div>
          <div x-show="form.ownerPresent === 'No'" class="bg-yellow-400/10 border border-yellow-400/30 rounded-xl px-4 py-3">
            <p class="text-yellow-400 text-sm">Form will be saved as draft. Return when the owner is present.</p>
          </div>
        </div>

        {{-- STEP 3: Engagement --}}
        <div x-show="wizardStep === 3" class="space-y-5">
          <div class="bg-gray-700/50 border border-gray-600 rounded-xl px-4 py-3">
            <p class="text-gray-300 text-sm">Internal Assessment — not shared with the pharmacy.</p>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Foot Traffic <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="opt in ['high','medium','low']" :key="opt">
                <button @click="form.footTraffic = opt" class="py-3 rounded-xl font-medium capitalize transition-all" :class="form.footTraffic === opt ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'" x-text="opt"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Shelf Stock Observed <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-2 gap-2">
              <template x-for="opt in [{v:'well_stocked',l:'Well Stocked'},{v:'moderate',l:'Moderate'},{v:'sparse',l:'Sparse'},{v:'not_observed',l:'Could Not Observe'}]" :key="opt.v">
                <button @click="form.stockLevel = opt.v" class="py-3 rounded-xl font-medium text-sm transition-all" :class="form.stockLevel === opt.v ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'" x-text="opt.l"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Activation Potential <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="opt in ['high','medium','low']" :key="opt">
                <button @click="form.potential = opt" class="py-3 rounded-xl font-medium capitalize transition-all" :class="{'bg-green-400 text-gray-900': form.potential === opt && opt === 'high', 'bg-yellow-400 text-gray-900': form.potential === opt && opt === 'medium', 'bg-red-400 text-gray-900': form.potential === opt && opt === 'low', 'bg-gray-700 text-gray-300': form.potential !== opt}" x-text="opt"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Willing to Receive Follow-up <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.followUp = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.followUp === 'Yes' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.followUp = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.followUp === 'No' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">No</button>
            </div>
          </div>
          <div x-show="form.followUp === 'Yes'">
            <label class="text-gray-400 text-xs mb-1 block">Preferred Callback Time</label>
            <input type="text" x-model="form.callbackTime" placeholder="e.g. Weekday mornings" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Additional Notes (internal)</label>
            <textarea x-model="form.notes" rows="3" placeholder="Observations not shared with pharmacy..." class="field-input" style="resize:none"></textarea>
          </div>
        </div>

        {{-- STEP 4: Follow-up --}}
        <div x-show="wizardStep === 4" class="space-y-5">
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Recommended Next Step <span class="text-red-400">*</span></label>
            <div class="space-y-2">
              <template x-for="opt in [{v:'sales_rep',l:'Schedule Sales Rep Visit'},{v:'spotter_followup',l:'Spotter Follow-up Visit'},{v:'owner_absent',l:'Owner Absent — Return Visit'},{v:'no_action',l:'No Further Action'}]" :key="opt.v">
                <button @click="form.nextStep = opt.v" class="w-full text-left px-4 py-3 rounded-xl font-medium text-sm transition-all" :class="form.nextStep === opt.v ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'" x-text="opt.l"></button>
              </template>
            </div>
          </div>
          <div x-show="form.nextStep && form.nextStep !== 'no_action'">
            <label class="text-gray-400 text-xs mb-1 block">Follow-up Date <span class="text-red-400">*</span></label>
            <input type="date" x-model="form.followUpDate" :min="today()" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Notes for Sales Rep</label>
            <textarea x-model="form.repNotes" rows="3" placeholder="Any additional context for the sales rep..." class="field-input" style="resize:none"></textarea>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Brochure and Card Left <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.brochure = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.brochure === 'Yes' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.brochure = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.brochure === 'No' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-300'">No</button>
            </div>
          </div>
        </div>

        {{-- STEP 5: Review & Submit --}}
        <div x-show="wizardStep === 5" class="space-y-4">
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-white font-bold text-lg" x-text="form.pharmacy || 'Pharmacy'"></h3>
              <div x-show="photoPreview" class="w-12 h-12 rounded-xl overflow-hidden border border-gray-600"><img :src="photoPreview" class="w-full h-full object-cover"></div>
            </div>
            <div class="text-gray-400 text-sm" x-text="`${form.town}, ${profile.ward}`"></div>
            <div x-show="gpsCoords" class="text-gray-500 text-xs font-mono" x-text="gpsCoords ? `${gpsCoords.lat}, ${gpsCoords.lng}` : ''"></div>
          </div>
          <template x-for="section in reviewSections()" :key="section.step">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
              <div class="flex items-center justify-between mb-3">
                <span class="text-gray-400 text-xs uppercase tracking-widest" x-text="section.label"></span>
                <button @click="wizardStep = section.step" class="text-yellow-400 text-xs">Edit</button>
              </div>
              <div class="space-y-1">
                <template x-for="field in section.fields" :key="field.label">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500" x-text="field.label"></span>
                    <span class="text-white text-right max-w-[60%]" :class="!field.value ? 'text-red-400' : ''" x-text="field.value || 'Missing'"></span>
                  </div>
                </template>
              </div>
            </div>
          </template>
          <div x-show="submitSuccess" class="bg-green-400/10 border border-green-400/30 rounded-2xl p-6 text-center">
            <div class="text-green-400 text-4xl mb-2">✓</div>
            <div class="text-white font-bold">Submitted!</div>
            <div class="text-gray-400 text-sm">Pharmacy logged successfully.</div>
          </div>
          <button x-show="!submitSuccess" @click="handleSubmitForm" :disabled="!canSubmit() || submitting" class="w-full bg-yellow-400 text-gray-900 font-bold py-4 rounded-2xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <span x-show="submitting" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
            <span x-text="submitting ? 'Saving...' : 'Submit Pharmacy'"></span>
          </button>
          <button x-show="!submitSuccess" @click="saveDraft" class="w-full border border-gray-600 text-gray-300 py-3 rounded-2xl text-sm">Save as Draft</button>
        </div>

        {{-- Wizard nav buttons --}}
        <div class="flex gap-3 mt-6 pb-6" x-show="!submitSuccess">
          <button x-show="wizardStep > 1" @click="wizardStep--" class="flex-1 border border-gray-600 text-gray-300 py-3 rounded-xl">← Back</button>
          <button x-show="wizardStep < 5" @click="validateAndNext" class="flex-1 bg-yellow-400 text-gray-900 font-bold py-3 rounded-xl">Next →</button>
          <button x-show="wizardStep < 5" @click="saveDraft" class="border border-gray-700 text-gray-500 px-4 py-3 rounded-xl text-sm">Draft</button>
        </div>
      </div>

      {{-- ─── SUBMISSIONS LIST ─── --}}
      <div x-show="currentView === 'submissions'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['all','submitted','held','accepted','draft']" :key="tab">
            <button @click="submissionFilter = tab" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all" :class="submissionFilter === tab ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
          </template>
        </div>
        <div x-show="filteredSubmissions().length === 0" class="text-center py-16">
          <p class="text-gray-500">No submissions yet</p>
          <button @click="navigate('submit')" class="text-yellow-400 text-sm mt-2">Start your first submission</button>
        </div>
        <template x-for="sub in filteredSubmissions()" :key="sub.localId">
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 mb-3">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="text-white font-medium" x-text="sub.pharmacy"></div>
                <div class="text-gray-400 text-sm" x-text="`${sub.town} · ${sub.ward}`"></div>
                <div class="text-gray-500 text-xs mt-1" x-text="sub.date"></div>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{'bg-yellow-400/10 text-yellow-400': sub.status === 'submitted', 'bg-orange-400/10 text-orange-400': sub.status === 'held', 'bg-green-400/10 text-green-400': sub.status === 'accepted', 'bg-red-400/10 text-red-400': sub.status === 'rejected', 'bg-gray-700 text-gray-400': sub.status === 'draft'}" x-text="sub.status === 'held' ? 'Held for Review' : sub.status"></span>
                <div class="w-2 h-2 rounded-full" :class="{'bg-green-400': sub.syncStatus === 'synced', 'bg-yellow-400': sub.syncStatus === 'pending', 'bg-red-400': sub.syncStatus === 'failed'}"></div>
              </div>
            </div>
          </div>
        </template>
      </div>

      {{-- ─── TASKS LIST ─── --}}
      <div x-show="currentView === 'tasks'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['open','overdue','completed']" :key="tab">
            <button @click="taskFilter = tab" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all" :class="taskFilter === tab ? 'bg-yellow-400 text-gray-900' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
          </template>
        </div>
        <div x-show="filteredTasks().length === 0" class="text-center py-16"><p class="text-gray-500">No tasks</p></div>
        <template x-for="task in filteredTasks()" :key="task.id">
          <div class="bg-gray-800 rounded-2xl p-4 mb-3 border" :class="{'border-red-400/50': task.status === 'overdue', 'border-gray-700': task.status === 'open', 'border-green-400/30': task.status === 'completed'}">
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1">
                <div class="text-white font-medium" x-text="task.pharmacyName"></div>
                <div class="text-gray-400 text-sm" x-text="nextStepLabel(task.nextStep)"></div>
                <div class="text-sm mt-1" :class="task.status === 'overdue' ? 'text-red-400' : 'text-gray-500'" x-text="`Due: ${task.followUpDate}${task.status === 'overdue' ? ` (${daysOverdue(task.followUpDate)} days overdue)` : ''}`"></div>
              </div>
              <span class="text-xs px-2 py-0.5 rounded-full" :class="{'bg-yellow-400/10 text-yellow-400': task.status === 'open', 'bg-red-400/10 text-red-400': task.status === 'overdue', 'bg-green-400/10 text-green-400': task.status === 'completed'}" x-text="task.status"></span>
            </div>
            <template x-if="task.status !== 'completed'">
              <div>
                <template x-if="completingTaskId !== task.id">
                  <button @click="completingTaskId = task.id" class="text-xs border border-gray-600 text-gray-300 px-3 py-1.5 rounded-lg">Mark Complete</button>
                </template>
                <template x-if="completingTaskId === task.id">
                  <div class="mt-2 space-y-2">
                    <textarea x-model="completionNote" rows="2" placeholder="Outcome note (optional)..." class="field-input" style="resize:none"></textarea>
                    <div class="flex gap-2">
                      <button @click="completingTaskId = null; completionNote = ''" class="flex-1 border border-gray-600 text-gray-400 py-2 rounded-lg text-sm">Cancel</button>
                      <button @click="confirmComplete(task)" class="flex-1 bg-green-400 text-gray-900 font-bold py-2 rounded-lg text-sm">Confirm</button>
                    </div>
                  </div>
                </template>
              </div>
            </template>
          </div>
        </template>
      </div>

      {{-- ─── SETTINGS SCREEN ─── --}}
      <div x-show="currentView === 'settings'" class="px-4 py-6 space-y-4">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl divide-y divide-gray-700">
          <div class="px-4 py-3"><div class="text-gray-400 text-xs uppercase tracking-widest">Profile</div></div>
          <template x-for="row in [{label:'Name',value:profile.name},{label:'ID',value:profile.id},{label:'County',value:profile.county},{label:'Ward',value:profile.ward},{label:'Sales Rep',value:profile.salesRep||'—'}]" :key="row.label">
            <div class="flex justify-between items-center px-4 py-3"><span class="text-gray-400 text-sm" x-text="row.label"></span><span class="text-white text-sm" x-text="row.value"></span></div>
          </template>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl divide-y divide-gray-700">
          <div class="px-4 py-3"><div class="text-gray-400 text-xs uppercase tracking-widest">Sync</div></div>
          <div class="flex justify-between items-center px-4 py-3">
            <span class="text-gray-400 text-sm">Status</span>
            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full" :class="online ? 'bg-green-400' : 'bg-gray-600'"></div><span class="text-white text-sm" x-text="online ? 'Online' : 'Offline'"></span></div>
          </div>
          <div class="flex justify-between items-center px-4 py-3"><span class="text-gray-400 text-sm">Pending Records</span><span class="text-white text-sm" x-text="pendingCount"></span></div>
          <div class="px-4 py-3">
            <button @click="handleSync" :disabled="!online || syncing || pendingCount === 0" class="bg-yellow-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
              <span x-show="syncing" class="w-3 h-3 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
              <span x-text="syncing ? 'Syncing...' : 'Sync Now'"></span>
            </button>
          </div>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl divide-y divide-gray-700">
          <div class="px-4 py-3"><div class="text-gray-400 text-xs uppercase tracking-widest">App</div></div>
          <div class="flex justify-between items-center px-4 py-3"><span class="text-gray-400 text-sm">Version</span><span class="text-white text-sm">1.0.0</span></div>
          <div class="px-4 py-3">
            <template x-if="!confirmClearData"><button @click="confirmClearData = true" class="text-red-400 text-sm">Clear Local Data</button></template>
            <template x-if="confirmClearData">
              <div class="space-y-2">
                <p class="text-gray-300 text-sm">This will remove all local data and deactivate this device.</p>
                <div class="flex gap-3">
                  <button @click="confirmClearData = false" class="flex-1 border border-gray-600 text-gray-400 py-2 rounded-lg text-sm">Cancel</button>
                  <button @click="clearAllData" class="flex-1 bg-red-500 text-white font-bold py-2 rounded-lg text-sm">Clear Everything</button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

    </div>

    {{-- BOTTOM NAV --}}
    <div x-show="currentView !== 'submit'" class="bg-gray-800 border-t border-gray-700 flex-shrink-0 pb-safe">
      <div class="flex">
        <template x-for="item in [{view:'home',label:'Home',icon:'home'},{view:'submit',label:'Submit',icon:'plus'},{view:'tasks',label:'Tasks',icon:'check'},{view:'settings',label:'Settings',icon:'gear'}]" :key="item.view">
          <button @click="navigate(item.view)" class="flex-1 flex flex-col items-center py-3 gap-1 transition-colors" :class="currentView === item.view ? 'text-yellow-400' : 'text-gray-600'">
            <svg x-show="item.icon === 'home'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <svg x-show="item.icon === 'plus'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <div x-show="item.icon === 'check'" class="relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
              <div x-show="overdueCount() > 0" class="absolute -top-1 -right-1 w-2 h-2 bg-red-400 rounded-full"></div>
            </div>
            <svg x-show="item.icon === 'gear'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="text-xs" x-text="item.label"></span>
          </button>
        </template>
      </div>
    </div>

  </div>

  <script src="/spotter-assets/js/db.js"></script>
  <script src="/spotter-assets/js/duplicate.js"></script>
  <script src="/spotter-assets/js/camera.js"></script>
  <script src="/spotter-assets/js/gps.js"></script>
  <script src="/spotter-assets/js/sync.js"></script>
  <script src="/spotter-assets/js/app.js"></script>

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw-spotter.js')
          .catch(err => console.warn('SW registration failed:', err));
      });
    }
  </script>
</body>
</html>
