<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dawa Mtaani')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Apply theme before paint to prevent flash --}}
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.store('theme', {
                isDark: true,
                init() {
                    this.isDark = localStorage.getItem('theme') !== 'light';
                    this.apply();
                },
                toggle() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    this.apply();
                },
                apply() {
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.classList.add('light');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded:    window.innerWidth >= 1280,
                isMobileOpen:  false,
                isHovered:     false,
                toggleExpanded()     { this.isExpanded = !this.isExpanded; this.isMobileOpen = false; },
                toggleMobileOpen()   { this.isMobileOpen = !this.isMobileOpen; },
                setMobileOpen(val)   { this.isMobileOpen = val; },
                closeMobile()        { this.isMobileOpen = false; },
                toggleMobile()       { this.isMobileOpen = !this.isMobileOpen; },
                setHovered(val)      {
                    if (window.innerWidth >= 1280 && !this.isExpanded) this.isHovered = val;
                }
            });

            Alpine.store('patientCart', {
                open: false, items: [], count: 0, total: 0,
                KEY: 'dm_patient_cart',
                load() { try { this.items = JSON.parse(localStorage.getItem(this.KEY) || '[]'); } catch(e) { this.items = []; } this.recalc(); },
                recalc() { this.count = this.items.reduce((s,i) => s + (i.qty||1), 0); this.total = this.items.reduce((s,i) => s + ((i.price||0)*(i.qty||1)), 0); },
                save() { localStorage.setItem(this.KEY, JSON.stringify(this.items)); this.recalc(); },
                add(id, name, price, unit) { const f = this.items.find(i => i.product_id === id); if (f) { f.qty++; } else { this.items.push({ product_id: id, name, price, unit, qty: 1 }); } this.save(); window.dispatchEvent(new CustomEvent('toast', { detail: { message: name + ' added to cart', type: 'success' } })); },
                increase(id) { const i = this.items.find(x => x.product_id === id); if (i) { i.qty++; this.save(); } },
                decrease(id) { const idx = this.items.findIndex(x => x.product_id === id); if (idx === -1) return; if (this.items[idx].qty <= 1) this.items.splice(idx, 1); else this.items[idx].qty--; this.save(); },
                remove(id) { this.items = this.items.filter(x => x.product_id !== id); this.save(); },
                clear() { this.items = []; this.save(); this.open = false; },
            });
        });
    </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen"
      x-data="{ loaded: true }"
      x-init="
        $store.sidebar.isExpanded = window.innerWidth >= 1280;
        window.addEventListener('resize', () => {
            if (window.innerWidth < 1280) {
                $store.sidebar.setMobileOpen(false);
                $store.sidebar.isExpanded = false;
            } else {
                $store.sidebar.isMobileOpen = false;
                $store.sidebar.isExpanded = true;
            }
        });
      ">

    {{-- Page load spinner --}}
    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 350) })"
         class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-white dark:bg-gray-900">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-yellow-400 border-t-transparent"></div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="$store.sidebar.isMobileOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.sidebar.closeMobile()"
         class="fixed inset-0 bg-black/60 z-[99998] xl:hidden"
         style="display: none;"></div>

    <div class="min-h-screen xl:flex">
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
             :class="{
                'xl:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[80px]':  !$store.sidebar.isExpanded && !$store.sidebar.isHovered
             }">
            @include('layouts.app-header')

            <div class="px-4 md:px-6 pt-4 pb-8">
                @if(session('success'))
                <div class="rounded-lg bg-green-900/20 border border-green-800 px-4 py-3 text-sm text-green-300 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="rounded-lg bg-red-900/20 border border-red-800 px-4 py-3 text-sm text-red-300 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    {{-- Google Maps for logistics routes only --}}
    @if(config('services.google_maps.key') && request()->is('logistics/routes*'))
    <script>function initGoogleMaps() { window.dispatchEvent(new Event('google-maps-loaded')); }</script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initGoogleMaps"></script>
    @endif
</body>
</html>