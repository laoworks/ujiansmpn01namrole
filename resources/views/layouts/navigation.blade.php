<nav x-data="{ open: false }"
     x-on:keydown.escape.window="open = false"
     x-on:resize.window="if (window.innerWidth >= 640) open = false"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16 gap-2">

            <!-- Logo + Judul -->
            <div class="flex items-center min-w-0 flex-1 sm:flex-none">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMP Negeri 01 Namrole"
                         class="block h-8 sm:h-9 w-auto shrink-0 object-contain">
                    <span class="font-semibold text-gray-800 text-sm leading-tight truncate">
                        <span class="sm:hidden">Ujian SMPN 01 Namrole</span>
                        <span class="hidden sm:inline">Ujian SMP Negeri 01 Namrole</span>
                    </span>
                </a>

                <!-- Navigation Links (desktop/tablet) -->
                <div class="hidden sm:ms-6 lg:ms-10 sm:flex sm:items-center">
                    <a href="{{ route('dashboard') }}"
                       @class([
                           'inline-flex items-center px-4 py-2 rounded-md text-sm text-white transition ease-in-out duration-150',
                           'bg-indigo-700 ring-2 ring-indigo-300' => request()->routeIs('dashboard'),
                           'bg-indigo-600 hover:bg-indigo-700' => ! request()->routeIs('dashboard'),
                       ])>
                        {{ __('Dashboard') }}
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown (desktop/tablet) -->
            <div class="hidden sm:flex sm:items-center sm:ms-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="max-w-[8rem] lg:max-w-[14rem] truncate">{{ Auth::user()->name }}</div>
                            <div class="ms-1 shrink-0">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <form method="POST" action="{{ route('logout') }}" class="px-2 py-1">
                            @csrf
                            <button type="submit"
                                    class="block w-full px-4 py-2 rounded-md text-start text-sm text-white bg-rose-600 hover:bg-rose-700">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="flex items-center sm:hidden shrink-0">
                <button @click="open = ! open"
                        :aria-expanded="open.toString()"
                        aria-controls="mobile-menu"
                        :aria-label="open ? 'Tutup menu' : 'Buka menu'"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition duration-150 ease-in-out">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Latar redup di belakang menu (ketuk untuk menutup) -->
    <div x-show="open"
         x-cloak
         x-transition.opacity.duration.150ms
         @click="open = false"
         class="sm:hidden fixed inset-x-0 bottom-0 top-14 z-40 bg-gray-900/40"
         aria-hidden="true"></div>

    <!-- Responsive Navigation Menu (melayang, tidak mendorong halaman) -->
    <div id="mobile-menu"
         x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="sm:hidden absolute inset-x-0 top-full z-50 bg-white rounded-b-2xl border-t border-gray-100 shadow-xl max-h-[calc(100dvh-3.5rem)] overflow-y-auto">

        <!-- Info pengguna -->
        <div class="flex items-center gap-3 px-4 pt-4 pb-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-semibold text-lg">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="font-semibold text-sm text-gray-900 truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ Auth::user()->username }}</div>
            </div>
        </div>

        <!-- Menu -->
        <div class="px-3 pb-2 border-t border-gray-100 pt-2">
            <a href="{{ route('dashboard') }}"
               @class([
                   'flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium transition',
                   'bg-indigo-50 text-indigo-700' => request()->routeIs('dashboard'),
                   'text-gray-700 hover:bg-gray-50 active:bg-gray-100' => ! request()->routeIs('dashboard'),
               ])>
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10" />
                </svg>
                {{ __('Dashboard') }}
            </a>
        </div>

        <!-- Log Out -->
        <div class="px-3 pb-4 pt-2 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 active:bg-rose-100 transition">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</nav>