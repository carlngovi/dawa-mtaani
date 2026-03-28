@php
    $p    = request()->path();
    $user = auth()->user();

    $isAdmin      = $user && $user->hasAnyRole(['network_admin','admin']) && !$user->hasAnyRole(['super_admin','technical_admin']);
    $isWholesale  = $user && $user->hasRole('wholesale_facility');
    $isLogistics  = $user && $user->hasRole('logistics_facility');
    $isGroupOwner = $user && $user->hasRole('group_owner');
    $isRetail     = $user && $user->hasRole('retail_facility');
    $isFieldAgent = $user && $user->hasRole('network_field_agent');
    $isSalesRep   = $user && $user->hasRole('sales_rep');
    $isCountyCoord = $user && $user->hasRole('county_coordinator');
    $isFinance    = $user && $user->hasRole('shared_accountant');
    $isSupport    = $user && $user->hasRole('admin_support');
    $isAssistant  = $user && $user->hasRole('assistant_admin');
    $isSuperAdmin = $user && $user->hasRole('super_admin');
    $isTechAdmin  = $user && $user->hasRole('technical_admin');
    $isPatient    = $user && $user->hasRole('patient');

    $logoHref = $isTechAdmin   ? '/tech/diagnostics'
              : ($isSuperAdmin ? '/super/settings'
              : ($isAssistant  ? '/assistant/dashboard'
              : ($isSupport    ? '/support/tickets'
              : ($isFinance    ? '/finance/settlement'
              : ($isAdmin      ? '/admin/dashboard'
              : ($isWholesale  ? '/wholesale/orders'
              : ($isLogistics  ? '/logistics/deliveries'
              : ($isFieldAgent ? '/field/pharmacies'
              : ($isCountyCoord ? '/county'
              : ($isSalesRep   ? '/sales'
              : ($isGroupOwner ? '/group/dashboard'
              : ($isPatient    ? '/store'
              : '/retail/dashboard'))))))))))));
@endphp

<aside id="sidebar"
       class="fixed flex flex-col top-0 left-0 h-screen transition-all duration-300 ease-in-out z-[99999]"
       x-data="{
           m: {},
           init() {
               const p = '{{ $p }}';
               if (p.includes('facilities') || p.includes('registrations')) this.m['pharmacies'] = true;
               if (p.includes('products') || p.includes('categories') || p.includes('ppb')) this.m['catalogue'] = true;
               if (p.includes('payments') || p.includes('wallets') || p.includes('credit') || p.includes('settlement')) this.m['finance'] = true;
               if (p.includes('audit') || p.includes('security') || p.includes('monitoring') || p.includes('tech')) this.m['system'] = true;
               if (p.includes('dispute')) this.m['disputes'] = true;
           },
           tog(k) { const c = this.m[k]; this.m = {}; this.m[k] = !c; },
           op(k)  { return this.m[k] || false; },
           act(path) { return '{{ $p }}' === path.replace(/^\//, ''); }
       }"
       :class="{
           'w-[280px] bg-gray-950 border-r border-gray-800 px-4': $store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen,
           'xl:w-[64px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen,
           'translate-x-0': $store.sidebar.isMobileOpen,
           '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
       }"
       @mouseenter="if(!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
       @mouseleave="$store.sidebar.setHovered(false)"
       @keydown.escape.window="$store.sidebar.setMobileOpen(false)">

    {{-- Logo --}}
    <div class="flex-shrink-0 pt-5 pb-5 flex transition-all duration-300"
         :class="($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 'border-b border-gray-800 px-3 justify-start' : 'border-b border-transparent justify-center px-0'">
        <a href="{{ $logoHref }}" class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-lg bg-yellow-400 flex items-center justify-center flex-shrink-0">
                <span class="text-gray-900 font-bold text-sm tracking-tight">DM</span>
            </div>
            <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <span class="text-white font-bold text-base tracking-tight whitespace-nowrap">Dawa<span class="text-yellow-400">Mtaani</span></span>
                <p class="text-gray-500 text-[10px] tracking-widest uppercase mt-0.5">Quality, Affordably</p>
            </div>
        </a>
    </div>

    {{-- Nav (scrollable) --}}
    <div id="sidebar-nav"
         x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
         class="flex flex-col overflow-y-auto scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-gray-700 flex-1 py-4">
        <nav>
            <ul class="flex flex-col gap-0.5">

                @if($isRetail)
                    @include('layouts.sidebar-sections.retail')
                @endif

                @if($isGroupOwner)
                    @include('layouts.sidebar-sections.group-owner')
                @endif

                @if($isWholesale)
                    @include('layouts.sidebar-sections.wholesale')
                @endif

                @if($isLogistics)
                    @include('layouts.sidebar-sections.logistics')
                @endif

                @if($isFieldAgent)
                    @include('layouts.sidebar-sections.field-agent')
                @endif

                @if($isSalesRep)
                    @include('layouts.sidebar-sections.sales-rep')
                @endif

                @if($isCountyCoord)
                    @include('layouts.sidebar-sections.county-coordinator')
                @endif

                @if($isFinance)
                    @include('layouts.sidebar-sections.finance')
                @endif

                @if($isSupport)
                    @include('layouts.sidebar-sections.support')
                @endif

                @if($isAssistant)
                    @include('layouts.sidebar-sections.assistant')
                @endif

                @if($isSuperAdmin)
                    @include('layouts.sidebar-sections.super-admin')
                @endif

                @if($isTechAdmin)
                    @include('layouts.sidebar-sections.tech-admin')
                @endif

                @if($isAdmin)
                    @include('layouts.sidebar-sections.admin')
                @endif

                @if($isPatient)
                    @include('layouts.sidebar-sections.patient')
                @endif

            </ul>
        </nav>
    </div>

    {{-- Footer --}}
    <div class="flex-shrink-0"
         :class="($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 'border-t border-gray-800' : ''">

        {{-- User pill --}}
        <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
             class="flex items-center gap-3 px-3 pt-3 pb-2">
            <div class="h-8 w-8 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                <span class="text-gray-900 font-bold text-xs">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name ?? '' }}</p>
                <p class="text-gray-500 text-xs truncate">{{ auth()->user()->getRoleNames()->first() ?? '' }}</p>
            </div>
        </div>

        {{-- Sign Out --}}
        <form method="POST" action="{{ route('logout') }}"
              x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 text-gray-400 hover:text-red-400 hover:bg-gray-800/50 transition-colors text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Sign Out</span>
            </button>
        </form>

    </div>

    <script>
    (function() {
        var NAV_KEY = 'dm_sidebar_scroll';
        var nav = document.getElementById('sidebar-nav');
        if (!nav) return;

        var saved = sessionStorage.getItem(NAV_KEY);
        if (saved !== null) {
            nav.scrollTop = parseInt(saved, 10);
        }

        nav.addEventListener('scroll', function() {
            sessionStorage.setItem(NAV_KEY, nav.scrollTop);
        }, { passive: true });

        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem(NAV_KEY, nav.scrollTop);
        });

        setTimeout(function() {
            var active = nav.querySelector('.menu-item-active');
            if (active) {
                var navRect = nav.getBoundingClientRect();
                var itemRect = active.getBoundingClientRect();
                if (itemRect.top < navRect.top || itemRect.bottom > navRect.bottom) {
                    active.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                }
            }
        }, 50);
    })();
    </script>
</aside>