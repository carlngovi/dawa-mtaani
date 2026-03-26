<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Registration — Dawa Mtaani</title>
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

    <div class="w-full max-w-md">

        <h1 class="text-2xl font-bold text-white mb-2 text-center">
            Create your account
        </h1>
        <p class="text-sm text-gray-400 mb-6 text-center">
            Create a free account to order medicines from pharmacies near you.
        </p>

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

        {{-- Registration form --}}
        <form method="POST" action="{{ route('register.patient.store') }}" class="space-y-5">
            @csrf

            {{-- First + Last name --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1.5">
                        First name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="first_name" name="first_name"
                           value="{{ old('first_name') }}"
                           required
                           autocomplete="given-name"
                           placeholder="John"
                           class="h-11 w-full rounded-lg border border-gray-600 bg-gray-800 px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400"/>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1.5">
                        Last name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="last_name" name="last_name"
                           value="{{ old('last_name') }}"
                           required
                           autocomplete="family-name"
                           placeholder="Doe"
                           class="h-11 w-full rounded-lg border border-gray-600 bg-gray-800 px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400"/>
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Email address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       placeholder="you@example.com"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password"
                       required
                       minlength="8"
                       autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
                <p class="mt-1 text-xs text-gray-400">Minimum 8 characters</p>
            </div>

            {{-- Confirm password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Confirm password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required
                       minlength="8"
                       autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm text-gray-200 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"/>
            </div>

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
                    class="flex w-full items-center justify-center rounded-lg bg-yellow-400 px-4 py-3 text-sm font-bold text-gray-900 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-colors">
                Create Account
            </button>
        </form>

        {{-- Google sign-up --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-gray-800 px-4 text-gray-400">Or sign up with</span>
            </div>
        </div>

        <a href="{{ route('auth.google.patient') }}"
           class="flex w-full items-center justify-center gap-3 rounded-lg border border-gray-600 bg-gray-800 px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-900 transition-colors">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M19.6 10.23c0-.68-.06-1.36-.18-2H10v3.79h5.38a4.6 4.6 0 01-2 3.02v2.5h3.22c1.89-1.74 2.98-4.3 2.98-7.31z" fill="#4285F4"/>
                <path d="M10 20c2.7 0 4.96-.89 6.62-2.42l-3.22-2.5c-.9.6-2.04.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H1.07v2.58A10 10 0 0010 20z" fill="#34A853"/>
                <path d="M4.39 11.91A6.01 6.01 0 014.18 10c0-.66.11-1.3.21-1.91V5.51H1.07A10 10 0 000 10c0 1.61.39 3.14 1.07 4.49l3.32-2.58z" fill="#FBBC04"/>
                <path d="M10 3.96c1.47 0 2.79.51 3.83 1.49l2.85-2.85C14.96.99 12.7 0 10 0A10 10 0 001.07 5.51l3.32 2.58C5.18 5.72 7.39 3.96 10 3.96z" fill="#EA4335"/>
            </svg>
            Continue with Google
        </a>

        <p class="mt-6 text-center text-sm text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-yellow-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>
</div>

</body>
</html>
