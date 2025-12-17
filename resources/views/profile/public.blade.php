<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8" x-data="{ activeTab: 'foto' }">
            @php
                $viewer = auth()->user();
                $isOwnProfileView = $viewer && $viewer->id === $user->id;
            @endphp

            <div class="flex flex-col items-center">
                <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center">
                    @if (!empty($user->avatar))
                        <img
                            src="{{ asset('storage/'.$user->avatar) }}"
                            alt="Avatar"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <span class="text-3xl text-gray-500">👤</span>
                    @endif
                </div>

                @php
                    $canDeleteUser = $viewer && $viewer->username === 'admin' && $viewer->id !== $user->id;
                @endphp

                @if ($canDeleteUser)
                    <form
                        method="POST"
                        action="{{ route('admin.users.destroy', $user) }}"
                        class="mt-3"
                        onsubmit="return confirm('Yakin hapus akun ini beserta semua postingan dan komentarnya?');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="px-3 py-1 text-[11px] font-semibold text-red-600 hover:text-red-800 border border-red-500 hover:border-red-700 rounded-full bg-white"
                        >
                            Hapus akun ini
                        </button>
                    </form>
                @endif

                <div class="mt-4 flex items-center gap-2">
                    <h1 class="text-lg font-semibold tracking-wide text-gray-800">
                        {{ strtoupper($user->name ?? $user->username) }}
                    </h1>

                    @if (!empty($isTopLikeThisMonth) && $isTopLikeThisMonth)
                        <div class="relative group" aria-label="Top Like">
                            <div class="h-6 w-6 flex items-center justify-center">
                                <img src="{{ asset('images/trophy.png') }}" alt="Top Like" class="h-6 w-6 object-contain">
                            </div>

                            <div class="pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150 absolute left-1/2 -translate-x-1/2 top-full mt-1 whitespace-nowrap z-10 bg-gray-900 text-white text-[11px] px-2 py-1 rounded-md shadow-lg">
                                Top Like bulan {{ $topLikeMonthLabel ?? '' }}
                            </div>
                        </div>
                    @endif
                </div>

                @if (!empty($user->bio))
                    <p class="mt-1 text-sm text-gray-600 text-center max-w-sm">
                        {{ \Illuminate\Support\Str::words($user->bio, 5, '...') }}
                    </p>
                @endif

                <div class="mt-2 flex items-center gap-8 text-sm text-gray-600">
                    <span>{{ $postsCount }} Posts</span>
                    <span>{{ $likesCount }} Like</span>
                </div>
            </div>

            <div class="mt-8 flex justify-center text-sm text-gray-600 border-b border-gray-300">
                <button
                    type="button"
                    class="px-6 py-2 -mb-px border-b-2"
                    :class="activeTab === 'foto' ? 'border-gray-900 text-gray-900' : 'border-transparent'"
                    @click="activeTab = 'foto'"
                >
                    Foto
                </button>
                <button
                    type="button"
                    class="px-6 py-2 -mb-px border-b-2"
                    :class="activeTab === 'video' ? 'border-gray-900 text-gray-900' : 'border-transparent'"
                    @click="activeTab = 'video'"
                >
                    Video
                </button>
            </div>

            <div class="mt-4" x-show="activeTab === 'foto'">
                <div class="grid grid-cols-3 gap-[1px]">
                    @forelse ($photos as $item)
                        <div class="bg-black aspect-square overflow-hidden">
                            <img
                                src="{{ asset('storage/'.$item->path) }}"
                                alt=""
                                class="w-full h-full object-cover"
                            >
                        </div>
                    @empty
                        <p class="col-span-3 text-center text-xs text-gray-500 py-8 bg-white">Belum ada foto.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4" x-show="activeTab === 'video'">
                <div class="grid grid-cols-3 gap-[1px]">
                    @forelse ($videos as $item)
                        <div class="bg-black aspect-square overflow-hidden">
                            @if ($item->thumbnail_path)
                                <img
                                    src="{{ asset('storage/'.$item->thumbnail_path) }}"
                                    alt="Thumbnail video"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <video
                                    class="w-full h-full object-cover"
                                    muted
                                    playsinline
                                    preload="metadata"
                                    @mouseenter="if ($event.target.paused) { $event.target.play().catch(() => {}); }"
                                    @mouseleave="$event.target.pause(); $event.target.currentTime = 0;"
                                >
                                    <source src="{{ route('media.view', $item) }}" type="{{ $item->type }}">
                                </video>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-3 text-center text-xs text-gray-500 py-8 bg-white">Belum ada video.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
