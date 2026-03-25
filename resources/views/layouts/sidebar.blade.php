@php
    $p = request()->path();
    $user = auth()->user();
    $isAdmin = $user && ($user->hasRole('network_admin') || $user->hasRole('network_field_agent'));
    $isWholesale = $user && $user->hasRole('wholesale_facility');
    $isRetail = $user && ($user->hasRole('retail_facility') || $user->hasRole('group_owner'));
@endphp

<aside id="sidebar"
       class="fixed flex flex-col top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 h-screen transition-all duration-300 ease-in-out z-[99999] border-r border-gray-200"
       x-data="{
           m: {},
           init() {
               const p = '{{ $p }}';
               if(p.includes('facilities')) this.m['pharmacies']=true;
               if(p.includes('products')||p.includes('categories')) this.m['catalogue']=true;
               if(p.includes('counties')||p.includes('sub-counties')||p.includes('wards')) this.m['geography']=true;
               if(p.includes('payments')||p.includes('wallets')) this.m['finance']=true;
               if(p.includes('audit')||p.includes('security')||p.includes('monitoring')) this.m['system']=true;
           },
           tog(k){ const c=this.m[k]; this.m={}; this.m[k]=!c; },
           op(k){ return this.m[k]||false; },
           act(path){ return '{{ $p }}' === path.replace(/^\//,''); }
       }"
       :class="{
           'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
           'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
           'translate-x-0': $store.sidebar.isMobileOpen,
           '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
       }"
       @mouseenter="if(!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
       @mouseleave="$store.sidebar.setHovered(false)">

    <div class="pt-8 pb-7 flex"
         :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'">
        <a href="{{ $isAdmin ? '/admin/dashboard' : ($isWholesale ? '/wholesale/orders' : '/retail/dashboard') }}" class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                <span class="text-white font-bold text-sm">DM</span>
            </div>
            <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                  class="text-lg font-bold text-gray-800 dark:text-white whitespace-nowrap">Dawa Mtaani</span>
        </a>
    </div>

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar flex-1">
        <nav class="mb-6">
            <ul class="flex flex-col gap-1">

                @if($isAdmin)

                <li>
                    <a href="/admin/dashboard" class="menu-item group"
                       :class="[act('/admin/dashboard') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('/admin/dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">Dashboard</span>
                    </a>
                </li>

                <li>
                    <button @click="tog('pharmacies')" class="menu-item group w-full"
                            :class="[op('pharmacies') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'xl:justify-start']">
                        <span :class="op('pharmacies') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="flex-1 text-left">Pharmacies</span>
                        <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="ml-auto w-4 h-4 transition-transform" :class="{'rotate-180 text-blue-600': op('pharmacies')}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="op('pharmacies') && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                        <ul class="mt-2 space-y-1 ml-9">
                            <li><a href="/admin/facilities" class="menu-dropdown-item" :class="act('/admin/facilities') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">All Pharmacies</a></li>
                            <li><a href="/admin/facilities?type=RETAIL" class="menu-dropdown-item menu-dropdown-item-inactive">Retail</a></li>
                            <li><a href="/admin/facilities?type=WHOLESALE" class="menu-dropdown-item menu-dropdown-item-inactive">Wholesalers</a></li>
                            <li><a href="/admin/facilities?type=HOSPITAL" class="menu-dropdown-item menu-dropdown-item-inactive">Hospitals</a></li>
                            <li><a href="/admin/facilities?type=MANUFACTURER" class="menu-dropdown-item menu-dropdown-item-inactive">Manufacturers</a></li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="/admin/wallets" class="menu-item group" :class="[act('/admin/wallets') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('/admin/wallets') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">Wallets</span>
                    </a>
                </li>

                <li>
                    <a href="/admin/bank-accounts" class="menu-item group" :class="[act('/admin/bank-accounts') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('/admin/bank-accounts') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">Bank Accounts</span>
                    </a>
                </li>

                <li>
                    <button @click="tog('geography')" class="menu-item group w-full" :class="[op('geography') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'xl:justify-start']">
                        <span :class="op('geography') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="flex-1 text-left">Geography</span>
                        <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="ml-auto w-4 h-4 transition-transform" :class="{'rotate-180 text-blue-600': op('geography')}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="op('geography') && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                        <ul class="mt-2 space-y-1 ml-9">
                            <li><a href="/admin/facilities?group=county" class="menu-dropdown-item menu-dropdown-item-inactive">Counties</a></li>
                            <li><a href="/admin/facilities?group=sub_county" class="menu-dropdown-item menu-dropdown-item-inactive">Sub-Counties</a></li>
                            <li><a href="/admin/facilities?group=ward" class="menu-dropdown-item menu-dropdown-item-inactive">Wards</a></li>
                        </ul>
                    </div>
                </li>

                <li>
                    <button @click="tog('catalogue')" class="menu-item group w-full" :class="[op('catalogue') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'xl:justify-start']">
                        <span :class="op('catalogue') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="flex-1 text-left">Catalogue</span>
                        <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="ml-auto w-4 h-4 transition-transform" :class="{'rotate-180 text-blue-600': op('catalogue')}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="op('catalogue') && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                        <ul class="mt-2 space-y-1 ml-9">
                            <li><a href="/admin/categories" class="menu-dropdown-item" :class="act('/admin/categories') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">Categories</a></li>
                            <li><a href="/admin/products" class="menu-dropdown-item" :class="act('/admin/products') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">Products</a></li>
                            <li><a href="/admin/ppb-registry" class="menu-dropdown-item" :class="act('/admin/ppb-registry') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">PPB Registry</a></li>
                        </ul>
                    </div>
                </li>

                @foreach([
                    ['/admin/orders', 'Orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['/admin/payments', 'Payments', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['/admin/disputes', 'Disputes', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    ['/admin/inventory', 'Inventory', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['/admin/sales', 'Sales', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                    ['/admin/quality-flags', 'Quality Flags', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['/admin/notifications', 'Notifications', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                    ['/admin/customers', 'Customers', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ] as [$href, $label, $iconPath])
                <li>
                    <a href="{{ $href }}" class="menu-item group" :class="[act('{{ $href }}') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('{{ $href }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">{{ $label }}</span>
                    </a>
                </li>
                @endforeach

                <li class="my-2 border-t border-gray-100 dark:border-gray-800"></li>

                @foreach([
                    ['/admin/audit-log', 'Audit Log', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['/admin/monitoring', 'Monitoring', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['/admin/security', 'Security', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['/admin/reports', 'Reports', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['/admin/settings', 'System Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ] as [$href, $label, $iconPath])
                <li>
                    <a href="{{ $href }}" class="menu-item group" :class="[act('{{ $href }}') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('{{ $href }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">{{ $label }}</span>
                    </a>
                </li>
                @endforeach

                @elseif($isWholesale)
                @foreach([
                    ['/wholesale/orders','Order Queue','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['/wholesale/price-lists','Price Lists','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['/wholesale/stock','Stock','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['/wholesale/performance','Performance','M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                ] as [$href,$label,$iconPath])
                <li>
                    <a href="{{ $href }}" class="menu-item group" :class="[act('{{ $href }}') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('{{ $href }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">{{ $label }}</span>
                    </a>
                </li>
                @endforeach

                @elseif($isRetail)
                @foreach([
                    ['/retail/dashboard','Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['/retail/catalogue','Order Medicines','M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['/retail/orders','My Orders','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['/retail/favourites','Favourites','M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                    ['/retail/credit','Credit','M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['/retail/pos','Point of Sale','M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['/retail/quality-flags','Quality Reports','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ] as [$href,$label,$iconPath])
                <li>
                    <a href="{{ $href }}" class="menu-item group" :class="[act('{{ $href }}') ? 'menu-item-active' : 'menu-item-inactive', (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start']">
                        <span :class="act('{{ $href }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg></span>
                        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">{{ $label }}</span>
                    </a>
                </li>
                @endforeach
                @endif

                <li class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="menu-item group w-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10"
                                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'">
                            <span class="text-red-600 dark:text-red-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></span>
                            <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>
