<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Submitted — Dawa Mtaani</title>
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

<div class="min-h-screen flex flex-col items-center justify-center px-4">

    <div class="w-full max-w-md text-center">

        {{-- Success icon --}}
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-900/30">
            <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-3">
            Registration Submitted
        </h1>

        <p class="text-gray-400 mb-8">
            Your application for <strong class="text-white">{{ $facilityName }}</strong> has been submitted.
            We'll notify you by email and SMS once your account is activated.
        </p>

        <p class="text-sm text-gray-400 mb-8">
            This usually takes 1–2 business days.
        </p>

        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center rounded-lg bg-yellow-400 px-6 py-3 text-sm font-bold text-gray-900 hover:bg-yellow-500 transition-colors">
            Back to Sign In
        </a>
    </div>
</div>

</body>
</html>
