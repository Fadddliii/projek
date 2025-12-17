<x-guest-layout>
    <div class="min-h-screen flex flex-col md:flex-row">
        {{-- LEFT SIDE: Logo + Welcome text --}}
        <div class="w-full md:w-3/5 bg-white flex flex-col px-8 md:px-16 py-8 mt-10 md:mt-14 relative">
            {{-- Logo + brand di bagian atas --}}
            <div class="flex items-center gap-4 mb-16 pl-8 md:pl-12">
                <div class="h-24 w-24 md:h-28 md:w-28 flex items-center justify-center">
                    <img src="{{ asset('images/icb.png') }}" alt="Logo" class="h-24 w-24 md:h-28 md:w-28 object-contain">
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-lg md:text-2xl font-semibold text-gray-800">CN</span>
                    <span class="text-base md:text-xl font-semibold text-gray-700">Campus Connect</span>
                </div>
            </div>

            {{-- Teks sambutan di tengah (atas-bawah) kolom kiri --}}
            <div class="absolute left-16 md:left-28 top-1/2 -translate-y-1/2 text-left">
                <p class="font-normal tracking-wide text-gray-800 text-[64px] md:text-[68px] leading-none">HALO,</p>
                <p class="font-extrabold tracking-wide text-gray-900 mt-0 text-[64px] md:text-[68px] leading-none">SELAMAT DATANG</p>
                <p class="mt-5 text-lg md:text-2xl text-gray-600 whitespace-nowrap leading-snug">Masuk untuk mulai berbagi dan berinteraksi.</p>
            </div>
        </div>

        {{-- RIGHT SIDE: Dark panel with login form --}}
        <div class="w-full md:w-2/5 bg-slate-900 flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-sm">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4 text-white" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Username"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                        <x-input-error :messages="$errors->get('username')" class="mt-1 text-xs text-red-300" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-300" />
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            class="w-32 py-2 bg-white text-slate-900 text-sm font-semibold tracking-wide hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-slate-900"
                        >
                            Login
                        </button>
                    </div>
                </form>

                <div class="pt-4 space-y-3">
                    <p class="text-xs text-slate-200">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="underline hover:text-white">Daftar</a>
                        atau
                        <button
                            type="submit"
                            form="guest-login-form"
                            class="underline hover:text-white"
                        >
                            Guest
                        </button>
                    </p>

                    <form id="guest-login-form" method="POST" action="{{ route('login.guest') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
