<x-app-layout>
    <div class="min-h-screen md:py-8 md:px-8 pb-20 md:pb-0">
        <div class="w-full" x-data="{
            open: false,
            items: [],
            currentIndex: null,
            activeId: null,
            activeSrc: '',
            activeType: '',
            liked: false,
            likeCount: 0,
            comments: [],
            newComment: '',
            ownerName: '',
            showMobileFeed: false,
            mobileStartIndex: 0,
            desktopActiveId: null,
            filterType: 'all',
            categoryFromType(mime) {
                if (!mime) return 'document';
                if (mime.startsWith('image/')) return 'image';
                if (mime.startsWith('video/')) return 'video';
                return 'document';
            },
            registerMedia(item) {
                this.items.push(item);
            },
            openMobileFeedFrom(index) {
                this.showMobileFeed = true;
                this.mobileStartIndex = index;
                this.$nextTick(() => {
                    const refKey = 'mobilePost_' + index;
                    const el = this.$refs[refKey];
                    if (el && el.scrollIntoView) {
                        el.scrollIntoView({ behavior: 'auto', block: 'start' });
                    }
                });
            },
            setMediaByIndex(index) {
                const item = this.items[index];
                if (!item) return;
                this.resetVideo();
                this.open = true;
                this.currentIndex = index;
                this.activeId = item.id;
                this.activeSrc = item.src;
                this.activeType = item.type;
                this.liked = item.liked;
                this.likeCount = item.likeCount;
                this.comments = item.comments || [];
                this.ownerName = item.owner_name || '';
                this.newComment = '';
            },
            nextMedia() {
                if (this.currentIndex === null || this.items.length === 0) return;
                const next = (this.currentIndex + 1) % this.items.length;
                this.setMediaByIndex(next);
            },
            prevMedia() {
                if (this.currentIndex === null || this.items.length === 0) return;
                const prev = (this.currentIndex - 1 + this.items.length) % this.items.length;
                this.setMediaByIndex(prev);
            },
            toggleLike() {
                if (!this.activeId) return;
                axios.post('/media/' + this.activeId + '/like')
                    .then(response => {
                        this.liked = response.data.liked;
                        this.likeCount = response.data.count;
                        if (this.currentIndex !== null && this.items[this.currentIndex]) {
                            this.items[this.currentIndex].liked = this.liked;
                            this.items[this.currentIndex].likeCount = this.likeCount;
                        }
                    });
            },
            postComment() {
                if (!this.activeId || !this.newComment.trim()) return;
                axios.post('/media/' + this.activeId + '/comments', { body: this.newComment })
                    .then(response => {
                        const newItem = {
                            id: response.data.id,
                            user_name: response.data.user.username,
                            body: response.data.body,
                            delete_url: response.data.delete_url || null,
                            liked: false,
                            like_count: 0,
                        };
                        this.comments.push(newItem);
                        if (this.currentIndex !== null && this.items[this.currentIndex]) {
                            this.items[this.currentIndex].comments.push(newItem);
                        }
                        this.newComment = '';
                    });
            },
            toggleCommentLike(comment) {
                if (!comment || !comment.id) return;

                axios.post('/comments/' + comment.id + '/like')
                    .then(response => {
                        comment.liked = response.data.liked;
                        comment.like_count = response.data.count;
                    })
                    .catch(() => {
                        // Abaikan error (misal guest diblok di backend)
                    });
            },
            resetVideo() {
                if (this.$refs.player) {
                    this.$refs.player.pause();
                    this.$refs.player.currentTime = 0;
                }
            },
            closeModal() {
                this.resetVideo();
                this.open = false;
                this.activeSrc = '';
                this.activeType = '';
                this.activeId = null;
            },
        }">
            @php
                $viewer = auth()->user();
                $isGuestViewer = $viewer && (($viewer->role ?? null) === 'guest' || $viewer->username === 'guest');
            @endphp

            <div class="flex gap-4 h-full md:pr-0">
                <div class="flex-1">

            {{-- Filter chips di atas feed (sticky saat scroll) --}}
            <div class="pt-4 pb-2 px-4 md:px-0 flex justify-center gap-2 bg-white sticky top-0 z-20">
                <button
                    type="button"
                    class="px-4 py-1.5 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap"
                    :class="filterType === 'all'
                        ? 'bg-[#2F3F57] text-white'
                        : 'bg-gray-100 text-gray-700'"
                    @click="filterType = 'all'"
                >
                    Semua
                </button>
                <button
                    type="button"
                    class="px-4 py-1.5 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap"
                    :class="filterType === 'image'
                        ? 'bg-[#2F3F57] text-white'
                        : 'bg-gray-100 text-gray-700'"
                    @click="filterType = 'image'"
                >
                    Foto
                </button>
                <button
                    type="button"
                    class="px-4 py-1.5 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap"
                    :class="filterType === 'video'
                        ? 'bg-[#2F3F57] text-white'
                        : 'bg-gray-100 text-gray-700'"
                    @click="filterType = 'video'"
                >
                    Video
                </button>
            </div>

            @if ($isGuestViewer)
                <div class="px-4 md:px-0 mt-3" x-data="{ showGuestNotice: true }" x-show="showGuestNotice">
                    <div class="max-w-xl mx-auto bg-blue-50 border border-blue-200 text-[11px] md:text-xs text-blue-900 rounded-xl px-3 py-2 flex items-center justify-between gap-3">
                        <p class="leading-snug">
                            Anda saat ini menggunakan <span class="font-semibold">akun guest</span>. Login atau daftar akun untuk bisa like, komentar, dan upload postingan.
                        </p>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-3 py-1 rounded-full bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700"
                                >
                                    Login / Daftar
                                </button>
                            </form>
                            <button
                                type="button"
                                class="text-xs text-blue-500 hover:text-blue-700"
                                @click="showGuestNotice = false"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- GRID MOBILE lama disembunyikan (beranda langsung feed scroll) --}}
            <div class="grid grid-cols-4 gap-0 md:hidden" x-show="false">
                @forelse ($media as $item)
                    @php
                        $type = $item->type ?? '';
                    @endphp

                    <div
                        class="aspect-square bg-gray-200 overflow-hidden border border-white cursor-pointer"
                        x-show="filterType === 'all' || categoryFromType('{{ $type }}') === filterType"
                        @click="
                            if (categoryFromType('{{ $type }}') === 'document') {
                                window.open('{{ route('media.view', $item) }}', '_blank');
                            } else {
                                openMobileFeedFrom({{ $loop->index }});
                            }
                        "
                    >
                        @if (strpos($type, 'image/') === 0)
                            <img
                                src="{{ asset('storage/'.$item->path) }}"
                                class="w-full h-full object-cover"
                                alt=""
                            >
                        @elseif (strpos($type, 'video/') === 0)
                            @if ($item->thumbnail_path)
                                <img
                                    src="{{ asset('storage/'.$item->thumbnail_path) }}"
                                    class="w-full h-full object-cover"
                                    alt="Thumbnail video"
                                >
                            @else
                                <video
                                    class="w-full h-full object-cover"
                                    muted
                                    playsinline
                                    preload="metadata"
                                    @loadeddata="$event.target.currentTime = 0.1; $event.target.pause();"
                                >
                                    <source src="{{ asset('storage/'.$item->path) }}" type="{{ $type }}">
                                </video>
                            @endif
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                <span class="text-[10px] md:text-xs font-medium text-gray-600">Dokumen</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm col-span-3 py-8">Belum ada postingan.</p>
                @endforelse
            </div>

            {{-- FEED MOBILE: beranda langsung scroll postingan --}}
            <div class="md:hidden space-y-6 py-4">
                @forelse ($media as $item)
                    @php
                        $type = $item->type ?? '';
                        $likeCount = $item->likes_count ?? 0;
                        $likedByMe = $item->likes->isNotEmpty();

                        $ownerUser = optional($item->user);
                        $ownerUsername = $ownerUser->username ?? 'username';
                        $ownerRole = $ownerUser->role ?? null;
                        $ownerClass = $ownerUser->kelas ?? null;

                        // Untuk guru/admin, gunakan email sebagai mapel (kecuali super admin 'admin')
                        $ownerMapel = null;
                        if ($ownerRole === 'admin') {
                            if (($ownerUser->username ?? null) === 'admin') {
                                $ownerMapel = 'Admin';
                            } else {
                                $ownerMapel = $ownerUser->email ?? null;
                            }
                        }

                        // Baris kedua di header: kelas (siswa) atau mapel (guru/admin)
                        if ($ownerRole === 'siswa') {
                            $ownerSecondary = $ownerClass;
                        } elseif ($ownerRole === 'admin') {
                            $ownerSecondary = $ownerMapel;
                        } else {
                            $ownerSecondary = $ownerClass;
                        }

                        $ownerName = $ownerUsername;
                        $currentUser = auth()->user();
                        $commentsPayload = $item->comments->map(function ($comment) use ($currentUser, $item) {
                            $canDelete = $currentUser && (
                                $currentUser->id === $comment->user_id ||
                                $currentUser->id === $item->user_id ||
                                $currentUser->username === 'admin'
                            );

                            $likedByMe = $comment->relationLoaded('likes') ? $comment->likes->isNotEmpty() : false;
                            $likeCount = $comment->likes_count ?? 0;

                            return [
                                'id' => $comment->id,
                                'user_name' => $comment->user->username ?? $comment->user->name,
                                'body' => $comment->body,
                                'delete_url' => $canDelete ? route('admin.comments.destroy', $comment) : null,
                                'liked' => $likedByMe,
                                'like_count' => $likeCount,
                            ];
                        })->values();

                        $postData = [
                            'id' => $item->id,
                            'type' => $type,
                            'liked' => $likedByMe,
                            'likeCount' => $likeCount,
                            'comments' => $commentsPayload,
                            'ownerName' => $ownerName,
                            'caption' => $item->caption ?? null,
                        ];
                    @endphp

                    <div
                        class="bg-white border-b border-gray-200"
                        x-data="mobilePostComponent(@js($postData))"
                        x-init="init()"
                        x-ref="mobilePost_{{ $loop->index }}"
                        x-show="filterType === 'all' || categoryFromType('{{ $type }}') === filterType"
                    >
                        {{-- Header user --}}
                        <div class="flex items-center px-4 py-3">
                            <a
                                href="{{ route('profiles.show', $item->user) }}"
                                class="flex items-center flex-1 min-w-0"
                            >
                                <div class="h-8 w-8 rounded-full bg-gray-300 mr-3"></div>
                                <div class="flex flex-col">
                                    {{-- Baris pertama: username --}}
                                    <span class="text-sm font-semibold" x-text="ownerName">{{ $ownerUsername }}</span>
                                    {{-- Baris kedua: kelas (siswa) atau mapel (guru/admin) --}}
                                    <span class="text-xs text-gray-500">{{ $ownerSecondary }}</span>
                                </div>
                            </a>

                            @php $currentUser = $currentUser ?? auth()->user(); @endphp
                            @if ($currentUser && ($currentUser->id === $item->user_id || $currentUser->username === 'admin'))
                                <div class="ml-auto flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Yakin hapus media ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Media --}}
                        <div class="w-full bg-black overflow-hidden flex items-center justify-center" style="aspect-ratio: 4 / 4;">
                            @if (strpos($type, 'image/') === 0)
                                <img
                                    src="{{ asset('storage/'.$item->path) }}"
                                    class="w-full h-full object-cover bg-black"
                                    alt=""
                                >
                            @elseif (strpos($type, 'video/') === 0)
                                <video
                                    x-ref="player"
                                    src="{{ route('media.view', $item) }}"
                                    class="w-full h-full object-cover bg-black"
                                    muted
                                    playsinline
                                    autoplay
                                    controls
                                ></video>
                            @else
                                @php
                                    $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                                    $docLabel = strtoupper($extension ?: 'DOC');
                                    $fileTitle = basename($item->path);
                                @endphp
                                <div
                                    class="w-full h-full flex items-center justify-center bg-indigo-900 px-4 py-4 cursor-pointer"
                                    @click="window.open('{{ route('media.view', $item) }}', '_blank')"
                                >
                                    <div class="w-full h-full rounded-2xl bg-indigo-950 flex flex-col p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-red-500 text-[11px] font-semibold text-white">
                                                {{ $docLabel }}
                                            </span>
                                            <span class="flex-1 text-[11px] font-semibold text-white leading-snug">
                                                {{ $fileTitle }}
                                            </span>
                                        </div>
                                        <div class="flex-1 flex items-center justify-center bg-white rounded-xl overflow-hidden">
                                            <span class="text-[12px] font-medium text-gray-700 text-center px-3">
                                                Preview tugas
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Aksi like & komentar --}}
                        <div class="px-4 py-3 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                @if (!$isGuestViewer)
                                    <button
                                        type="button"
                                        @click="toggleLike()"
                                        class="text-2xl leading-none focus:outline-none h-8 w-8 flex items-center justify-center rounded-full border border-gray-300 bg-white"
                                        :style="liked ? 'background-color: #facc15;' : 'background-color: #ffffff;'"
                                    >
                                        <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-6 w-6 object-contain">
                                    </button>
                                @else
                                    <div class="h-8 w-8 flex items-center justify-center rounded-full border border-gray-200 bg-gray-100 opacity-60">
                                        <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-6 w-6 object-contain">
                                    </div>
                                @endif

                                <span class="font-semibold" x-text="likeCount > 0 ? likeCount + ' like' : '0 like'"></span>

                                <button
                                    type="button"
                                    class="ml-2 text-xl leading-none text-gray-700"
                                    @click="openComments()"
                                >
                                    <img src="{{ asset('images/coment.jpg') }}" alt="Comment" class="h-6 w-6 object-contain">
                                </button>
                                <span class="text-xs text-gray-600" x-text="comments.length"></span>
                            </div>

                            {{-- Caption --}}
                            @if (!empty($item->caption))
                                @php
                                    $fullCaption = $item->caption;
                                    $captionLimit = 90; // kira-kira ~2 baris di layout sekarang
                                    $shortCaption = \Illuminate\Support\Str::limit($fullCaption, $captionLimit, '...');
                                    $captionIsLong = mb_strlen($fullCaption) > $captionLimit;
                                @endphp

                                <div class="text-sm" x-data="{ showFull: false }">
                                    <span class="font-semibold" x-text="ownerName"></span>

                                    {{-- Caption pendek + tombol "lainnya" --}}
                                    <template x-if="!showFull">
                                        <span class="ml-1 text-gray-800">
                                            {{ $captionIsLong ? $shortCaption : $fullCaption }}
                                            @if ($captionIsLong)
                                                <button
                                                    type="button"
                                                    class="ml-1 text-xs text-gray-500 hover:text-gray-700 font-semibold"
                                                    @click="showFull = true"
                                                >
                                                    lainnya
                                                </button>
                                            @endif
                                        </span>
                                    </template>

                                    {{-- Caption penuh setelah klik "lainnya" --}}
                                    @if ($captionIsLong)
                                        <template x-if="showFull">
                                            <span class="ml-1 text-gray-800">{{ $fullCaption }}</span>
                                        </template>
                                    @endif
                                </div>
                            @endif

                            {{-- Link lihat semua komentar (di bawah caption) --}}
                            <button
                                type="button"
                                class="text-xs text-gray-600 hover:underline mt-1"
                                x-show="comments.length > 0"
                                @click="openComments()"
                            >
                                <span x-text="'Lihat semua ' + comments.length + ' komentar'"></span>
                            </button>

                            {{-- Form komentar (non-guest saja) --}}
                            @if (!$isGuestViewer)
                                <form class="flex items-center gap-2 pt-2" @submit.prevent="postComment()">
                                    <input
                                        type="text"
                                        class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                        placeholder="Add a comment..."
                                        x-model="newComment"
                                    >
                                    <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                        Post
                                    </button>
                                </form>
                            @endif

                            {{-- Popup semua komentar (desktop) - gaya mirip Instagram --}}
                            <div
                                x-show="showCommentsModal"
                                x-transition.opacity
                                class="fixed inset-0 z-40 flex items-center justify-center bg-black/70"
                            >
                                {{-- Klik area gelap untuk menutup --}}
                                <div class="absolute inset-0" @click="showCommentsModal = false"></div>

                                <div class="relative w-full h-full md:h-auto md:max-h-[90vh] md:max-w-5xl flex flex-col md:flex-row bg-white md:bg-transparent md:rounded-2xl overflow-hidden">
                                    {{-- Kolom media (hanya desktop) --}}
                                    <div class="hidden md:flex flex-1 bg-black items-center justify-center">
                                        @if (strpos($type, 'image/') === 0)
                                            <img
                                                src="{{ asset('storage/'.$item->path) }}"
                                                alt=""
                                                class="w-full h-full object-cover"
                                            >
                                        @elseif (strpos($type, 'video/') === 0)
                                            @if ($item->thumbnail_path)
                                                <img
                                                    src="{{ asset('storage/'.$item->thumbnail_path) }}"
                                                    alt="Thumbnail video"
                                                    class="w-full h-full object-cover"
                                                >
                                            @else
                                                <video
                                                    class="w-full h-full object-cover"
                                                    controls
                                                    muted
                                                    playsinline
                                                    preload="metadata"
                                                >
                                                    <source src="{{ route('media.view', $item) }}" type="{{ $type }}">
                                                </video>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Kolom komentar --}}
                                    <div class="w-full md:w-[380px] bg-white md:rounded-r-2xl flex flex-col">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                            <h3 class="text-sm font-semibold">Komentar</h3>
                                            <button
                                                type="button"
                                                class="text-lg text-gray-500 hover:text-gray-700"
                                                @click="showCommentsModal = false"
                                            >
                                                &times;
                                            </button>
                                        </div>

                                        <div class="flex-1 px-4 py-3 space-y-2 overflow-y-auto text-sm">
                                            <template x-if="comments.length === 0">
                                                <p class="text-gray-500 text-xs">Belum ada komentar.</p>
                                            </template>

                                            <template x-for="comment in comments" :key="comment.id">
                                                <div class="flex items-center justify-between gap-2">
                                                    <p class="flex items-center gap-1">
                                                        <span class="font-semibold" x-text="comment.user_name"></span>
                                                        <span class="ml-1 text-gray-700" x-text="comment.body"></span>
                                                        <button
                                                            x-show="comment.delete_url"
                                                            type="button"
                                                            class="ml-1 text-[11px] text-red-500 hover:text-red-700"
                                                            @click="deleteComment(comment)"
                                                            title="Hapus komentar"
                                                        >
                                                            🗑️
                                                        </button>
                                                    </p>

                                                    <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                                        @if (!$isGuestViewer)
                                                            <button
                                                                type="button"
                                                                class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-300 bg-white"
                                                                :style="comment.liked ? 'background-color: #facc15;' : 'background-color: #ffffff;'"
                                                                @click="toggleCommentLike(comment)"
                                                            >
                                                                <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                                            </button>
                                                        @else
                                                            <div class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-200 bg-gray-100 opacity-60">
                                                                <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                                            </div>
                                                        @endif
                                                        <span x-text="comment.like_count || 0"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <form class="flex items-center gap-2 px-4 py-3 border-t border-gray-200" @submit.prevent="postComment()">
                                            <input
                                                type="text"
                                                class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                placeholder="Add a comment..."
                                                x-model="newComment"
                                            >
                                            <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                                Post
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Popup semua komentar (MOBILE saja) --}}
                            <div
                                x-show="showCommentsModal"
                                x-transition.opacity
                                class="fixed inset-0 z-40 flex items-end justify-center bg-black/50 md:hidden"
                            >
                                {{-- Klik area gelap untuk menutup --}}
                                <div class="absolute inset-0" @click="showCommentsModal = false"></div>

                                <div class="relative w-full md:max-w-md bg-white rounded-t-2xl md:rounded-2xl h-[80vh] md:h-auto md:max-h-[80vh] flex flex-col overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                        <h3 class="text-sm font-semibold">Komentar</h3>
                                        <button
                                            type="button"
                                            class="text-lg text-gray-500 hover:text-gray-700"
                                            @click="showCommentsModal = false"
                                        >
                                            &times;
                                        </button>
                                    </div>

                                    <div class="flex-1 px-4 py-3 space-y-2 overflow-y-auto text-sm">
                                        <template x-if="comments.length === 0">
                                            <p class="text-gray-500 text-xs">Belum ada komentar.</p>
                                        </template>

                                        <template x-for="comment in comments" :key="comment.id">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="flex items-center gap-1">
                                                    <span class="font-semibold" x-text="comment.user_name"></span>
                                                    <span class="ml-1 text-gray-700" x-text="comment.body"></span>
                                                    <button
                                                        x-show="comment.delete_url"
                                                        type="button"
                                                        class="ml-1 text-[11px] text-red-500 hover:text-red-700"
                                                        @click="deleteComment(comment)"
                                                        title="Hapus komentar"
                                                    >
                                                        🗑️
                                                    </button>
                                                </p>

                                                <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                                    @if (!$isGuestViewer)
                                                        <button
                                                            type="button"
                                                            class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-300 bg-white"
                                                            :style="comment.liked ? 'background-color: #facc15;' : 'background-color: #ffffff;'"
                                                            @click="toggleCommentLike(comment)"
                                                        >
                                                            <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                                        </button>
                                                    @else
                                                        <div class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-200 bg-gray-100 opacity-60">
                                                            <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                                        </div>
                                                    @endif
                                                    <span x-text="comment.like_count || 0"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <form class="flex items-center gap-2 px-4 py-3 border-t border-gray-200" @submit.prevent="postComment()">
                                        <input
                                            type="text"
                                            class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                            placeholder="Add a comment..."
                                            x-model="newComment"
                                        >
                                        <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                            Post
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm py-8">Belum ada postingan.</p>
                @endforelse
            </div>

            {{-- FEED DESKTOP: langsung scroll postingan (tanpa grid) --}}
            <div class="hidden md:flex flex-col space-y-6 px-8 py-6">
                @forelse ($media as $item)
                    @php
                        $type = $item->type ?? '';
                        $likeCount = $item->likes_count ?? 0;
                        $likedByMe = $item->likes->isNotEmpty();
                        $ownerUser = optional($item->user);
                        $ownerName = $ownerUser->username ?? 'username';

                        $ownerRole = $ownerUser->role ?? null;
                        $ownerClass = $ownerUser->kelas ?? null;

                        $ownerMapel = null;
                        if ($ownerRole === 'admin') {
                            if (($ownerUser->username ?? null) === 'admin') {
                                $ownerMapel = 'Admin';
                            } else {
                                $ownerMapel = $ownerUser->email ?? null;
                            }
                        }

                        if ($ownerRole === 'siswa') {
                            $ownerSecondary = $ownerClass;
                        } elseif ($ownerRole === 'admin') {
                            $ownerSecondary = $ownerMapel;
                        } else {
                            $ownerSecondary = $ownerClass;
                        }

                        $currentUser = auth()->user();
                        $commentsPayload = $item->comments->map(function ($comment) use ($currentUser, $item) {
                            $canDelete = $currentUser && (
                                $currentUser->id === $comment->user_id ||
                                $currentUser->id === $item->user_id ||
                                $currentUser->username === 'admin'
                            );

                            $likedByMe = $comment->relationLoaded('likes') ? $comment->likes->isNotEmpty() : false;
                            $likeCount = $comment->likes_count ?? 0;

                            return [
                                'id' => $comment->id,
                                'user_name' => $comment->user->username ?? $comment->user->name,
                                'body' => $comment->body,
                                'delete_url' => $canDelete ? route('admin.comments.destroy', $comment) : null,
                                'liked' => $likedByMe,
                                'like_count' => $likeCount,
                            ];
                        })->values();

                        $postData = [
                            'id' => $item->id,
                            'type' => $type,
                            'liked' => $likedByMe,
                            'likeCount' => $likeCount,
                            'comments' => $commentsPayload,
                            'ownerName' => $ownerName,
                        ];
                    @endphp

                    <div
                        id="media-desktop-{{ $item->id }}"
                        class="bg-white border-b border-gray-200 max-w-xl w-full mx-auto"
                        x-data="mobilePostComponent(@js($postData))"
                        x-init="init()"
                        x-show="filterType === 'all' || categoryFromType('{{ $type }}') === filterType"
                    >
                        <div class="flex items-center px-4 py-3">
                            <a
                                href="{{ route('profiles.show', $item->user) }}"
                                class="flex items-center flex-1 min-w-0"
                            >
                                <div class="h-8 w-8 rounded-full bg-gray-300 mr-3"></div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold" x-text="ownerName">{{ $ownerName }}</span>
                                    <span class="text-xs text-gray-500">{{ $ownerSecondary }}</span>
                                </div>
                            </a>

                            @php $currentUser = $currentUser ?? auth()->user(); @endphp
                            @if ($currentUser && ($currentUser->id === $item->user_id || $currentUser->username === 'admin'))
                                <div class="ml-auto flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Yakin hapus media ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="w-full bg-black overflow-hidden flex items-center justify-center" style="aspect-ratio: 4 / 4;">
                            @if (strpos($type, 'image/') === 0)
                                <img
                                    src="{{ asset('storage/'.$item->path) }}"
                                    class="w-full h-full object-cover bg-black"
                                    alt=""
                                >
                            @elseif (strpos($type, 'video/') === 0)
                                <video
                                    x-ref="player"
                                    src="{{ route('media.view', $item) }}"
                                    class="w-full h-full object-cover bg-black"
                                    muted
                                    playsinline
                                    autoplay
                                    controls
                                ></video>
                            @else
                                @php
                                    $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                                    $docLabel = strtoupper($extension ?: 'DOC');
                                    $fileTitle = basename($item->path);
                                @endphp
                                <div
                                    class="w-full h-full flex items-center justify-center bg-indigo-900 px-4 py-4 cursor-pointer"
                                    @click="window.open('{{ route('media.view', $item) }}', '_blank')"
                                >
                                    <div class="w-full h-full rounded-2xl bg-indigo-950 flex flex-col p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-red-500 text-[11px] font-semibold text-white">
                                                {{ $docLabel }}
                                            </span>
                                            <span class="flex-1 text-[11px] font-semibold text-white leading-snug">
                                                {{ $fileTitle }}
                                            </span>
                                        </div>
                                        <div class="flex-1 flex items-center justify-center bg-white rounded-xl overflow-hidden">
                                            <span class="text-[12px] font-medium text-gray-700 text-center px-3">
                                                Preview dokumen
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="px-4 py-3 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <button
                                type="button"
                                @click="toggleLike()"
                                class="text-2xl leading-none focus:outline-none h-8 w-8 flex items-center justify-center rounded-full"

                                >
                                <img
                                    :src="liked
                                    ? '{{ asset('images/like_active.jpg') }}'
                                    : '{{ asset('images/like.jpg') }}'"
                                    alt="Like"
                                    class="h-6 w-6 object-contain"
                                    >
                                </button>
                                <span class="font-semibold" x-text="likeCount > 0 ? likeCount + ' like' : '0 like'"></span>

                                <button
                                    type="button"
                                    class="ml-2 text-xl leading-none text-gray-700"
                                    @click="openComments()"
                                >
                                    <img src="{{ asset('images/coment.jpg') }}" alt="Comment" class="h-6 w-6 object-contain">
                                </button>
                                <span class="text-xs text-gray-600" x-text="comments.length"></span>
                            </div>

                            @if (!empty($item->caption))
                                @php
                                    $fullCaption = $item->caption;
                                    $captionLimit = 90; // kira-kira ~2 baris di layout sekarang
                                    $shortCaption = \Illuminate\Support\Str::limit($fullCaption, $captionLimit, '...');
                                    $captionIsLong = mb_strlen($fullCaption) > $captionLimit;
                                @endphp

                                <div class="text-sm" x-data="{ showFull: false }">
                                    <span class="font-semibold" x-text="ownerName"></span>

                                    <template x-if="!showFull">
                                        <span class="ml-1 text-gray-800">
                                            {{ $captionIsLong ? $shortCaption : $fullCaption }}
                                            @if ($captionIsLong)
                                                <button
                                                    type="button"
                                                    class="ml-1 text-xs text-gray-500 hover:text-gray-700 font-semibold"
                                                    @click="showFull = true"
                                                >
                                                    lainnya
                                                </button>
                                            @endif
                                        </span>
                                    </template>

                                    @if ($captionIsLong)
                                        <template x-if="showFull">
                                            <span class="ml-1 text-gray-800">{{ $fullCaption }}</span>
                                        </template>
                                    @endif
                                </div>
                            @endif

                            {{-- Link lihat semua komentar (di bawah caption) --}}
                            <button
                                type="button"
                                class="text-xs text-gray-600 hover:underline mt-1"
                                x-show="comments.length > 0"
                                @click="openComments()"
                            >
                                <span x-text="'Lihat semua ' + comments.length + ' komentar'"></span>
                            </button>

                            <form class="flex items-center gap-2 pt-2" @submit.prevent="postComment()">
                                <input
                                    type="text"
                                    class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    placeholder="Add a comment..."
                                    x-model="newComment"
                                >
                                <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                    Post
                                </button>
                            </form>

                            {{-- Popup semua komentar (desktop) - gaya mirip Instagram --}}
                            <div
                                x-show="showCommentsModal"
                                x-transition.opacity
                                class="fixed inset-0 z-40 flex items-center justify-center bg-black/70"
                            >
                                {{-- Klik area gelap untuk menutup --}}
                                <div class="absolute inset-0" @click="showCommentsModal = false"></div>

                                <div class="relative w-full h-full md:h-auto md:max-h-[90vh] md:max-w-5xl flex flex-col md:flex-row bg-white md:bg-transparent md:rounded-2xl overflow-hidden">
                                    {{-- Kolom media (hanya desktop) --}}
                                    <div class="hidden md:flex flex-1 bg-black items-center justify-center">
                                        @if (strpos($type, 'image/') === 0)
                                            <img
                                                src="{{ asset('storage/'.$item->path) }}"
                                                alt=""
                                                class="w-full h-full object-cover"
                                            >
                                        @elseif (strpos($type, 'video/') === 0)
                                            @if ($item->thumbnail_path)
                                                <img
                                                    src="{{ asset('storage/'.$item->thumbnail_path) }}"
                                                    alt="Thumbnail video"
                                                    class="w-full h-full object-cover"
                                                >
                                            @else
                                                <video
                                                    class="w-full h-full object-cover"
                                                    controls
                                                    muted
                                                    playsinline
                                                    preload="metadata"
                                                >
                                                    <source src="{{ asset('storage/'.$item->path) }}" type="{{ $type }}">
                                                </video>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Kolom komentar --}}
                                    <div class="w-full md:w-[380px] bg-white md:rounded-r-2xl flex flex-col">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                            <h3 class="text-sm font-semibold">Komentar</h3>
                                            <button
                                                type="button"
                                                class="text-lg text-gray-500 hover:text-gray-700"
                                                @click="showCommentsModal = false"
                                            >
                                                &times;
                                            </button>
                                        </div>

                                        <div class="flex-1 px-4 py-3 space-y-2 overflow-y-auto text-sm">
                                            <template x-if="comments.length === 0">
                                                <p class="text-gray-500 text-xs">Belum ada komentar.</p>
                                            </template>

                                            <template x-for="comment in comments" :key="comment.id">
                                                <p class="flex items-center gap-1">
                                                    <span class="font-semibold" x-text="comment.user_name"></span>
                                                    <span class="ml-1 text-gray-700" x-text="comment.body"></span>
                                                    <button
                                                        x-show="comment.delete_url"
                                                        type="button"
                                                        class="ml-1 text-[11px] text-red-500 hover:text-red-700"
                                                        @click="deleteComment(comment)"
                                                        title="Hapus komentar"
                                                    >
                                                        🗑️
                                                    </button>
                                                </p>
                                            </template>
                                        </div>

                                        <form class="flex items-center gap-2 px-4 py-3 border-t border-gray-200" @submit.prevent="postComment()">
                                            <input
                                                type="text"
                                                class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                placeholder="Add a comment..."
                                                x-model="newComment"
                                            >
                                            <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                                Post
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm py-8">Belum ada postingan.</p>
                @endforelse
            </div>

            {{-- Grid 4 kolom (DESKTOP) disembunyikan --}}
            <div class="hidden" x-ref="desktopGrid">
                @forelse ($media as $item)
                    @php
                        $type = $item->type ?? '';
                        $likeCount = $item->likes_count ?? 0;
                        $likedByMe = $item->likes->isNotEmpty();
                        $ownerName = optional($item->user)->username ?? 'username';
                        $commentsPayload = $item->comments->map(function ($comment) {
                            $likedByMe = $comment->relationLoaded('likes') ? $comment->likes->isNotEmpty() : false;
                            $likeCount = $comment->likes_count ?? 0;

                            return [
                                'id' => $comment->id,
                                'user_name' => $comment->user->username ?? $comment->user->name,
                                'body' => $comment->body,
                                'liked' => $likedByMe,
                                'like_count' => $likeCount,
                            ];
                        })->values();
                        $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                        $docLabel = strtoupper($extension ?: 'DOC');
                        $fileTitle = \Illuminate\Support\Str::limit(basename($item->path), 18);
                    @endphp
                    <div
                        class="aspect-square bg-gray-200 overflow-hidden border border-white cursor-pointer"
                        x-show="filterType === 'all' || categoryFromType('{{ $type }}') === filterType"
                        x-init='registerMedia({
                            id: {{ $item->id }},
                            src: "{{ asset('storage/'.$item->path) }}",
                            type: "{{ $type }}",
                            liked: {{ $likedByMe ? 'true' : 'false' }},
                            likeCount: {{ $likeCount }},
                            comments: @json($commentsPayload),
                            owner_name: "{{ $ownerName }}",
                        })'
                        @click='
                            if (categoryFromType("{{ $type }}") === "document") {
                                window.open("{{ route('media.view', $item) }}", "_blank");
                            } else {
                                desktopActiveId = {{ $item->id }};
                                $nextTick(() => {
                                    const el = document.getElementById("media-desktop-{{ $item->id }}");
                                    if (el && el.scrollIntoView) {
                                        el.scrollIntoView({ behavior: "smooth", block: "start" });
                                    }
                                });
                            }
                        '
                    >

                        @if (strpos($type, 'image/') === 0)
                            <img
                                src="{{ asset('storage/'.$item->path) }}"
                                class="w-full h-full object-cover"
                                alt=""
                            >
                        @elseif (strpos($type, 'video/') === 0)
                            @if ($item->thumbnail_path)
                                <img
                                    src="{{ asset('storage/'.$item->thumbnail_path) }}"
                                    class="w-full h-full object-cover"
                                    alt="Thumbnail video"
                                >
                            @else
                                <video
                                    class="w-full h-full object-cover"
                                    muted
                                    playsinline
                                    preload="metadata"
                                    @loadeddata="$event.target.currentTime = 0.1; $event.target.pause();"
                                    @mouseenter="if ($event.target.paused) { $event.target.play().catch(() => {}); }"
                                    @mouseleave="$event.target.pause(); $event.target.currentTime = 0;"
                                >
                                    <source src="{{ asset('storage/'.$item->path) }}" type="{{ $type }}">
                                </video>
                            @endif
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-indigo-900 px-3 py-3">
                                <div class="w-full h-full rounded-2xl bg-indigo-950 flex flex-col p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-500 text-[10px] font-semibold text-white">
                                            {{ $docLabel }}
                                        </span>
                                        <span class="flex-1 text-[10px] font-semibold text-white truncate">
                                            {{ $fileTitle }}
                                        </span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center bg-white rounded-xl overflow-hidden">
                                        <span class="text-[11px] font-medium text-gray-700 text-center px-2">
                                            Preview dokumen
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    {{-- Kalau belum ada media sama sekali, tampilkan 3x3 kotak warna contoh --}}
                    {{-- Baris 1 --}}
                    <div class="aspect-square bg-black border border-white"></div>
                    <div class="aspect-square bg-neutral-900 border border-white"></div>
                    <div class="aspect-square bg-neutral-700 border border-white"></div>

                    {{-- Baris 2 --}}
                    <div class="aspect-square bg-neutral-700 border border-white"></div>
                    <div class="aspect-square bg-neutral-500 border border-white"></div>
                    <div class="aspect-square bg-gray-400 border border-white"></div>

                    {{-- Baris 3 --}}
                    <div class="aspect-square bg-gray-400 border border-white"></div>
                    <div class="aspect-square bg-gray-300 border border-white"></div>
                    <div class="aspect-square bg-rose-400 border border-white"></div>
                @endforelse
            </div>

                </div> {{-- end flex-1 (area feed + grid) --}}

                {{-- Sidebar kanan dikosongkan --}}
                <div class="hidden md:flex fixed inset-y-0 right-0 w-48"></div>
            </div> {{-- end flex wrapper utama --}}

            {{-- POPUP MEDIA BESAR --}}
            <div
                x-show="open"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-black/70 flex items-start md:items-center justify-center p-0 md:p-4"
            >
                {{-- Klik area gelap untuk menutup --}}
                <div class="absolute inset-0" @click="closeModal()"></div>

                {{-- Tombol navigasi kiri/kanan di tepi overlay --}}
                <button
                    x-show="items.length > 1"
                    @click.stop="prevMedia()"
                    class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white text-gray-800 shadow-md border border-gray-200 items-center justify-center hover:bg-gray-100"
                >
                    &#10094;
                </button>
                <button
                    x-show="items.length > 1"
                    @click.stop="nextMedia()"
                    class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white text-gray-800 shadow-md border border-gray-200 items-center justify-center hover:bg-gray-100"
                >
                    &#10095;
                </button>

                <div class="relative z-50 max-w-5xl w-full h-full md:h-auto md:max-h-[90vh] bg-white rounded-none md:rounded-lg flex flex-col md:flex-row overflow-y-auto md:overflow-hidden">
                    {{-- Media --}}
                    <div class="relative flex-1 bg-black flex items-center justify-center">
                        <template x-if="activeType.startsWith('image/')">
                            <img :src="activeSrc" class="max-h-[90vh] w-full object-contain" alt="">
                        </template>

                        <template x-if="activeType.startsWith('video/')">
                            <video
                                x-ref="player"
                                :src="activeSrc"
                                controls
                                autoplay
                                muted
                                class="max-h-[90vh] w-full object-contain bg-black"
                            ></video>
                        </template>
                    </div>

                    {{-- Panel bawah MOBILE: user + like + comment --}}
                    <div class="md:hidden w-full bg-white flex flex-col border-t border-gray-200">
                        {{-- Header user --}}
                        <div class="flex items-center px-4 py-3 border-b border-gray-200">
                            <div class="h-8 w-8 rounded-full bg-gray-300 mr-3"></div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold" x-text="ownerName || 'username'"></span>
                                <span class="text-xs text-gray-500" x-text="ownerName || 'username'"></span>
                            </div>
                        </div>

                        {{-- Daftar komentar --}}
                        <div class="px-4 py-3 space-y-2 text-sm">
                            <template x-if="comments.length === 0">
                                <p class="text-gray-500 text-xs">Belum ada komentar.</p>
                            </template>

                            <template x-for="comment in comments" :key="comment.id">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="flex items-center gap-1">
                                        <span class="font-semibold" x-text="comment.user_name"></span>
                                        <span class="ml-1 text-gray-700" x-text="comment.body"></span>
                                        <button
                                            x-show="comment.delete_url"
                                            type="button"
                                            class="ml-1 text-[11px] text-red-500 hover:text-red-700"
                                            @click="deleteComment(comment)"
                                            title="Hapus komentar"
                                        >
                                            🗑️
                                        </button>
                                    </p>

                                    <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                        @if (!$isGuestViewer)
                                            <button
                                                type="button"
                                                class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-300 bg-white"
                                                :style="comment.liked ? 'background-color: #facc15;' : 'background-color: #ffffff;'"
                                                @click="toggleCommentLike(comment)"
                                            >
                                                <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                            </button>
                                        @else
                                            <div class="h-5 w-5 flex items-center justify-center rounded-full border border-gray-200 bg-gray-100 opacity-60">
                                                <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-3 w-3 object-contain">
                                            </div>
                                        @endif
                                        <span x-text="comment.like_count || 0"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Aksi like & form komentar --}}
                        <div class="border-t border-gray-200 px-4 py-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="toggleLike()"
                                        class="text-2xl leading-none focus:outline-none h-8 w-8 flex items-center justify-center rounded-full border border-gray-300 bg-white"
                                        :style="liked ? 'background-color: #facc15;' : 'background-color: #ffffff;'"
                                    >
                                        <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-6 w-6 object-contain">
                                    </button>
                                    <button type="button" class="text-xl leading-none text-gray-700">
                                        <img src="{{ asset('images/coment.jpg') }}" alt="Comment" class="h-6 w-6 object-contain">
                                    </button>
                                </div>
                            </div>

                            <div class="text-sm font-semibold" x-text="likeCount > 0 ? likeCount + ' like' : '0 like'"></div>

                            <form class="flex items-center gap-2 pt-2" @submit.prevent="postComment()">
                                <input
                                    type="text"
                                    class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    placeholder="Add a comment..."
                                    x-model="newComment"
                                >
                                <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                    Post
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Panel kanan: info, like & comment (DESKTOP) --}}
                    <div class="hidden md:flex w-80 bg-white flex-col">
                        {{-- Header user --}}
                        <div class="flex items-center px-4 py-3 border-b border-gray-200">
                            <div class="h-8 w-8 rounded-full bg-gray-300 mr-3"></div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold" x-text="ownerName || 'username'"></span>
                                <span class="text-xs text-gray-500" x-text="ownerName || 'username'"></span>
                            </div>
                        </div>

                        {{-- Daftar komentar --}}
                        <div class="flex-1 px-4 py-3 space-y-2 overflow-y-auto text-sm">
                            <template x-if="comments.length === 0">
                                <p class="text-gray-500 text-xs">Belum ada komentar.</p>
                            </template>

                            <template x-for="comment in comments" :key="comment.id">
                                <p class="flex items-center gap-1">
                                    <span class="font-semibold" x-text="comment.user_name"></span>
                                    <span class="ml-1 text-gray-700" x-text="comment.body"></span>
                                    <button
                                        x-show="isAdmin"
                                        type="button"
                                        class="ml-1 text-[11px] text-red-500 hover:text-red-700"
                                        @click="deleteComment(comment)"
                                    >
                                        x
                                    </button>
                                </p>
                            </template>
                        </div>

                        {{-- Aksi like & form komentar --}}
                        <div class="border-t border-gray-200 px-4 py-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="toggleLike()"
                                        class="text-2xl leading-none focus:outline-none"
                                        :class="liked ? 'text-red-500' : 'text-gray-700'"
                                    >
                                        <img src="{{ asset('images/like.jpg') }}" alt="Like" class="h-6 w-6 object-contain">
                                    </button>
                                    <button type="button" class="text-xl leading-none text-gray-700">
                                        <img src="{{ asset('images/coment.jpg') }}" alt="Comment" class="h-6 w-6 object-contain">
                                    </button>
                                </div>
                            </div>

                            <div class="text-sm font-semibold" x-text="likeCount > 0 ? likeCount + ' like' : '0 like'"></div>

                            <form class="flex items-center gap-2 pt-2" @submit.prevent="postComment()">
                                <input
                                    type="text"
                                    class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    placeholder="Add a comment..."
                                    x-model="newComment"
                                >
                                <button type="submit" class="text-sm font-semibold text-blue-500" :class="{'opacity-50 cursor-not-allowed': !newComment.trim()}">
                                    Post
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Tombol close --}}
                    <button
                        @click="closeModal()"
                        class="absolute top-3 right-3 text-white md:text-gray-700 bg-black/40 md:bg-white/70 rounded-full h-8 w-8 flex items-center justify-center text-lg"
                    >
                        &times;
                    </button>
                </div>
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                function mobilePostComponent(data) {
                    return {
                        id: data.id,
                        type: data.type,
                        liked: !!data.liked,
                        likeCount: data.likeCount || 0,
                        comments: data.comments || [],
                        ownerName: data.ownerName || 'username',
                        newComment: '',
                        observer: null,
                        showCommentsModal: false,
                        init() {
                            this.$nextTick(() => {
                                const video = this.$refs.player;
                                if (!video || !('IntersectionObserver' in window)) {
                                    return;
                                }

                                try {
                                    video.muted = true;
                                } catch (e) {}

                                this.observer = new IntersectionObserver((entries) => {
                                    entries.forEach(entry => {
                                        const visible = entry.isIntersecting;

                                        try {
                                            if (visible) {
                                                // Saat mulai kelihatan: play otomatis (video sudah muted)
                                                video.play().catch(() => {});
                                            } else {
                                                // Saat keluar viewport: pause dan reset ke awal
                                                video.pause();
                                                video.currentTime = 0;
                                            }
                                        } catch (e) {}
                                    });
                                });

                                this.observer.observe(video);

                                // Coba play sekali saat init kalau sudah kelihatan di layar
                                try {
                                    const rect = video.getBoundingClientRect();
                                    const fullyOut = rect.bottom <= 0 || rect.top >= window.innerHeight;
                                    if (!fullyOut) {
                                        video.play().catch(() => {});
                                    }
                                } catch (e) {}
                            });
                        },
                        toggleLike() {
                            axios.post(`/media/${this.id}/like`)
                                .then(response => {
                                    this.liked = response.data.liked;
                                    this.likeCount = response.data.count;
                                });
                        },
                        postComment() {
                            if (!this.newComment.trim()) return;

                            axios.post(`/media/${this.id}/comments`, { body: this.newComment })
                                .then(response => {
                                    this.comments.push({
                                        id: response.data.id,
                                        user_name: response.data.user.username,
                                        body: response.data.body,
                                        delete_url: response.data.delete_url || null,
                                    });
                                    this.newComment = '';
                                });
                        },
                        deleteComment(comment) {
                            if (!comment || !comment.delete_url) return;

                            axios.delete(comment.delete_url)
                                .then(() => {
                                    this.comments = this.comments.filter(c => c.id !== comment.id);
                                });
                        },
                        openComments() {
                            this.showCommentsModal = true;
                        },
                    };
                }
            </script>
        @endpush
    @endonce
</x-app-layout>