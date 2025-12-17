<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>CN | CAMPUS CONNECT </title>

        <link rel="icon" type="image/png" href="{{ asset('images/icb.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white dark:bg-gray-900" x-data="searchModalState()" x-init="init()" @keydown.escape.window="openSearch = false">
        <div class="min-h-screen flex">

           <aside
    id="app-sidebar"
    class="hidden md:flex group/nav fixed inset-y-0 left-0 w-44
           flex-col bg-white dark:bg-gray-900 py-6 z-20">

    {{-- HEADER: Logo di atas --}}
<div class="flex items-center px-3">
    <div class="h-12 w-12 rounded-full overflow-hidden flex items-center justify-center">
        <img src="{{ asset('images/icb.png') }}" class="h-11 w-11 object-contain" alt="Logo">
    </div>
</div>

    {{-- NAV: ikon + text, di TENGAH --}}
    <nav class="flex-1 flex flex-col gap-6 justify-center items-start mt-8 mb-8 text-gray-500">
        {{-- Home --}}
        <a href="{{ route('dashboard') }}"
           class="group flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-xl transition-colors duration-200">
            <div class="flex items-center justify-center h-9 w-9">
                <img src="{{ asset('images/home.png') }}" alt="Home" class="h-7 w-7 object-contain">
            </div>
            <span
                class="ml-3 text-sm text-gray-800 whitespace-nowrap overflow-hidden
                       w-0 opacity-0
                       group-hover/nav:w-auto group-hover/nav:opacity-100
                       transition-all duration-200 ease-out"
            >
                Home
            </span>
        </a>

        {{-- Search --}}
        <button
            type="button"
            @click.prevent="openSearch = true; $nextTick(() => { const el = document.getElementById('global-search-input'); if (el) el.focus(); });"
            class="group flex items-center px-3 py-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors duration-200">
            <div class="flex items-center justify-center h-9 w-9">
                <img src="{{ asset('images/search.png') }}" alt="Search" class="h-7 w-7 object-contain">
            </div>
            <span
                class="ml-3 text-sm text-gray-800 whitespace-nowrap overflow-hidden
                       w-0 opacity-0
                       group-hover/nav:w-auto group-hover/nav:opacity-100
                       transition-all duration-200 ease-out"
            >
                Search
            </span>
        </button>

        {{-- Create: submenu Foto / Video / Tugas dengan konsep seperti More (label hilang, diganti menu vertikal di kanan icon) --}}
        <div class="group flex items-center px-3 py-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors duration-200 cursor-pointer">
            <div class="flex items-center justify-center h-9 w-9">
                <img src="{{ asset('images/creat.png') }}" alt="Create" class="h-7 w-7 object-contain">
            </div>

            {{-- Label Create (default) --}}
            <span
                class="ml-3 text-sm text-gray-800 whitespace-nowrap overflow-hidden
                       w-0 opacity-0
                       group-hover/nav:w-auto group-hover/nav:opacity-100
                       group-hover:hidden
                       transition-all duration-200 ease-out"
            >
                Create
            </span>

            {{-- Menu tipe upload – muncul menggantikan teks Create saat hover baris Create --}}
            <div
                class="ml-3 flex flex-col text-xs text-gray-700 space-y-1 overflow-hidden
                       w-0 opacity-0 pointer-events-none
                       group-hover:w-auto group-hover:opacity-100 group-hover:pointer-events-auto
                       transition-all duration-200 ease-out"
            >
                <a href="{{ route('media.create', ['type' => 'photo']) }}" class="px-1 py-0.5 rounded hover:bg-gray-100 hover:text-gray-900 cursor-pointer">Foto</a>
                <a href="{{ route('media.create', ['type' => 'video']) }}" class="px-1 py-0.5 rounded hover:bg-gray-100 hover:text-gray-900 cursor-pointer">Video</a>
                <a href="{{ route('media.create', ['type' => 'document']) }}" class="px-1 py-0.5 rounded hover:bg-gray-100 hover:text-gray-900 cursor-pointer">Tugas</a>
            </div>
        </div>

        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}"
   class="group flex items-center px-3 py-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors duration-200">
    <div class="flex items-center justify-center h-9 w-9">
        <img src="{{ asset('images/profile.png') }}" alt="Profile" class="h-7 w-7 object-contain">
    </div>
    <span
    class="ml-3 text-sm text-gray-800 whitespace-nowrap overflow-hidden
           w-0 opacity-0
           group-hover/nav:w-auto group-hover/nav:opacity-100
           transition-all duration-200 ease-out"
