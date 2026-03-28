<nav class="bg-gray-800 border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="/dashboard" class="text-xl font-bold text-green-400">Dawa Mtaani</a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">{{ Auth::user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-400 hover:text-red-800">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
