@php
    $p    = request()->path();
    $user = auth()->user();

    $isAdmin      = $user && $user->hasAnyRole(['network_admin','admin','super_admin','technical_admin','admin_support','assistant_admin','shared_accountant']);
    $isWholesale  = $user && $user->hasRole('wholesale_facility');
    $isLogistics  = $user && $user->hasRole('logistics_facility');
    $isGroupOwner = $user && $user->hasRole('group_owner');
    $isRetail     = $user && $user->hasRole('retail_facility');
    $isFieldAgent = $user && $user->hasRole('network_field_agent');
    $isSalesRep   = $user && $user->hasRole('sales_rep');
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
              : ($isSalesRep   ? '/rep/pharmacies'
              : ($isGroupOwner ? '/group/dashboard'
              : ($isPatient    ? '/store'
              : '/retail/dashboard')))))))))));
@endphp

<aside id="sidebar"
       class="fixed flex flex-col top-0 px-4 left-0 bg-gray-950 h-screen transition-all duration-300 ease-in-out z-[99999] border-r border-gray-800"
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
           'w-[280px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
           'w-[76px]':  !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
           'translate-x-0':                        $store.sidebar.isMobileOpen,
           '-translate-x-full xl:translate-x-0':  !$store.sidebar.isMobileOpen
       }"
       @mouseenter="if(!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
       @mouseleave="$store.sidebar.setHovered(false)">

    {{-- Logo --}}
    <div class="pt-7 pb-6 flex border-b border-gray-800"
         :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'">
        <a href="{{ $logoHref }}"
           class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-lg bg-yellow-400 flex items-center justify-center flex-shrink-0">
                <span class="text-gray-900 font-bold text-sm tracking-tight">DM</span>
            </div>
            <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                <span class="text-white font-bold text-base tracking-tight">Dawa<span class="text-yellow-400">Mtaani</span></span>
                <p class="text-gray-500 text-[10px] tracking-widest uppercase mt-0.5">Quality, Affordably</p>
            </div>
        </a>
    </div>

    {{-- Nav --}}
    <div class="flex flex-col overflow-y-auto no-scrollbar flex-1 py-4">
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

    {{-- Footer: user pill + collapse toggle --}}
    <div class="py-4 border-t border-gray-800 flex items-center gap-3"
         :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-between'">
        <div class="flex items-center gap-3 min-w-0"
             x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
            <div class="h-8 w-8 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                <span class="text-gray-900 font-bold text-xs">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            </div>
            <div class="min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name ?? '' }}</p>
                <p class="text-gray-500 text-xs truncate">{{ auth()->user()->getRoleNames()->first() ?? '' }}</p>
            </div>
        </div>
        <button @click="$store.sidebar.toggleExpanded()"
                class="hidden xl:flex text-gray-500 hover:text-white transition-colors flex-shrink-0">
            <svg class="w-5 h-5 transition-transform duration-300"
                 :class="$store.sidebar.isExpanded ? 'rotate-0' : 'rotate-180'"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>
</aside>