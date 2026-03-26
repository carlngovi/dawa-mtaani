<header class="sticky top-0 flex w-full bg-gray-900 border-b border-gray-800 z-[9999]"
        x-data="{ appMenuOpen: false }">
    <div class="flex flex-col items-center justify-between grow xl:flex-row xl:px-6">
        <div class="flex items-center justify-between w-full gap-2 px-3 py-3 border-b border-gray-800 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 lg:py-4">

            <button class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-400 border border-gray-700 rounded-lg hover:bg-gray-800"
                    @click="$store.sidebar.toggleExpanded()">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/></svg>
            </button>

            <button class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-400 rounded-lg"
                    @click="$store.sidebar.toggleMobileOpen()">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/></svg>
            </button>

            <div class="hidden xl:block flex-1 max-w-md">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"/></svg>
                    </span>
                    <input type="text" placeholder="Search..."
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors"/>
                </div>
            </div>
        </div>

        <div :class="appMenuOpen ? 'flex' : 'hidden'"
             class="items-center justify-between w-full gap-4 px-5 py-4 xl:flex xl:justify-end xl:px-0">
            <div class="flex items-center gap-3">
                {{-- Dark mode toggle --}}
                <button @click="$store.theme.toggle()"
                        class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-gray-800 border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition-colors">
                    <svg x-show="$store.theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="4.5" stroke-width="1.5"/>
                        <path d="M12 2v1m0 18v1m-10-10h1m18 0h1m-3.54-7.46-.7.7m-12.02 12.02-.7.7m0-14.14-.7-.7m12.02 12.02-.7-.7" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <svg x-show="!$store.theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

            </div>

            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button class="flex items-center gap-2 text-gray-300" @click.prevent="open = !open">
                    <div class="h-9 w-9 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-gray-900">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <span class="hidden md:block font-medium text-sm">{{ Auth::user()->name ?? '' }}</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-3 w-56 rounded-2xl border border-gray-700 bg-gray-800 p-3 shadow-lg z-50"
                     style="display:none;">
                    <div class="pb-3 border-b border-gray-700">
                        <span class="block font-medium text-white text-sm">{{ Auth::user()->name ?? '' }}</span>
                        <span class="block text-xs text-gray-400 mt-0.5">{{ Auth::user()->email ?? '' }}</span>
                    </div>
                    <div class="pt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full gap-3 px-3 py-2 font-medium text-red-400 rounded-lg text-sm hover:bg-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
