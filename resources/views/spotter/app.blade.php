<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#facc15">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Spotter">
  <meta name="api-base" content="{{ rtrim(request()->getSchemeAndHttpHost(), '/') }}/api/v1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="manifest" href="/spotter/manifest.json">
  <link rel="apple-touch-icon" href="/spotter/icon-192.png">
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

      {{-- Tab switcher --}}
      <div class="flex bg-gray-800 border border-gray-700 rounded-xl p-1 mb-6">
        <button @click="loginTab = 'spotter'" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all" :class="loginTab === 'spotter' ? 'bg-yellow-400 text-white' : 'text-gray-400'">Field Agent</button>
        <button @click="loginTab = 'supervisor'" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all" :class="loginTab === 'supervisor' ? 'bg-yellow-400 text-white' : 'text-gray-400'">Supervisor</button>
      </div>

      {{-- SPOTTER TAB --}}
      <div x-show="loginTab === 'spotter'" class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
        <h2 class="text-white text-lg font-medium mb-1">Enter Activation Code</h2>
        <p class="text-gray-400 text-sm mb-6">Provided by your Sales Rep or Admin</p>
        <input type="text" x-model="activationCode" @input="formatActivationCode" @keydown.enter="handleActivate" placeholder="XXXX-XXXX-XXXX" maxlength="14" class="w-full bg-gray-900 border border-gray-700 text-white text-center text-xl tracking-widest rounded-xl px-4 py-3 focus:border-yellow-400 focus:outline-none uppercase" :class="activationError ? 'border-gray-700' : ''" autocomplete="off" spellcheck="false">
        <p x-show="activationError" x-text="activationError" class="text-red-400 text-sm mt-2 text-center"></p>
        <button @click="handleActivate" :disabled="activationCode.replace(/-/g,'').length < 12 || activating" class="w-full mt-4 bg-yellow-400 text-white font-bold py-3 rounded-xl disabled:opacity-40 flex items-center justify-center gap-2">
          <span x-show="activating" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
          <span x-text="activating ? 'Activating...' : 'Activate Device'"></span>
        </button>
      </div>

      {{-- SUPERVISOR TAB --}}
      <div x-show="loginTab === 'supervisor'" class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
        <h2 class="text-white text-lg font-medium mb-1">Supervisor Login</h2>
        <p class="text-gray-400 text-sm mb-6">Sales Rep · County Coordinator · Admin</p>
        <div class="space-y-4">
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Email</label>
            <input type="email" x-model="supervisorEmail" @keydown.enter="handleSupervisorLogin" placeholder="you@example.com" autocomplete="email" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:border-yellow-400 focus:outline-none" :class="supervisorError ? 'border-gray-700' : ''">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Password</label>
            <input type="password" x-model="supervisorPassword" @keydown.enter="handleSupervisorLogin" placeholder="••••••••" autocomplete="current-password" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:border-yellow-400 focus:outline-none" :class="supervisorError ? 'border-gray-700' : ''">
          </div>
        </div>
        <p x-show="supervisorError" x-text="supervisorError" class="text-red-400 text-sm mt-3 text-center"></p>
        <button @click="handleSupervisorLogin" :disabled="!supervisorEmail || !supervisorPassword || supervisorLoggingIn" class="w-full mt-4 bg-yellow-400 text-white font-bold py-3 rounded-xl disabled:opacity-40 flex items-center justify-center gap-2">
          <span x-show="supervisorLoggingIn" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
          <span x-text="supervisorLoggingIn ? 'Signing in...' : 'Sign In'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- MAIN APP (post-activation)                              --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div x-show="activated" class="flex min-h-screen min-h-dvh">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR                                                 --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 border-r border-gray-700 flex flex-col transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      {{-- Logo + user info --}}
      <div class="px-4 py-5 border-b border-gray-700 flex-shrink-0">
        <div class="text-yellow-400 font-bold text-sm tracking-widest">DAWA MTAANI</div>
        <div class="text-gray-500 text-xs mt-0.5" x-text="isSupervisor() ? (profile.role || 'Supervisor').replace(/_/g,' ') : 'Field Agent'"></div>
        <div class="text-gray-300 text-sm mt-2 font-medium" x-text="profile.name || 'Activating...'"></div>
      </div>

      {{-- Nav items --}}
      <nav class="flex-1 overflow-y-auto py-3 space-y-0.5 px-2">

        {{-- SPOTTER NAV --}}
        <template x-if="!isSupervisor()">
          <div class="space-y-0.5">
            <button @click="navigate('home'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'home' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              <span>Home</span>
            </button>
            <button @click="clockedIn ? (navigate('submit'), sidebarOpen = false) : showClockInPrompt = true" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'submit' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : clockedIn ? 'text-gray-400 hover:text-white hover:bg-gray-700/50' : 'text-gray-300 cursor-not-allowed'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              <span>New Visit</span>
              <svg x-show="!clockedIn" class="w-3 h-3 ml-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </button>
            <button @click="navigate('submissions'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'submissions' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <span>My Visits</span>
            </button>
            <button @click="navigate('tasks'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'tasks' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 9l2 2 4-4"/></svg>
              <span>Follow-ups</span>
              <span x-show="overdueCount() > 0" class="ml-auto bg-red-400 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" x-text="overdueCount()"></span>
            </button>
            <button @click="navigate('ward'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'ward' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Co-Ward</span>
            </button>
            <button @click="navigate('settings'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'settings' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Settings</span>
            </button>
          </div>
        </template>

        {{-- SUPERVISOR NAV --}}
        <template x-if="isSupervisor()">
          <div class="space-y-0.5">
            <button @click="navigate('supervisor_dashboard'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_dashboard' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
              <span>Dashboard</span>
            </button>
            <button @click="navigate('supervisor_submissions'); loadSupervisorSubmissions(); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_submissions' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <span>Submissions</span>
            </button>
            <button @click="navigate('supervisor_duplicates'); loadSupervisorDuplicates(); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_duplicates' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span>Reviews</span>
              <span x-show="(supervisorStats.pendingDuplicates ?? 0) > 0" class="ml-auto bg-orange-400 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" x-text="supervisorStats.pendingDuplicates"></span>
            </button>
            <button @click="navigate('supervisor_followups'); loadSupervisorFollowUps(); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_followups' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 9l2 2 4-4"/></svg>
              <span>Follow-ups</span>
              <span x-show="(supervisorStats.overdueFollowUps ?? 0) > 0" class="ml-auto bg-red-400 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" x-text="supervisorStats.overdueFollowUps"></span>
            </button>
            <button @click="navigate('supervisor_attendance'); loadSupervisorAttendance(); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_attendance' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Attendance</span>
            </button>
            <button @click="navigate('supervisor_leaderboard'); loadLeaderboard(); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'supervisor_leaderboard' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
              <span>Leaderboard</span>
            </button>
            <button @click="navigate('settings'); sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all" :class="currentView === 'settings' ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50'">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <span>Account</span>
            </button>
          </div>
        </template>

      </nav>

      {{-- Sidebar bottom: Sign Out with inline confirmation --}}
      <div class="px-3 py-4 border-t border-gray-700 flex-shrink-0" x-data="{ confirmLogout: false }">
        <template x-if="!confirmLogout">
          <button @click="confirmLogout = true"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-400/10 transition-all">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span x-text="isSupervisor() ? 'Sign Out' : 'Log Out'"></span>
          </button>
        </template>
        <template x-if="confirmLogout">
          <div class="space-y-2">
            <p class="text-gray-300 text-xs px-1" x-text="isSupervisor() ? 'Sign out of your supervisor session?' : 'This will log you out and clear all local data. You will need a new activation code to sign back in.'"></p>
            <div class="flex gap-2">
              <button @click="confirmLogout = false" class="flex-1 border border-gray-600 text-gray-400 py-2 rounded-lg text-xs">Cancel</button>
              <button @click="clearAllData()" class="flex-1 bg-red-500 text-white font-bold py-2 rounded-lg text-xs">Log Out</button>
            </div>
          </div>
        </template>
      </div>
    </aside>

    {{-- MOBILE OVERLAY --}}
    <div
      x-show="sidebarOpen"
      x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/60 lg:hidden"
      style="display:none"
    ></div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT                                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-screen min-h-dvh">

    {{-- TOP BAR --}}
    <div class="bg-gray-800 border-b border-gray-700 px-4 py-3 flex items-center justify-between flex-shrink-0 pt-safe">
      <div class="flex items-center gap-2">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1 text-gray-400 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <button x-show="currentView !== 'home'" @click="goBack" class="text-gray-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span class="text-yellow-400 font-bold text-sm tracking-widest" x-text="viewTitle()"></span>
      </div>
      <div class="flex items-center gap-2">
        <span x-show="syncing" class="w-3 h-3 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
      </div>
    </div>

    {{-- SYNC MESSAGE BANNER --}}
    <div x-show="syncMsg" x-transition class="bg-gray-700 px-4 py-2 text-sm text-gray-300 text-center flex-shrink-0" x-text="syncMsg"></div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex-1 overflow-y-auto no-scrollbar">

      {{-- ─── HOME SCREEN ─── --}}
      <div x-show="currentView === 'home'" class="px-4 py-6 space-y-4">

        {{-- User status card --}}
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-yellow-400/20 border border-yellow-400/30 flex items-center justify-center flex-shrink-0">
                <span class="text-yellow-400 font-bold text-sm" x-text="profile.name ? profile.name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase() : 'SP'"></span>
              </div>
              <div>
                <div class="text-white font-semibold text-base" x-text="profile.name || 'Spotter'"></div>
                <div class="text-gray-400 text-xs" x-text="profile.id ? 'ID: ' + profile.id : ''"></div>
              </div>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full border" :class="online ? 'bg-green-400/10 border-gray-700/30' : 'bg-gray-700 border-gray-600'">
              <div class="w-2 h-2 rounded-full" :class="online ? 'bg-green-400' : 'bg-gray-500'"></div>
              <span class="text-xs font-medium" :class="online ? 'text-green-400' : 'text-gray-400'" x-text="online ? 'Online' : 'Offline'"></span>
            </div>
          </div>
          <div class="border-t border-gray-700 mb-3"></div>
          <div class="grid grid-cols-2 gap-3">
            <div><div class="text-gray-500 text-xs mb-0.5">County</div><div class="text-white text-sm font-medium" x-text="profile.county || '—'"></div></div>
            <div><div class="text-gray-500 text-xs mb-0.5">Ward</div><div class="text-white text-sm font-medium" x-text="profile.ward || '—'"></div></div>
            <div x-show="profile.salesRep"><div class="text-gray-500 text-xs mb-0.5">Sales Rep</div><div class="text-white text-sm font-medium" x-text="profile.salesRep"></div></div>
            <div x-show="profile.role && profile.role !== 'spotter'"><div class="text-gray-500 text-xs mb-0.5">Role</div><div class="text-yellow-400 text-sm font-medium capitalize" x-text="profile.role?.replace(/_/g,' ')"></div></div>
          </div>
          <div class="mt-3 pt-3 border-t border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span x-show="syncing" class="w-3 h-3 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></span>
              <span class="text-xs text-gray-500" x-text="syncing ? 'Syncing...' : (pendingCount > 0 ? pendingCount + ' records pending' : 'All synced')"></span>
            </div>
            <button x-show="pendingCount > 0 && online && !syncing" @click="handleSync" class="text-xs text-yellow-400 border border-yellow-400/30 rounded-lg px-2.5 py-1 hover:bg-yellow-400/10 transition-all">Sync now</button>
            <span x-show="!syncing && pendingCount === 0" class="text-xs text-green-400">&#10003; Up to date</span>
          </div>
        </div>

        {{-- Clock status card --}}
        <div class="bg-gray-800 border rounded-2xl p-4" :class="clockedIn ? 'border-gray-700/30' : 'border-yellow-400/30'">
          <template x-if="!clockedIn">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 110 20 10 10 0 010-20z"/></svg>
                <span class="text-white font-medium">Clock in to begin your day</span>
              </div>
              <p class="text-gray-400 text-sm mb-3">Submissions are locked until you clock in</p>
              <button @click="navigate('clock')" class="bg-yellow-400 text-white font-bold px-4 py-2 rounded-lg text-sm">Clock In Now</button>
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
            <button @click="clockedIn ? navigate('submit') : showClockInPrompt = true" class="w-full py-4 rounded-2xl font-bold text-center transition-all" :class="clockedIn ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-500 cursor-not-allowed opacity-50'">+ New Submission</button>
            <div x-show="showClockInPrompt" x-transition class="mt-2 bg-yellow-400/10 border border-yellow-400/30 rounded-xl px-4 py-3">
              <p class="text-yellow-400 text-sm font-medium">You must clock in before submitting a visit.</p>
              <button @click="navigate('clock'); showClockInPrompt = false" class="text-yellow-400 text-xs underline mt-1">Clock in now →</button>
              <button @click="showClockInPrompt = false" class="text-gray-500 text-xs ml-3">Dismiss</button>
            </div>
            <button @click="navigate('submissions')" class="w-full py-4 rounded-2xl font-bold text-center bg-gray-800 border border-gray-700 text-white">My Submissions</button>
          </div>
        </div>

        {{-- Overdue alerts --}}
        <div x-show="overdueCount() > 0">
          <div class="text-red-400 text-xs uppercase tracking-widest mb-2">Overdue Follow-ups</div>
          <template x-for="task in overdueFollowups().slice(0,3)" :key="task.id">
            <div class="bg-gray-800 border border-gray-700/30 rounded-xl p-3 mb-2">
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
              <button @click="handleClockIn" :disabled="clockingIn" class="w-full bg-yellow-400 text-white font-bold py-4 rounded-xl disabled:opacity-50 flex items-center justify-center gap-2">
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
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all" :class="{'bg-yellow-400 text-white': wizardStep === n, 'bg-green-400 text-white': wizardStep > n, 'bg-gray-700 text-gray-500': wizardStep < n}" x-text="wizardStep > n ? '✓' : n"></div>
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
            <label class="text-gray-400 text-xs mb-1 block">Nearest Landmark</label>
            <input type="text" x-model="form.landmark" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Date of Visit</label>
            <input type="text" :value="today()" readonly class="field-input cursor-not-allowed" style="color:#6b7280">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Pharmacy Name <span class="text-red-400">*</span></label>
            <input type="text" x-model="form.pharmacy" @blur="checkDuplicate" class="field-input" :style="formErrors.pharmacy ? 'border-color:#f87171' : ''">
            <p x-show="formErrors.pharmacy" class="text-red-400 text-xs mt-1" x-text="formErrors.pharmacy"></p>
            <div x-show="duplicateWarning" class="mt-2 border rounded-lg px-3 py-2" :class="duplicateBlock ? 'bg-red-400/10 border-gray-700/30' : 'bg-yellow-400/10 border-yellow-400/30'">
              <p class="text-sm" :class="duplicateBlock ? 'text-red-400' : 'text-yellow-400'" x-text="duplicateWarning"></p>
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
                  <button @click="getGPS" :disabled="gettingGPS" class="bg-yellow-400 text-white font-bold px-3 py-1.5 rounded-lg text-sm disabled:opacity-50">
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
            <p x-show="form.photoData" class="text-gray-500 text-xs mt-1" x-text="photoSizeKB() + 'KB'"></p>
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
            <input type="tel" x-model="form.ownerPhone" class="field-input" placeholder="07XXXXXXXX" pattern="^(\+?254|0)[17]\d{8}$" inputmode="tel">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Pharmacy Phone</label>
            <input type="tel" x-model="form.pharmacyPhone" class="field-input" placeholder="07XXXXXXXX" pattern="^(\+?254|0)[17]\d{8}$" inputmode="tel">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Email</label>
            <input type="email" x-model="form.ownerEmail" class="field-input">
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Owner Present at Visit <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.ownerPresent = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.ownerPresent === 'Yes' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.ownerPresent = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.ownerPresent === 'No' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">No</button>
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
                <button @click="form.footTraffic = opt" class="py-3 rounded-xl font-medium capitalize transition-all" :class="form.footTraffic === opt ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'" x-text="opt"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Shelf Stock Observed <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-2 gap-2">
              <template x-for="opt in [{v:'well_stocked',l:'Well Stocked'},{v:'moderate',l:'Moderate'},{v:'sparse',l:'Sparse'},{v:'not_observed',l:'Could Not Observe'}]" :key="opt.v">
                <button @click="form.stockLevel = opt.v" class="py-3 rounded-xl font-medium text-sm transition-all" :class="form.stockLevel === opt.v ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'" x-text="opt.l"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Activation Potential <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="opt in ['high','medium','low']" :key="opt">
                <button @click="form.potential = opt" class="py-3 rounded-xl font-medium capitalize transition-all" :class="{'bg-green-400 text-white': form.potential === opt && opt === 'high', 'bg-yellow-400 text-white': form.potential === opt && opt === 'medium', 'bg-red-400 text-white': form.potential === opt && opt === 'low', 'bg-gray-700 text-gray-300': form.potential !== opt}" x-text="opt"></button>
              </template>
            </div>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Willing to Receive Follow-up <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.followUp = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.followUp === 'Yes' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.followUp = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.followUp === 'No' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">No</button>
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
                <button @click="form.nextStep = opt.v" class="w-full text-left px-4 py-3 rounded-xl font-medium text-sm transition-all" :class="form.nextStep === opt.v ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'" x-text="opt.l"></button>
              </template>
            </div>
          </div>
          <div x-show="form.nextStep && form.nextStep !== 'no_action'">
            <label class="text-gray-400 text-xs mb-1 block">Follow-up Date <span class="text-red-400">*</span></label>
            <input type="date" x-model="form.followUpDate" :min="today()" class="field-input">
            <p class="text-gray-500 text-xs mt-1">Sales Rep is alerted if no update within 48 hours of this date.</p>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-1 block">Notes for Sales Rep</label>
            <textarea x-model="form.repNotes" rows="3" placeholder="Any additional context for the sales rep..." class="field-input" style="resize:none"></textarea>
          </div>
          <div>
            <label class="text-gray-400 text-xs mb-2 block">Brochure and Card Left <span class="text-red-400">*</span></label>
            <div class="flex gap-3">
              <button @click="form.brochure = 'Yes'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.brochure === 'Yes' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">Yes</button>
              <button @click="form.brochure = 'No'" class="flex-1 py-3 rounded-xl font-medium transition-all" :class="form.brochure === 'No' ? 'bg-yellow-400 text-white' : 'bg-gray-700 text-gray-300'">No</button>
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
          <div x-show="submitSuccess" class="bg-green-400/10 border border-gray-700/30 rounded-2xl p-6 text-center">
            <div class="text-green-400 text-4xl mb-2">✓</div>
            <div class="text-white font-bold">Submitted!</div>
            <div class="text-gray-400 text-sm">Pharmacy logged successfully.</div>
          </div>
          <button x-show="!submitSuccess" @click="handleSubmitForm" :disabled="!canSubmit() || submitting" class="w-full bg-yellow-400 text-white font-bold py-4 rounded-2xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <span x-show="submitting" class="w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
            <span x-text="submitting ? 'Saving...' : 'Submit Pharmacy'"></span>
          </button>
          <button x-show="!submitSuccess" @click="saveDraft" class="w-full border border-gray-600 text-gray-300 py-3 rounded-2xl text-sm">Save as Draft</button>
        </div>

        {{-- Wizard nav buttons --}}
        <div class="flex gap-3 mt-6 pb-6" x-show="!submitSuccess">
          <button x-show="wizardStep > 1" @click="wizardStep--" class="flex-1 border border-gray-600 text-gray-300 py-3 rounded-xl">← Back</button>
          <button x-show="wizardStep < 5" @click="validateAndNext" class="flex-1 bg-yellow-400 text-white font-bold py-3 rounded-xl">Next →</button>
          <button x-show="wizardStep < 5" @click="saveDraft" class="border border-gray-700 text-gray-500 px-4 py-3 rounded-xl text-sm">Draft</button>
        </div>
      </div>

      {{-- ─── SUBMISSIONS LIST ─── --}}
      <div x-show="currentView === 'submissions'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['all','submitted','held','sr_reviewed','cc_verified','accepted','rejected','draft']" :key="tab">
            <button @click="submissionFilter = tab" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all" :class="submissionFilter === tab ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
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
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{
                  'bg-yellow-400/10 text-yellow-400': sub.status === 'submitted',
                  'bg-orange-400/10 text-orange-400': sub.status === 'held',
                  'bg-blue-400/10 text-blue-400': sub.status === 'sr_reviewed',
                  'bg-purple-400/10 text-purple-400': sub.status === 'cc_verified',
                  'bg-green-400/10 text-green-400': sub.status === 'accepted',
                  'bg-red-400/10 text-red-400': sub.status === 'rejected',
                  'bg-gray-700 text-gray-400': sub.status === 'draft',
                }" x-text="sub.status === 'held' ? 'Held for Review' : sub.status === 'sr_reviewed' ? 'SR Reviewed' : sub.status === 'cc_verified' ? 'CC Verified' : sub.status"></span>
                <div class="w-2 h-2 rounded-full" :class="{'bg-green-400': sub.syncStatus === 'synced', 'bg-yellow-400': sub.syncStatus === 'pending', 'bg-red-400': sub.syncStatus === 'failed'}"></div>
              </div>
            </div>
            <p x-show="heldStatusLabel(sub.status)" class="text-xs text-gray-500 mt-1" x-text="heldStatusLabel(sub.status)"></p>
          </div>
        </template>
      </div>

      {{-- ─── TASKS LIST ─── --}}
      <div x-show="currentView === 'tasks'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['open','overdue','completed']" :key="tab">
            <button @click="taskFilter = tab" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all" :class="taskFilter === tab ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
          </template>
        </div>
        <div x-show="filteredTasks().length === 0" class="text-center py-16"><p class="text-gray-500">No tasks</p></div>
        <template x-for="task in filteredTasks()" :key="task.id">
          <div class="bg-gray-800 rounded-2xl p-4 mb-3 border" :class="{'border-gray-700/50': task.status === 'overdue', 'border-gray-700': task.status === 'open', 'border-gray-700/30': task.status === 'completed'}">
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
                      <button @click="confirmComplete(task)" class="flex-1 bg-green-400 text-white font-bold py-2 rounded-lg text-sm">Confirm</button>
                    </div>
                  </div>
                </template>
              </div>
            </template>
          </div>
        </template>
      </div>

      {{-- ─── WARD SUBMISSIONS ─── --}}
      <div x-show="currentView === 'ward'" class="px-4 py-6">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
          <div class="text-gray-400 text-xs uppercase tracking-widest mb-4">Co-Ward Submissions</div>
          <div x-show="!online" class="text-gray-500 text-sm text-center py-8">Available when online</div>
          <div x-show="online && wardSubmissions.length === 0" class="text-gray-500 text-sm text-center py-8">No co-ward submissions yet</div>
          <template x-for="sub in wardSubmissions" :key="sub.id">
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-3 mb-2">
              <div class="text-white font-medium" x-text="sub.pharmacy"></div>
              <div class="text-gray-400 text-sm" x-text="sub.town"></div>
              <div class="text-gray-500 text-xs mt-1" x-text="sub.address"></div>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs" :class="{
                  'text-green-400': sub.potential === 'high',
                  'text-yellow-400': sub.potential === 'medium',
                  'text-red-400': sub.potential === 'low'
                }" x-text="(sub.potential || '') + ' potential'"></span>
                <span class="text-gray-500 text-xs" x-text="sub.next_step ? sub.next_step.replace(/_/g,' ') : ''"></span>
              </div>
            </div>
          </template>
        </div>
      </div>

      {{-- ─── SUPERVISOR DASHBOARD ─── --}}
      <div x-show="currentView === 'supervisor_dashboard'" class="px-4 py-6 space-y-4">
        {{-- Supervisor user status card --}}
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-yellow-400/20 border border-yellow-400/30 flex items-center justify-center flex-shrink-0">
                <span class="text-yellow-400 font-bold text-sm" x-text="profile.name ? profile.name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase() : 'SV'"></span>
              </div>
              <div>
                <div class="text-white font-semibold text-base" x-text="profile.name || 'Supervisor'"></div>
                <div class="text-yellow-400 text-xs capitalize" x-text="profile.role?.replace(/_/g,' ')"></div>
              </div>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full border" :class="online ? 'bg-green-400/10 border-gray-700/30' : 'bg-gray-700 border-gray-600'">
              <div class="w-2 h-2 rounded-full" :class="online ? 'bg-green-400' : 'bg-gray-500'"></div>
              <span class="text-xs font-medium" :class="online ? 'text-green-400' : 'text-gray-400'" x-text="online ? 'Online' : 'Offline'"></span>
            </div>
          </div>
          <div class="border-t border-gray-700 pt-3">
            <div class="text-gray-400 text-sm" x-text="supervisorScopeLabel()"></div>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4"><div class="text-yellow-400 font-bold text-2xl" x-text="supervisorStats.submissionsToday ?? '—'"></div><div class="text-gray-400 text-xs mt-1">Submissions Today</div></div>
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4"><div class="text-white font-bold text-2xl" x-text="supervisorStats.totalSubmissions ?? '—'"></div><div class="text-gray-400 text-xs mt-1">Total Submitted</div></div>
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4"><div class="font-bold text-2xl" :class="(supervisorStats.pendingDuplicates ?? 0) > 0 ? 'text-orange-400' : 'text-white'" x-text="supervisorStats.pendingDuplicates ?? '—'"></div><div class="text-gray-400 text-xs mt-1">Pending Reviews</div></div>
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4"><div class="font-bold text-2xl" :class="(supervisorStats.overdueFollowUps ?? 0) > 0 ? 'text-red-400' : 'text-white'" x-text="supervisorStats.overdueFollowUps ?? '—'"></div><div class="text-gray-400 text-xs mt-1">Overdue Follow-ups</div></div>
        </div>
        <div x-show="(supervisorStats.pendingDuplicates ?? 0) > 0 || (supervisorStats.overdueFollowUps ?? 0) > 0" class="bg-gray-800 border border-yellow-400/30 rounded-2xl p-4">
          <div class="text-yellow-400 text-xs uppercase tracking-widest mb-3">Pending Actions</div>
          <button x-show="supervisorStats.pendingDuplicates > 0" @click="navigate('supervisor_duplicates')" class="w-full flex items-center justify-between py-2 border-b border-gray-700"><span class="text-white text-sm">Duplicate Reviews</span><span class="bg-orange-400/10 text-orange-400 text-xs px-2 py-0.5 rounded-full" x-text="supervisorStats.pendingDuplicates"></span></button>
          <button x-show="supervisorStats.overdueFollowUps > 0" @click="navigate('supervisor_followups')" class="w-full flex items-center justify-between py-2"><span class="text-white text-sm">Overdue Follow-ups</span><span class="bg-red-400/10 text-red-400 text-xs px-2 py-0.5 rounded-full" x-text="supervisorStats.overdueFollowUps"></span></button>
        </div>
        <div x-show="profile.role === 'sales_rep' && supervisorSpotters.length > 0">
          <div class="text-gray-400 text-xs uppercase tracking-widest mb-3">My Spotters</div>
          <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
            <template x-for="spotter in supervisorSpotters" :key="spotter.id">
              <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700 last:border-0">
                <div><div class="text-white text-sm font-medium" x-text="spotter.name"></div><div class="text-gray-500 text-xs" x-text="`${spotter.today_submissions} today · ${spotter.total_submissions} total`"></div></div>
                <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full" :class="spotter.clocked_in ? 'bg-green-400' : 'bg-gray-600'"></div><span class="text-xs text-gray-500" x-text="spotter.clocked_in ? 'Active' : 'Offline'"></span></div>
              </div>
            </template>
          </div>
        </div>
      </div>

      {{-- ─── SUPERVISOR SUBMISSIONS ─── --}}
      <div x-show="currentView === 'supervisor_submissions'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['all','submitted','held','accepted','rejected']" :key="tab">
            <button @click="supervisorSubmissionFilter = tab; loadSupervisorSubmissions()" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all" :class="supervisorSubmissionFilter === tab ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab === 'all' ? 'All' : tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
          </template>
        </div>
        <div x-show="supervisorSubmissions.length === 0" class="text-center py-16"><p class="text-gray-500">No submissions found</p></div>
        <template x-for="sub in supervisorSubmissions" :key="sub.id">
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 mb-3">
            <div class="flex items-start justify-between">
              <div class="flex-1"><div class="text-white font-medium" x-text="sub.pharmacy"></div><div class="text-gray-400 text-sm" x-text="`${sub.town || ''} · ${sub.ward || ''}`"></div><div class="text-gray-500 text-xs mt-1" x-text="sub.spotter?.name ?? ''"></div></div>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{'bg-yellow-400/10 text-yellow-400': sub.status === 'submitted', 'bg-orange-400/10 text-orange-400': sub.status === 'held', 'bg-blue-400/10 text-blue-400': sub.status === 'sr_reviewed', 'bg-purple-400/10 text-purple-400': sub.status === 'cc_verified', 'bg-green-400/10 text-green-400': sub.status === 'accepted', 'bg-red-400/10 text-red-400': sub.status === 'rejected', 'bg-gray-700 text-gray-400': sub.status === 'draft'}" x-text="sub.status"></span>
            </div>
          </div>
        </template>
      </div>

      {{-- ─── SUPERVISOR DUPLICATES ─── --}}
      <div x-show="currentView === 'supervisor_duplicates'" class="px-4 py-4">
        <div x-show="supervisorDuplicates.length === 0" class="text-center py-16"><div class="text-gray-500 text-sm">No pending duplicate reviews</div><div class="text-green-400 text-xs mt-2">All clear</div></div>
        <template x-for="review in supervisorDuplicates" :key="review.id">
          <div class="bg-gray-800 border border-gray-700/30 rounded-2xl p-4 mb-4">
            <div class="text-orange-400 text-xs uppercase tracking-widest mb-3">Duplicate Review</div>
            <div class="grid grid-cols-2 gap-3 mb-4">
              <div class="bg-gray-900 rounded-xl p-3"><div class="text-gray-400 text-xs mb-1">Submitted</div><div class="text-white text-sm font-medium" x-text="review.submission?.pharmacy"></div><div class="text-gray-500 text-xs" x-text="review.submission?.ward"></div></div>
              <div class="bg-gray-900 rounded-xl p-3"><div class="text-gray-400 text-xs mb-1">Matched With</div><div class="text-white text-sm font-medium" x-text="review.matched_submission?.pharmacy ?? review.match_name"></div><div class="text-gray-500 text-xs" x-text="review.matched_submission?.ward"></div></div>
            </div>
            <div class="flex gap-3 mb-4">
              <div x-show="review.gps_distance_metres !== null" class="text-xs px-3 py-1 rounded-full" :class="review.gps_distance_metres <= 50 ? 'bg-red-400/10 text-red-400' : 'bg-green-400/10 text-green-400'" x-text="`GPS: ${review.gps_distance_metres}m`"></div>
              <div x-show="review.name_edit_distance !== null" class="bg-gray-700 text-gray-300 text-xs px-3 py-1 rounded-full" x-text="`Name dist: ${review.name_edit_distance}`"></div>
            </div>
            <div x-show="!review._decided">
              <div x-show="!review._confirming" class="flex gap-3">
                <button @click="review._confirming = 'duplicate'" class="flex-1 bg-red-500/20 border border-gray-700/30 text-red-400 font-medium py-2.5 rounded-xl text-sm">Confirm Duplicate</button>
                <button @click="decideDuplicate(review, 'not_duplicate')" class="flex-1 bg-green-400/20 border border-gray-700/30 text-green-400 font-medium py-2.5 rounded-xl text-sm" x-text="profile.role === 'sales_rep' ? 'Not Dup → CC' : profile.role === 'county_coordinator' ? 'Verify → Admin' : 'Accept'"></button>
              </div>
              <div x-show="review._confirming === 'duplicate'" class="space-y-2">
                <textarea x-model="review._notes" rows="2" placeholder="Reason (optional)..." class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm resize-none focus:border-yellow-400 outline-none"></textarea>
                <div class="flex gap-2"><button @click="review._confirming = null" class="flex-1 border border-gray-600 text-gray-400 py-2 rounded-xl text-sm">Cancel</button><button @click="decideDuplicate(review, 'confirmed_duplicate')" class="flex-1 bg-red-500 text-white font-bold py-2 rounded-xl text-sm">Confirm</button></div>
              </div>
            </div>
            <div x-show="review._decided" class="text-center text-green-400 text-sm py-2">Decision recorded</div>
          </div>
        </template>
      </div>

      {{-- ─── SUPERVISOR LEADERBOARD ─── --}}
      <div x-show="currentView === 'supervisor_leaderboard'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="p in [{v:'programme',l:'All Time'},{v:'month',l:'This Month'},{v:'week',l:'This Week'}]" :key="p.v">
            <button @click="leaderboardPeriod = p.v; loadLeaderboard()" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0" :class="leaderboardPeriod === p.v ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="p.l"></button>
          </template>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
          <div class="grid grid-cols-4 gap-2 px-4 py-3 bg-gray-700 text-gray-400 text-xs uppercase tracking-widest"><span>Rank</span><span class="col-span-2">Spotter</span><span class="text-right">Activations</span></div>
          <template x-for="(entry, i) in leaderboardData" :key="i">
            <div class="grid grid-cols-4 gap-2 px-4 py-3 border-t border-gray-700 items-center">
              <span class="font-bold text-sm" :class="{'text-yellow-400': entry.rank === 1, 'text-gray-300': entry.rank === 2, 'text-orange-400': entry.rank === 3, 'text-gray-500': entry.rank > 3}" x-text="entry.rank"></span>
              <span class="col-span-2 text-white text-sm" x-text="entry.name"></span>
              <span class="text-right text-green-400 font-bold" x-text="entry.activations"></span>
            </div>
          </template>
          <div x-show="leaderboardData.length === 0" class="text-center py-8 text-gray-500 text-sm">No data for this period</div>
        </div>
      </div>

      {{-- ─── SUPERVISOR FOLLOW-UPS ─── --}}
      <div x-show="currentView === 'supervisor_followups'" class="px-4 py-4">
        <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
          <template x-for="tab in ['overdue','open','completed']" :key="tab">
            <button @click="supervisorFollowUpFilter = tab; loadSupervisorFollowUps()" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0" :class="supervisorFollowUpFilter === tab ? 'bg-yellow-400 text-white' : 'bg-gray-800 border border-gray-700 text-gray-400'" x-text="tab.charAt(0).toUpperCase() + tab.slice(1)"></button>
          </template>
        </div>
        <template x-for="task in supervisorFollowUps" :key="task.id">
          <div class="bg-gray-800 border rounded-2xl p-4 mb-3" :class="task.status === 'overdue' ? 'border-gray-700/50' : 'border-gray-700'">
            <div class="text-white font-medium" x-text="task.submission?.pharmacy ?? 'Unknown'"></div>
            <div class="text-gray-400 text-sm" x-text="nextStepLabel(task.next_step)"></div>
            <div class="text-sm mt-1" :class="task.status === 'overdue' ? 'text-red-400' : 'text-gray-500'" x-text="`Due: ${task.follow_up_date}${task.status === 'overdue' ? ' · ' + daysOverdue(task.follow_up_date) + ' days overdue' : ''}`"></div>
          </div>
        </template>
        <div x-show="supervisorFollowUps.length === 0" class="text-center py-16 text-gray-500">No follow-ups</div>
      </div>

      {{-- ─── SUPERVISOR ATTENDANCE ─── --}}
      <div x-show="currentView === 'supervisor_attendance'" class="px-4 py-4">
        <template x-for="record in supervisorAttendance" :key="record.id">
          <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 mb-3">
            <div class="flex items-center justify-between mb-2"><span class="text-white font-medium" x-text="record.spotter?.name ?? 'Unknown'"></span></div>
            <div class="text-gray-400 text-sm" x-text="record.date"></div>
            <div class="flex gap-4 mt-2 text-xs text-gray-500"><span x-text="`In: ${formatTime(record.clock_in_at)}`"></span><span x-text="record.clock_out_at ? `Out: ${formatTime(record.clock_out_at)}` : 'Not clocked out'"></span></div>
          </div>
        </template>
        <div x-show="supervisorAttendance.length === 0" class="text-center py-16 text-gray-500">No attendance records</div>
      </div>

      {{-- ─── SETTINGS SCREEN ─── --}}
      <div x-show="currentView === 'settings'" class="px-4 py-6 space-y-4">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl divide-y divide-gray-700">
          <div class="px-4 py-3"><div class="text-gray-400 text-xs uppercase tracking-widest">Profile</div></div>
          <template x-for="row in [{label:'Name',value:profile.name},{label:'Role',value:profile.role ? profile.role.replace(/_/g,' ') : 'Spotter'},{label:'County',value:profile.county},{label:'Ward',value:profile.ward},{label:'Sales Rep',value:profile.salesRep||'—'}]" :key="row.label">
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
            <button @click="handleSync" :disabled="!online || syncing || pendingCount === 0" class="bg-yellow-400 text-white font-bold px-4 py-2 rounded-lg text-sm disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
              <span x-show="syncing" class="w-3 h-3 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
              <span x-text="syncing ? 'Syncing...' : 'Sync Now'"></span>
            </button>
          </div>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl divide-y divide-gray-700">
          <div class="px-4 py-3"><div class="text-gray-400 text-xs uppercase tracking-widest">App</div></div>
          <div class="flex justify-between items-center px-4 py-3"><span class="text-gray-400 text-sm">Version</span><span class="text-white text-sm">1.0.0</span></div>
          <div class="px-4 py-3">
            <template x-if="!confirmClearData"><button @click="confirmClearData = true" class="text-red-400 text-sm">Log Out</button></template>
            <template x-if="confirmClearData">
              <div class="space-y-2">
                <p class="text-gray-300 text-sm" x-text="isSupervisor() ? 'Sign out of your supervisor session?' : 'This will log you out, clear all local data, and deactivate this device. You will need a new activation code to sign back in.'"></p>
                <div class="flex gap-3">
                  <button @click="confirmClearData = false" class="flex-1 border border-gray-600 text-gray-400 py-2 rounded-lg text-sm">Cancel</button>
                  <button @click="clearAllData" class="flex-1 bg-red-500 text-white font-bold py-2 rounded-lg text-sm">Log Out</button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

    </div>

    </div>{{-- /main content --}}

  </div>

  <script src="/spotter/js/db.js"></script>
  <script src="/spotter/js/duplicate.js"></script>
  <script src="/spotter/js/camera.js"></script>
  <script src="/spotter/js/gps.js"></script>
  <script src="/spotter/js/sync.js"></script>
  <script src="/spotter/js/app.js"></script>

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
