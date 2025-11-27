<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex">

            <aside
    class="hidden md:flex fixed inset-y-0 left-0 w-20 flex-col justify-between items-center
           bg-white dark:bg-gray-900 py-6 shadow-md z-20">
    {{-- Logo atas --}}
    <div class="flex flex-col items-center gap-8">
        <div class="h-12 w-12 rounded-full bg-white shadow overflow-hidden flex items-center justify-center">
            <img src="{{ asset('images/icb.jpg') }}" class="h-10 w-10 object-contain" alt="Logo">
        </div>

        {{-- Ikon menu utama --}}
        <nav class="flex flex-col items-center gap-8 text-gray-500">
            {{-- Home (aktif) --}}
           <a href="{{ route('dashboard') }}"
   class="flex items-center justify-center h-7 w-7">
    <img
        src="{{ asset('images/home.png') }}"
        alt="Home"
        class="h-10 w-10 object-contain"
    >
</a>
            {{-- Search --}}
            <button class="flex items-center justify-center h-9 w-9 text-gray-500 hover:text-gray-800">
                <span class="text-lg">🔍</span>
            </button>

            {{-- Add --}}
            <a href="{{ route('media.create') }}"
               class="flex items-center justify-center h-9 w-9 text-gray-500 hover:text-gray-800">
                <span class="text-2xl leading-none">+</span>
            </a>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="flex items-center justify-center h-9 w-9 text-gray-500 hover:text-gray-800">
                <span class="text-lg">👤</span>
            </a>
        </nav>
    </div>

    {{-- Hamburger bawah dengan menu --}}
<div class="relative group pt-4 w-full flex justify-center">
    <button class="h-10 w-10 flex items-center justify-center text-gray-600">
        <span class="text-2xl">≡</span>
    </button>

    <div
        class="absolute left-full bottom-2 ml-2 hidden group-hover:block
               bg-white dark:bg-gray-800 shadow-lg rounded-md py-2 w-40 z-20"
    >
            <a
                href="{{ route('profile.edit') }}"
                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                Settings
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-red-600
                           hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
            {{-- KONTEN UTAMA --}}
            <div class="flex-1 flex flex-col min-h-screen">
                {{-- TOPBAR MOBILE --}}
                <header class="md:hidden flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-b">
                    <div class="h-8 w-8 rounded-full bg-gray-200"></div>
                    <button class="h-8 w-8 flex items-center justify-center">
                        <span class="text-2xl">≡</span>
                    </button>
                </header>

                <main class="flex-1">
                    {{ $slot }}
                </main>

                {{-- BOTTOM NAV MOBILE --}}
                <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white dark:bg-gray-900 border-t flex justify-around py-2">
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-yellow-500 text-xs">
                        <span class="text-xl leading-none">⌂</span>
                        <span>Home</span>
                    </a>
                    <button class="flex flex-col items-center text-gray-500 text-xs">
                        <span class="text-xl leading-none">🔍</span>
                        <span>Search</span>
                    </button>
                    <button class="flex flex-col items-center text-gray-500 text-xs">
                        <span class="text-2xl leading-none">+</span>
                        <span>Add</span>
                    </button>
                    <button class="flex flex-col items-center text-gray-500 text-xs">
                        <span class="text-xl leading-none">👤</span>
                        <span>Profile</span>
                    </button>
                </nav>
            </div>
        </div>
    </body>
</html>