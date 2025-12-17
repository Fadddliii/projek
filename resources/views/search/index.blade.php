<x-app-layout>
    <div class="min-h-screen bg-white">
        <div class="w-full max-w-6xl mx-auto flex">
            {{-- Panel kiri: Search seperti Instagram --}}
            <div class="w-full md:w-80 border-r border-gray-200 min-h-screen py-6 px-4">
                <h1 class="text-xl font-semibold mb-4">Search</h1>

                <form method="GET" action="{{ route('search.index') }}" class="mb-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            🔍
                        </span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $query }}"
                            placeholder="Search"
                            class="w-full pl-9 pr-3 py-2 rounded-xl bg-gray-100 focus:bg-white border border-transparent focus:border-gray-300 focus:outline-none text-sm"
                        >
                    </div>
                </form>

                @if ($query === '')
                    <p class="text-sm text-gray-500">Mulai ketik untuk mencari akun berdasarkan nama atau username.</p>
                @else
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase tracking-wide text-gray-500">Results</span>
                        <span class="text-xs text-gray-400">{{ $results->count() }} found</span>
                    </div>

                    @if ($results->isEmpty())
                        <p class="text-sm text-gray-500">Tidak ada akun yang cocok.</p>
                    @else
                        <ul class="space-y-2 overflow-y-auto max-h-[70vh] pr-1">
                            @foreach ($results as $user)
                                <li>
                                    <a
                                        href="{{ route('profiles.show', $user) }}"
                                        class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-gray-100 cursor-pointer"
                                    >
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-500">
                                            {{ strtoupper(substr($user->name ?? $user->username, 0, 2)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            {{-- Baris utama: nama lengkap --}}
                                            <span class="text-sm font-semibold text-gray-900">{{ $user->name ?? $user->username }}</span>
                                            {{-- Baris kecil: mapel / username --}}
                                            <span class="text-xs text-gray-500">{{ $user->username }}</span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>

            {{-- Panel kanan: grid media agar gambar tetap terlihat --}}
            <div class="hidden md:flex flex-1 items-start justify-center py-6 px-4">
                @if (isset($media) && $media->count() > 0)
                    <div class="grid grid-cols-3 gap-[2px] bg-gray-200 max-w-xl w-full">
                        @foreach ($media as $item)
                            @php
                                $type = $item->type ?? '';
                                $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                                $docLabel = strtoupper($extension ?: 'DOC');
                                $fileTitle = \Illuminate\Support\Str::limit(basename($item->path), 16);
                            @endphp

                            <div class="aspect-square bg-gray-200 overflow-hidden border border-white">
                                @if (strpos($type, 'image/') === 0)
                                    <img
                                        src="{{ asset('storage/'.$item->path) }}"
                                        class="w-full h-full object-cover"
                                        alt=""
                                    >
                                @elseif (strpos($type, 'video/') === 0)
                                    <video
                                        class="w-full h-full object-cover"
                                        muted
                                        playsinline
                                        preload="metadata"
                                        @loadeddata="$event.target.currentTime = 0.1; $event.target.pause();"
                                    >
                                        <source src="{{ asset('storage/'.$item->path) }}" type="{{ $type }}">
                                    </video>
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-indigo-900 px-2 py-2">
                                        <div class="w-full h-full rounded-2xl bg-indigo-950 flex flex-col p-2">
                                            <div class="flex items-center gap-1 mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-500 text-[9px] font-semibold text-white">
                                                    {{ $docLabel }}
                                                </span>
                                                <span class="flex-1 text-[9px] font-semibold text-white truncate">
                                                    {{ $fileTitle }}
                                                </span>
                                            </div>
                                            <div class="flex-1 flex items-center justify-center bg-white rounded-xl overflow-hidden">
                                                <span class="text-[10px] font-medium text-gray-700 text-center px-2">
                                                    Preview dokumen
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center">
                        <p class="text-sm text-gray-400">Belum ada media yang diupload.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
