<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dawa Mtaani')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (t === 'dark') { document.documentElement.classList.add('dark'); document.body.classList.add('dark','bg-gray-900'); }
        })();
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const saved = localStorage.getItem('theme');
                    const sys = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = saved || sys;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    if (this.theme === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.body.classList.add('dark','bg-gray-900');
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.body.classList.remove('dark','bg-gray-900');
                    }
                }
            });
            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                toggleExpanded() { this.isExpanded = !this.isExpanded; this.isMobileOpen = false; },
                toggleMobileOpen() { this.isMobileOpen = !this.isMobileOpen; },
                setMobileOpen(val) { this.isMobileOpen = val; },
                setHovered(val) { if (window.innerWidth >= 1280 && !this.isExpanded) this.isHovered = val; }
            });
        });
    </script>
</head>
<body x-data="{ loaded: true }"
      x-init="
        $store.sidebar.isExpanded = window.innerWidth >= 1280;
        window.addEventListener('resize', () => {
            if (window.innerWidth < 1280) { $store.sidebar.setMobileOpen(false); $store.sidebar.isExpanded = false; }
            else { $store.sidebar.isMobileOpen = false; $store.sidebar.isExpanded = true; }
        });
      ">

    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 350) })"
         class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-white dark:bg-gray-900">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-blue-600 border-t-transparent"></div>
    </div>

    <div :class="$store.sidebar.isMobileOpen ? 'block xl:hidden' : 'hidden'"
         class="fixed z-50 h-screen w-full bg-gray-900/50"
         @click="$store.sidebar.setMobileOpen(false)"></div>

    <div class="min-h-screen xl:flex">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 ease-in-out"
             :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered
             }">
            @include('layouts.app-header')
            <div class="px-4 md:px-6 pt-4">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
            </div>
            <main class="p-4 mx-auto max-w-screen-2xl md:p-6 space-y-0">@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
