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
                <p class="font-normal tracking-wide text-gray-800 text-[52px] md:text-[56px] leading-none">BUAT AKUN,</p>
                <p class="font-extrabold tracking-wide text-gray-900 mt-0 text-[52px] md:text-[56px] leading-none">GABUNG BERSAMA</p>
                <p class="mt-5 text-lg md:text-2xl text-gray-600 whitespace-nowrap leading-snug">Daftar untuk mulai berbagi dan berinteraksi.</p>
            </div>
        </div>

        {{-- RIGHT SIDE: Dark panel with register form --}}
        <div class="w-full md:w-2/5 bg-slate-900 flex items-center justify-center px-8 py-12">
            <div
                class="w-full max-w-sm"
                x-data="{
                    mode: '{{ old('role', 'siswa') }}',
                    nisn: '{{ old('role', 'siswa') === 'siswa' ? old('email') : '' }}',
                    subject: '{{ old('role') === 'guru' ? old('email') : '' }}',
                }"
            >
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- Toggle jenis akun: Siswa / Guru --}}
                    <div class="flex items-center gap-2 mb-2">
                        <button
                            type="button"
                            @click="mode = 'siswa'"
                            :class="mode === 'siswa' ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-200'"
                            class="px-4 py-1 text-xs font-semibold border border-white/40"
                        >
                            Siswa
                        </button>
                        <button
                            type="button"
                            @click="mode = 'guru'"
                            :class="mode === 'guru' ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-200'"
                            class="px-4 py-1 text-xs font-semibold border border-white/40"
                        >
                            Guru
                        </button>

                        {{-- Kirim jenis akun ke backend: siswa / admin (untuk guru) --}}
                        <input type="hidden" name="role" :value="mode === 'siswa' ? 'siswa' : 'admin'">
                    </div>

                    {{-- Nama lengkap --}}
                    <div>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Nama lengkap"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-red-300" />
                    </div>

                    {{-- Kelas (hanya untuk siswa) --}}
                    <div x-show="mode === 'siswa'">
                        <input
                            id="kelas"
                            type="text"
                            name="kelas"
                            value="{{ old('kelas') }}"
                            autocomplete="off"
                            placeholder="Kelas"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                    </div>

                    {{-- NISN (untuk siswa) --}}
                    <div x-show="mode === 'siswa'">
                        <input
                            id="nisn"
                            type="text"
                            x-model="nisn"
                            :name="mode === 'siswa' ? 'email' : null"
                            :disabled="mode !== 'siswa'"
                            required
                            autocomplete="off"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="NISN"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-300" />
                    </div>

                    {{-- Mata pelajaran (untuk guru) --}}
                    <div x-show="mode === 'guru'">
                        <select
                            id="subject"
                            x-model="subject"
                            :name="mode === 'guru' ? 'email' : null"
                            :disabled="mode !== 'guru'"
                            required
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                            <option value="" disabled selected>Pilih mata pelajaran</option>
                            <option value="Matematika">Matematika</option>
                            <option value="B.Indonesia">B.Indonesia</option>
                            <option value="B.Inggris">B.Inggris</option>
                            <option value="PJOK">PJOK</option>
                            <option value="Seni Budaya">Seni Budaya</option>
                            <option value="IPAS">IPAS</option>
                            <option value="PAI">PAI</option>
                            <option value="PKN">PKN</option>
                        </select>
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-300" />
                    </div>

                    {{-- Username (untuk login dan identitas akun) --}}
                    <div>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
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
                            autocomplete="new-password"
                            placeholder="Password"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-300" />
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Konfirmasi password"
                            class="w-full px-3 py-2 bg-white text-gray-900 text-sm border border-white focus:outline-none focus:ring-2 focus:ring-blue-300"
                        >
                    </div>

                    <div class="pt-4 space-y-3">
                        <button
                            type="submit"
                            class="w-32 py-2 bg-white text-slate-900 text-sm font-semibold tracking-wide hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-slate-900"
                        >
                            Daftar
                        </button>

                        <p class="text-xs text-slate-200">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="underline hover:text-white">Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
