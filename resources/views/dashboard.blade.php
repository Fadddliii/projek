<x-app-layout>
    <div class="min-h-screen md:py-8 md:px-8 pb-20 md:pb-0">
        <div class="max-w-5xl mx-auto">
            {{-- Grid 3 kolom, seperti explore --}}
            <div class="grid grid-cols-3 gap-0">
                @forelse ($media as $item)
                    <div class="aspect-square bg-gray-200 overflow-hidden">
                        @php $type = $item->type ?? ''; @endphp

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
                                loop
                            >
                                <source src="{{ asset('storage/'.$item->path) }}" type="{{ $type }}">
                            </video>
                        @endif
                    </div>
                @empty
                    {{-- Kalau belum ada media sama sekali, tampilkan 3x3 kotak warna contoh --}}
                    {{-- Baris 1 --}}
                    <div class="aspect-square bg-black"></div>
                    <div class="aspect-square bg-neutral-900"></div>
                    <div class="aspect-square bg-neutral-700"></div>

                    {{-- Baris 2 --}}
                    <div class="aspect-square bg-neutral-700"></div>
                    <div class="aspect-square bg-neutral-500"></div>
                    <div class="aspect-square bg-gray-400"></div>

                    {{-- Baris 3 --}}
                    <div class="aspect-square bg-gray-400"></div>
                    <div class="aspect-square bg-gray-300"></div>
                    <div class="aspect-square bg-rose-400"></div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>