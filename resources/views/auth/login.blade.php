<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Dawa Mtaani</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">Dawa Mtaani</h1>
            <p class="text-gray-500 text-sm mt-1">Pharmacy Network Platform</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-8 py-8">

            <h2 class="text-xl font-semibold text-gray-800 mb-6">Sign in to your account</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                                  @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                                  @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-green-700 hover:underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit"
                        class="w-full bg-green-700 hover:bg-green-800 text-white font-medium
                               py-2.5 px-4 rounded-lg text-sm transition-colors">
                    Sign in
                </button>

            </form>

        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Next Door Ltd &copy; {{ date('Y') }} — Not for external distribution
        </p>

    </div>

</body>
</html>