>
    Profile
</span>
</a>
    </nav>

   {{-- FOOTER: Hamburger + More di bawah --}}
<div class="relative pt-4 w-full mb-2">
    <div class="group flex items-center px-3 py-2 hover:bg-gray-100 rounded-xl transition-colors duration-200 cursor-pointer">

        {{-- Icon More --}}
        <div class="flex items-center justify-center h-9 w-9">
            <img src="{{ asset('images/more.jpg') }}" alt="More" class="h-7 w-7 object-contain">
        </div>

        {{-- Label "More" – muncul saat hover sidebar, hilang saat hover baris More --}}
        <span
            class="ml-3 text-sm text-gray-800 whitespace-nowrap overflow-hidden
                   w-0 opacity-0
                   group-hover/nav:w-auto group-hover/nav:opacity-100
                   group-hover:hidden
                   transition-all duration-200 ease-out"
        >
            More
        </span>

        {{-- Menu Logout – muncul menggantikan teks More saat hover baris More --}}
        <div
            class="ml-3 flex flex-col gap-1 overflow-hidden
                   w-0 opacity-0 pointer-events-none
                   group-hover:w-auto group-hover:opacity-100 group-hover:pointer-events-auto
                   transition-all duration-200 ease-out"
        >
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="text-sm text-red-600 hover:underline text-left">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>

</aside>
            {{-- KONTEN UTAMA --}}
