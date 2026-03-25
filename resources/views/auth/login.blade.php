<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — Dawa Mtaani</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            }
        })();
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const saved = localStorage.getItem('theme');
                    const sys = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = saved || sys;
                    this.apply();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.apply();
                },
                apply() {
                    if (this.theme === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.body.classList.add('dark', 'bg-gray-900');
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });
        });
    </script>
</head>
<body class="h-full bg-white dark:bg-gray-900">

<div class="relative flex h-screen w-full flex-col lg:flex-row">

    {{-- ===== LEFT — Login Form ===== --}}
    <div class="flex w-full flex-1 flex-col lg:w-1/2">

        {{-- Top logo bar --}}
        <div class="flex items-center gap-2 px-5 sm:px-8 pt-6 sm:pt-8">
            <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center">
                <span class="text-white font-bold text-sm">DM</span>
            </div>
            <span class="text-lg font-bold text-gray-800 dark:text-white">Dawa Mtaani</span>
        </div>

        {{-- Form centered --}}
        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-5 sm:px-8 py-8">

            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white mb-2">
                    Sign in to your account
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Enter your credentials to access the Dawa Mtaani portal
                </p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 dark:bg-red-900/20 dark:border-red-800">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 dark:bg-green-900/20 dark:border-green-800">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}"
               class="flex w-full items-center justify-center gap-3 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 mb-6">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M19.6 10.23c0-.68-.06-1.36-.18-2H10v3.79h5.38a4.6 4.6 0 01-2 3.02v2.5h3.22c1.89-1.74 2.98-4.3 2.98-7.31z" fill="#4285F4"/>
                    <path d="M10 20c2.7 0 4.96-.89 6.62-2.42l-3.22-2.5c-.9.6-2.04.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H1.07v2.58A10 10 0 0010 20z" fill="#34A853"/>
                    <path d="M4.39 11.91A6.01 6.01 0 014.18 10c0-.66.11-1.3.21-1.91V5.51H1.07A10 10 0 000 10c0 1.61.39 3.14 1.07 4.49l3.32-2.58z" fill="#FBBC04"/>
                    <path d="M10 3.96c1.47 0 2.79.51 3.83 1.49l2.85-2.85C14.96.99 12.7 0 10 0A10 10 0 001.07 5.51l3.32 2.58C5.18 5.72 7.39 3.96 10 3.96z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>

            {{-- Divider --}}
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-gray-400 dark:bg-gray-900 dark:text-gray-500">or sign in with email</span>
                </div>
            </div>

            {{-- Login form --}}
            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false, remember: false }">
                @csrf

                <div class="space-y-5">

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1.5">
                            Email address <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               placeholder="you@example.com"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-600 @error('email') border-red-400 @enderror"/>
                    </div>

                    {{-- Password with show/hide toggle --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Enter your password"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-600 @error('password') border-red-400 @enderror"/>
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me + Forgot password --}}
                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 select-none dark:text-gray-400">
                            <div class="relative">
                                <input type="checkbox"
                                       name="remember"
                                       class="sr-only"
                                       @change="remember = !remember"/>
                                <div :class="remember ? 'border-blue-600 bg-blue-600' : 'border-gray-300 bg-transparent dark:border-gray-600'"
                                     class="flex h-5 w-5 items-center justify-center rounded-md border-[1.5px] transition-colors">
                                    <span :class="remember ? 'opacity-100' : 'opacity-0'" class="transition-opacity">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                            <path d="M10 3L4.75 8.25L2 5.5" stroke="white" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            Keep me logged in
                        </label>
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Forgot password?
                        </a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors dark:focus:ring-offset-gray-900">
                        Sign in
                    </button>

                </div>
            </form>

        </div>
    </div>

    {{-- ===== RIGHT — Brand panel ===== --}}
    <div class="relative hidden lg:flex lg:w-1/2 bg-gray-950 dark:bg-white/5 items-center justify-center overflow-hidden">

        {{-- Grid pattern background --}}
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>
        </div>

        {{-- Glowing orbs --}}
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-blue-600 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-blue-400 rounded-full opacity-10 blur-3xl"></div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col items-center text-center px-12 max-w-sm">
            <div class="h-16 w-16 rounded-2xl bg-blue-600 flex items-center justify-center mb-6 shadow-lg">
                <span class="text-white font-bold text-2xl">DM</span>
            </div>

            <h2 class="text-2xl font-bold text-white mb-4">Dawa Mtaani</h2>

            <p class="text-gray-400 text-sm leading-relaxed mb-8">
                Kenya's pharmacy network platform connecting retail pharmacies, hospitals, and wholesale distributors — built for speed, transparency, and compliance.
            </p>

            {{-- Feature pills --}}
            <div class="flex flex-wrap justify-center gap-2">
                @foreach(['Network Orders', 'PPB Verified', 'Credit Engine', 'WhatsApp Ordering', 'Kenya DPA Compliant'] as $feature)
                <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-gray-300 border border-white/10">
                    {{ $feature }}
                </span>
                @endforeach
            </div>

            {{-- Stats with count-up animation --}}
            <div class="mt-10 grid grid-cols-3 gap-6 w-full" x-data="countUp()" x-init="start()">
                <div class="text-center">
                    <p class="text-2xl font-bold text-white" x-text="counts[0] + '+'"></p>
                    <p class="text-xs text-gray-400 mt-1">Facilities</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-white" x-text="counts[1] + '+'"></p>
                    <p class="text-xs text-gray-400 mt-1">Products</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-white" x-text="counts[2]"></p>
                    <p class="text-xs text-gray-400 mt-1">Portals</p>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Floating dark mode toggle --}}
<div class="fixed right-6 bottom-6 z-50">
    <button @click.prevent="$store.theme.toggle()"
            x-data
            class="bg-blue-600 hover:bg-blue-700 inline-flex h-14 w-14 items-center justify-center rounded-full text-white shadow-lg transition-colors">
        <svg class="hidden dark:block w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg class="dark:hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
</div>

<script>
function countUp() {
    return {
        targets: [11, 25, 3],
        counts: [0, 0, 0],
        start() {
            // Delay slightly so the page has rendered
            setTimeout(() => {
                this.targets.forEach((target, i) => {
                    const duration = 1800;
                    const steps = 60;
                    const stepTime = duration / steps;
                    let current = 0;
                    const increment = target / steps;

                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            this.counts[i] = target;
                            clearInterval(timer);
                        } else {
                            this.counts[i] = Math.floor(current);
                        }
                    }, stepTime);
                });
            }, 400);
        }
    }
}
</script>

</body>
</html>
