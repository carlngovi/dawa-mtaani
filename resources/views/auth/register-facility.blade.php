<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register your Pharmacy — Dawa Mtaani</title>
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
<body class="h-full bg-white dark:bg-gray-900">

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">

    {{-- Logo --}}
    <a href="/" class="flex items-center gap-2 mb-8">
        <div class="h-8 w-8 rounded-lg bg-yellow-400 flex items-center justify-center">
            <span class="text-gray-900 font-bold text-sm">DM</span>
        </div>
        <span class="text-lg font-bold text-gray-900 dark:text-white">
            Dawa<span class="text-yellow-400">Mtaani</span>
        </span>
    </a>

    <div class="w-full max-w-lg">

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">
            Register your Pharmacy
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center">
            For pharmacy owners &amp; operators
        </p>

        {{-- Notice --}}
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    After submitting, your PPB licence will be verified automatically.
                    Your account will be reviewed by our team before activation.
                    This usually takes 1–2 business days.
                </p>
            </div>
        </div>

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

        {{-- Google prefill badge --}}
        @if ($prefill)
        <div class="flex items-center gap-3 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-3 mb-6">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm text-green-700 dark:text-green-300">
                Google account linked: <strong>{{ $prefill['email'] }}</strong>. Complete the form below.
            </span>
        </div>
        @endif

        {{-- Registration form --}}
        <form method="POST" action="{{ route('register.facility.store') }}" class="space-y-5">
            @csrf

            {{-- Owner full name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Owner full name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $prefill['name'] ?? '') }}"
                       required
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
            </div>

            {{-- PPB Licence Number --}}
            <div>
                <label for="ppb_licence_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    PPB Licence Number <span class="text-red-500">*</span>
                </label>
                <input type="text" id="ppb_licence_number" name="ppb_licence_number"
                       value="{{ old('ppb_licence_number') }}"
                       required
                       placeholder="PPB/PH/XXXX/XXXX"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Your PPB registration number e.g. PPB/PH/XXXX/XXXX</p>
            </div>

            {{-- Facility name --}}
            <div>
                <label for="facility_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Facility / Pharmacy name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="facility_name" name="facility_name"
                       value="{{ old('facility_name') }}"
                       required
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Phone number <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone" name="phone"
                       value="{{ old('phone') }}"
                       required
                       placeholder="+254XXXXXXXXX"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Email address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $prefill['email'] ?? '') }}"
                       required
                       {{ $prefill ? 'readonly' : '' }}
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400 {{ $prefill ? 'bg-gray-50 dark:bg-gray-800' : '' }}"/>
            </div>

            {{-- County --}}
            <div>
                <label for="county" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    County <span class="text-red-500">*</span>
                </label>
                <select id="county" name="county" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-yellow-400">
                    <option value="">Select county...</option>
                    @foreach ($counties as $county)
                        <option value="{{ $county->code }}" {{ old('county') == $county->code ? 'selected' : '' }}>
                            {{ $county->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Password fields (hidden if Google prefill) --}}
            @unless ($prefill)
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password"
                       required
                       minlength="8"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Minimum 8 characters</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Confirm password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required
                       minlength="8"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-yellow-400"/>
            </div>
            @endunless

            {{-- Terms --}}
            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" name="terms" value="1" required
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-yellow-400 focus:ring-yellow-400 dark:border-gray-600"/>
                <span>
                    I agree to the <a href="#" class="text-yellow-600 hover:underline dark:text-yellow-400">Terms of Service</a>
                    and <a href="#" class="text-yellow-600 hover:underline dark:text-yellow-400">Privacy Policy</a>.
                </span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                    class="flex w-full items-center justify-center rounded-lg bg-yellow-400 px-4 py-3 text-sm font-bold text-gray-900 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-colors dark:focus:ring-offset-gray-900">
                Submit Registration
            </button>
        </form>

        {{-- Google registration option (only if no prefill) --}}
        @unless ($prefill)
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-4 text-gray-400 dark:bg-gray-900 dark:text-gray-500">Or register with</span>
            </div>
        </div>

        <a href="{{ route('auth.google.facility') }}"
           class="flex w-full items-center justify-center gap-3 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M19.6 10.23c0-.68-.06-1.36-.18-2H10v3.79h5.38a4.6 4.6 0 01-2 3.02v2.5h3.22c1.89-1.74 2.98-4.3 2.98-7.31z" fill="#4285F4"/>
                <path d="M10 20c2.7 0 4.96-.89 6.62-2.42l-3.22-2.5c-.9.6-2.04.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H1.07v2.58A10 10 0 0010 20z" fill="#34A853"/>
                <path d="M4.39 11.91A6.01 6.01 0 014.18 10c0-.66.11-1.3.21-1.91V5.51H1.07A10 10 0 000 10c0 1.61.39 3.14 1.07 4.49l3.32-2.58z" fill="#FBBC04"/>
                <path d="M10 3.96c1.47 0 2.79.51 3.83 1.49l2.85-2.85C14.96.99 12.7 0 10 0A10 10 0 001.07 5.51l3.32 2.58C5.18 5.72 7.39 3.96 10 3.96z" fill="#EA4335"/>
            </svg>
            Continue with Google
        </a>
        @endunless

        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-yellow-600 hover:underline dark:text-yellow-400 font-medium">Sign in</a>
        </p>
    </div>
</div>

</body>
</html>