<div class="flex-1 flex flex-col min-h-screen md:ml-44">
                {{-- TOPBAR MOBILE --}}
                <header
                    id="app-topbar-mobile"
                    x-data="{ open: false }"
                    class="md:hidden relative flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-b"
                >
                    <div class="h-8 w-8 rounded-full overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('images/icb.png') }}" alt="Logo" class="h-8 w-8 object-contain">
                    </div>
                    <button @click="open = ! open" class="h-8 w-8 flex items-center justify-center rounded-full hover:bg-gray-100 cursor-pointer transition-colors duration-200">
                        <span class="text-2xl">≡</span>
                    </button>

                    {{-- Panel More (Settings & Logout) di mobile: slide dari kanan, menutupi hampir seluruh layar --}}
                    <div
                        x-show="open"
                        x-transition.opacity
                        class="fixed inset-0 z-30 flex"
                    >
                        {{-- Area kiri transparan untuk menutup panel --}}
                        <div class="flex-1 bg-black/30" @click="open = false"></div>

                        {{-- Panel kanan --}}
                        <div
                            class="w-4/5 max-w-xs h-full bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-xl flex flex-col py-4 text-sm"
                        >
                            <div class="px-4 pb-3 font-semibold text-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                                More
                            </div>

                            <form method="POST" action="{{ route('login') }}" class="mt-1">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full text-left px-4 py-3 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1">
                    {{ $slot }}
                </main>

                {{-- BOTTOM NAV MOBILE --}}
                <nav
                    id="app-bottomnav-mobile"
                    class="md:hidden fixed bottom-0 inset-x-0 bg-white dark:bg-gray-900 border-t flex justify-around py-2"
                >
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-yellow-500 text-xs">
                        <img src="{{ asset('images/home.png') }}" alt="Home" class="h-6 w-6 object-contain">
                        <span>Home</span>
                    </a>
                    <button
                        type="button"
                        @click.prevent="openSearch = true; $nextTick(() => { const el = document.getElementById('global-search-input'); if (el) el.focus(); });"
                        class="flex flex-col items-center text-gray-500 text-xs"
                    >
                        <img src="{{ asset('images/search.png') }}" alt="Search" class="h-6 w-6 object-contain">
                        <span>Search</span>
                    </button>
                    <a href="{{ route('media.create') }}" class="flex flex-col items-center text-gray-500 text-xs">
                        <img src="{{ asset('images/creat.png') }}" alt="Create" class="h-6 w-6 object-contain">
                        <span>Add</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center text-gray-500 text-xs">
                        <img src="{{ asset('images/profile.png') }}" alt="Profile" class="h-6 w-6 object-contain">
                        <span>Profile</span>
                    </a>
                </nav>
            </div>
        </div>

        {{-- Popup Search global --}}
        <div
            x-show="openSearch"
            x-transition.opacity
            @click.self="openSearch = false"
            class="fixed inset-0 z-40 flex items-stretch bg-transparent"
        >
            {{-- Panel kiri (search) --}}
            <div
                class="w-full max-w-xs md:max-w-sm bg-white dark:bg-gray-900 h-full md:h-full md:ml-44 rounded-none md:rounded-r-2xl shadow-2xl flex flex-col transform"
                x-transition:enter="transform transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
            >
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-800 dark:text-gray-100">Search</h2>
                </div>

                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <img src="{{ asset('images/search.png') }}" alt="Search" class="h-4 w-4 object-contain">
                        </span>
                        <input
                            id="global-search-input"
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.300ms="performSearch()"
                            placeholder="Search users..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 focus:bg-white dark:focus:bg-gray-900 border border-transparent focus:border-gray-300 dark:focus:border-gray-600 focus:outline-none text-sm text-gray-900 dark:text-gray-100"
                        >
                    </div>
                </div>

                {{-- Daftar recent akun yang pernah dicari (muncul setelah pernah search) --}}
                <div class="px-4 pt-2 pb-3 border-b border-gray-200 dark:border-gray-800" x-show="hasSearched || userHistory.length > 0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[11px] uppercase tracking-wide text-gray-400">Recent</p>
                        <button
                            type="button"
                            class="text-[11px] font-medium text-blue-500 hover:text-blue-600"
                            x-show="userHistory.length > 0"
                            @click="clearUserHistory()"
                        >
                            Clear all
                        </button>
                    </div>

                    <template x-if="userHistory.length === 0">
                        <p class="text-[11px] text-gray-400">Belum ada recent history.</p>
                    </template>

                    <ul class="space-y-2 max-h-40 overflow-y-auto" x-show="userHistory.length > 0">
                        <template x-for="item in userHistory" :key="item.id">
                            <li class="flex items-center justify-between gap-2">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 flex-1 px-1 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                    @click="if (item.profile_url) { window.location.href = item.profile_url; openSearch = false; }"
                                >
                                    <div class="h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-600">
                                        <span x-text="(item.username || item.name || '?').slice(0,2).toUpperCase()"></span>
                                    </div>
                                    <div class="flex flex-col items-start">
                                        {{-- Baris utama: username --}}
                                        <span class="text-xs font-semibold text-gray-900 dark:text-gray-100" x-text="item.username"></span>
                                        {{-- Baris kedua: kelas (kosong jika belum diisi) --}}
                                        <span class="text-[11px] text-gray-500" x-text="item.kelas || ''"></span>
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    class="text-xs text-gray-400 hover:text-gray-600 px-1"
                                    @click="removeFromUserHistory(item.id)"
                                >
                                    ✕
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="flex-1 overflow-y-auto p-2">
                    <template x-if="loading">
                        <p class="text-xs text-gray-500 px-2 py-1">Mencari...</p>
                    </template>

                    {{-- Pesan bantuan hanya saat benar-benar belum pernah search akun apapun --}}
                    <template x-if="!loading && !hasSearched">
                        <p class="text-xs text-gray-500 px-2 py-1">Ketik nama atau username untuk mencari akun.</p>
                    </template>

                    <ul class="space-y-1" x-show="results.length > 0">
                        <template x-for="user in results" :key="user.id">
                            <li>
                                <div
                                    class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer"
                                    @click="addUserToHistory(user); if (user.profile_url) { window.location.href = user.profile_url; openSearch = false; }"
                                >
                                    <div class="h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-600">
                                        <span x-text="(user.username || user.name || '?').slice(0,2).toUpperCase()"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        {{-- Baris utama: username --}}
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="user.username"></span>
                                        {{-- Baris kecil: kelas (kosong jika belum diisi) --}}
                                        <span class="text-xs text-gray-500" x-text="user.kelas || ''"></span>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Area kanan transparan agar konten utama tetap terlihat, klik untuk menutup --}}
            <div class="flex-1 hidden md:block" @click="openSearch = false"></div>
        </div>

        <script>
            function searchModalState() {
                return {
                    openSearch: false,
                    searchQuery: '',
                    results: [],
                    loading: false,
                    // history teks masih ada tapi tidak dipakai di UI; fokus utama userHistory (akun)
                    history: [],
                    userHistory: [],
                    hasSearched: false,
                    init() {
                        // Load history akun dari localStorage saat pertama kali halaman dimuat
                        try {
                            const raw = window.localStorage.getItem('cn_search_user_history');
                            if (raw) {
                                const parsed = JSON.parse(raw);
                                if (Array.isArray(parsed)) {
                                    this.userHistory = parsed;
                                    if (this.userHistory.length > 0) {
                                        this.hasSearched = true;
                                    }
                                }
                            }
                        } catch (e) {
                            this.userHistory = [];
                        }
                    },
                    saveUserHistory() {
                        try {
                            window.localStorage.setItem('cn_search_user_history', JSON.stringify(this.userHistory));
                        } catch (e) {}
                    },
                    addToHistory(q) {
                        q = (q || '').trim();
                        if (!q) return;

                        const existingIndex = this.history.indexOf(q);
                        if (existingIndex !== -1) {
                            this.history.splice(existingIndex, 1);
                        }

                        this.history.unshift(q);
                        if (this.history.length > 10) {
                            this.history.pop();
                        }
                    },
                    addUserToHistory(user) {
                        if (!user || !user.id) return;

                        const existingIndex = this.userHistory.findIndex(u => u.id === user.id);
                        if (existingIndex !== -1) {
                            this.userHistory.splice(existingIndex, 1);
                        }

                        // Simpan hanya field yang dipakai agar ringan
                        this.userHistory.unshift({
                            id: user.id,
                            name: user.name || null,
                            username: user.username || '',
                            kelas: user.kelas || null,
                            profile_url: user.profile_url || null,
                        });

                        if (this.userHistory.length > 10) {
                            this.userHistory.pop();
                        }

                        this.saveUserHistory();
                    },
                    removeFromUserHistory(id) {
                        this.userHistory = this.userHistory.filter(u => u.id !== id);
                        this.saveUserHistory();
                    },
                    clearUserHistory() {
                        this.userHistory = [];
                        this.saveUserHistory();
                    },
                    performSearch() {
                        const q = this.searchQuery.trim();
                        if (!q) {
                            this.results = [];
                            return;
                        }

                        this.hasSearched = true;
                        this.addToHistory(q);

                        this.loading = true;
                        axios.get('{{ route('search.ajax') }}', { params: { q } })
                            .then((response) => {
                                this.results = response.data.results || [];

                                // Otomatis tambahkan hasil pertama ke recent seperti Instagram
                                if (Array.isArray(this.results) && this.results.length > 0) {
                                    this.addUserToHistory(this.results[0]);
                                }
                            })
                            .catch(() => {
                                this.results = [];
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    },
                };
            }
        </script>

        @stack('scripts')
    </body>
</html>