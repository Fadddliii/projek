<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div
            class="max-w-4xl mx-auto px-4 py-8"
            x-data="profilePageState('{{ request()->query('tab') === 'dokumen' || !empty($activeSubject) ? 'dokumen' : 'foto' }}')"
        >
            @php
                $viewer = auth()->user();
                $isOwnProfileView = $viewer && $viewer->id === $user->id;
                // Fitur Hapus Akun hanya boleh untuk super admin dengan username 'admin'
                $isAdminViewer = $viewer && $viewer->username === 'admin';
                // Guru/admin (role admin atau username admin) boleh kelola mata pelajaran
                $canManageSubjects = $viewer && (($viewer->role ?? null) === 'admin' || $viewer->username === 'admin');
            @endphp
            <div class="flex flex-col items-center" x-show="!activePostId">
                <div
                    class="w-24 h-24 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center @if($isOwnProfileView) cursor-pointer @endif"
                    @if ($isOwnProfileView)
                        @click="
                            showEditProfileModal = true;
                            $nextTick(() => {
                                const input = document.getElementById('avatar-input-modal');
                                if (input) { input.click(); }
                            });
                        "
                    @endif
                >
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

                @if ($isOwnProfileView)
                    <button
                        type="button"
                        class="mt-2 inline-flex items-center px-4 py-1.5 border border-gray-300 rounded-full text-xs font-semibold text-gray-700 hover:bg-gray-100"
                        @click="showEditProfileModal = true"
                    >
                        Edit Profil
                    </button>
                @elseif ($isAdminViewer)
                    <form
                        method="POST"
                        action="{{ route('admin.users.destroy', $user) }}"
                        class="mt-2"
                        onsubmit="return confirm('Yakin ingin menghapus akun ini? Semua media milik akun ini juga akan dihapus.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-1.5 border border-red-400 rounded-full text-xs font-semibold text-red-600 hover:bg-red-50"
                        >
                            Hapus Akun
                        </button>
                    </form>
                @endif
            </div>

            @if ($isOwnProfileView)
                <div
                    x-show="showEditProfileModal"
                    x-transition.opacity
                    class="fixed inset-0 z-40 flex items-center justify-center bg-black/60"
                >
                    <div class="absolute inset-0" @click="showEditProfileModal = false"></div>

                    <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-lg p-6">
                        <button
                            type="button"
                            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-lg"
                            @click="showEditProfileModal = false"
                        >
                            &times;
                        </button>

                        <div class="flex flex-col items-center gap-4" x-data="{ showAvatarInput: false }">
                            <div
                                class="w-20 h-20 rounded-full overflow-hidden bg-gray-300 flex items-center justify-center cursor-pointer"
                                @click.prevent="$refs.avatarInput && $refs.avatarInput.click(); showAvatarInput = true"
                            >
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

                            <button
                                type="button"
                                class="px-5 py-1.5 rounded-full bg-black text-white text-xs font-semibold shadow-sm"
                                @click.prevent="showAvatarInput = !showAvatarInput"
                            >
                                ubah foto profil
                            </button>

                            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="w-full mt-4 space-y-4">
                                @csrf
                                @method('patch')

                                <input type="hidden" name="name" value="{{ $user->name }}">
                                <input type="hidden" name="email" value="{{ $user->email }}">

                                <div x-show="showAvatarInput" class="space-y-1">
                                    <label class="block text-xs text-gray-600">Foto profil</label>
                                    <input
                                        type="file"
                                        name="avatar"
                                        accept="image/*"
                                        x-ref="avatarInput"
                                        id="avatar-input-modal"
                                        class="block w-full text-xs text-gray-700 border border-gray-300 rounded-xl px-3 py-2 cursor-pointer focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tambahkan bio</label>
                                    <textarea
                                        name="bio"
                                        rows="3"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                        placeholder="Tambahkan bio"
                                    ></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        class="px-5 py-1.5 rounded-full bg-[#2F3F57] text-white text-xs font-semibold shadow-sm"
                                    >
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8 flex justify-center text-sm text-gray-600 border-b border-gray-300" x-show="!activePostId">
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
                @if ($isOwnProfileView)
                    <button
                        type="button"
                        class="px-6 py-2 -mb-px border-b-2"
                        :class="activeTab === 'dokumen' ? 'border-gray-900 text-gray-900' : 'border-transparent'"
                        @click="activeTab = 'dokumen'"
                    >
                        Tugas
                    </button>
                @endif
            </div>

            <div class="mt-4" x-show="activeTab === 'foto' && !activePostId" x-ref="grid">
                <div class="grid grid-cols-3 gap-[1px]">
                    @forelse ($photos as $item)
                        <button
                            type="button"
                            class="bg-black aspect-square overflow-hidden block"
                            @click="
                                activePostId = {{ $item->id }};
                                $nextTick(() => {
                                    const el = document.getElementById('profile-post-{{ $item->id }}');
                                    if (el) {
                                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    }
                                });
                            "
                        >
                            <img
                                src="{{ asset('storage/'.$item->path) }}"
                                alt=""
                                class="w-full h-full object-cover"
                            >
                        </button>
                    @empty
                        <p class="col-span-3 text-center text-xs text-gray-500 py-8 bg-white">Belum ada foto.</p>
                    @endforelse
                </div>
            </div>
            @endif

            <div class="mt-4" x-show="activeTab === 'video' && !activePostId">
                <div class="grid grid-cols-3 gap-[1px]">
                    @forelse ($videos as $item)
                        <button
                            type="button"
                            class="bg-black aspect-square overflow-hidden block"
                            @click="
                                activePostId = {{ $item->id }};
                                $nextTick(() => {
                                    const el = document.getElementById('profile-post-{{ $item->id }}');
                                    if (el) {
                                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    }
                                });
                            "
                        >
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
                        </button>
                    @empty
                        <p class="col-span-3 text-center text-xs text-gray-500 py-8 bg-white">Belum ada video.</p>
                    @endforelse
                </div>
            </div>

            @if ($isOwnProfileView)
            <div class="mt-4 space-y-6" x-show="activeTab === 'dokumen' && !activePostId">

                {{-- Untuk guru/admin: kelola daftar folder mata pelajaran milik mereka sendiri --}}
                @if ($canManageSubjects && isset($subjects) && empty($activeSubject))
                    <div class="max-w-xl mx-auto w-full bg-white rounded-xl shadow p-4">
                        <h2 class="text-base font-semibold text-gray-800 mb-4">Kelola Mata Pelajaran</h2>

                        <div>
                            <h3 class="text-xs font-semibold text-gray-700 mb-2">Daftar folder mata pelajaran</h3>
                            @if ($subjects->isEmpty())
                                <p class="text-xs text-gray-500">Belum ada mata pelajaran.</p>
                            @else
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($subjects as $subject)
                                        <li class="flex items-center justify-between py-2">
                                            <a
                                                href="{{ route('profile.edit', ['tab' => 'dokumen', 'subject' => $subject->name]) }}"
                                                class="flex items-center gap-2 text-sm text-gray-800 hover:text-indigo-600"
                                            >
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-indigo-50 text-[11px] font-semibold text-indigo-700">
                                                    📁
                                                </span>
                                                <span>{{ $subject->name }}</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mata pelajaran ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[11px] text-red-600 hover:text-red-700 font-semibold">
                                                    Hapus
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Daftar folder tugas yang dimiliki user ini (untuk semua role) --}}
                @if (isset($taskSubjects) && $taskSubjects->isNotEmpty() && empty($activeSubject))
                    <div class="max-w-xl mx-auto w-full bg-white rounded-xl shadow p-4">
                        <h2 class="text-base font-semibold text-gray-800 mb-4">Folder Tugas</h2>

                        <div>
                            <h3 class="text-xs font-semibold text-gray-700 mb-2">Pilih folder untuk melihat tugas</h3>
                            <ul class="divide-y divide-gray-200">
                                @foreach ($taskSubjects as $subjectName)
                                    <li class="flex items-center justify-between py-2">
                                        <a
                                            href="{{ route('profile.edit', ['tab' => 'dokumen', 'subject' => $subjectName]) }}"
                                            class="flex items-center gap-2 text-sm text-gray-800 hover:text-indigo-600"
                                        >
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-indigo-50 text-[11px] font-semibold text-indigo-700">
                                                📁
                                            </span>
                                            <span>{{ $subjectName }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="mt-3 text-[11px] text-gray-500">Pilih salah satu folder di atas untuk melihat tugas yang sudah diupload.</p>
                        </div>
                    </div>
                @endif

                {{-- Jika sudah memilih folder, tampilkan header kecil dengan nama folder + tombol kembali --}}
                @if (!empty($activeSubject))
                    <div class="max-w-xl mx-auto w-full flex items-center justify-between mb-3">
                        <div class="text-xs sm:text-sm text-gray-700">
                            Folder: <span class="font-semibold text-gray-900">{{ $activeSubject }}</span>
                        </div>
                        <a
                            href="{{ route('profile.edit', ['tab' => 'dokumen']) }}"
                            class="text-[11px] sm:text-xs font-semibold text-indigo-600 hover:text-indigo-700"
                        >
                            &larr; Kembali ke daftar folder
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-3 gap-[1px]">
                    @forelse ($tasks as $item)
                        @php
                            $type = $item->type ?? '';
                            $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                            $docLabel = strtoupper($extension ?: 'DOC');
                            // Tampilkan nama asli file jika ada, tanpa menyamarkan
                            $fileTitle = $item->original_name ?? basename($item->path);
                        @endphp
                        <button
                            type="button"
                            class="bg-indigo-900 aspect-square flex items-center justify-center block px-2 py-2"
                            @click="
                                activeTab = 'dokumen';
                                activePostId = {{ $item->id }};
                                $nextTick(() => {
                                    const el = document.getElementById('profile-post-{{ $item->id }}');
                                    if (el) {
                                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    }
                                });
                            "
                        >
                            <div class="w-full h-full rounded-2xl bg-indigo-950 flex flex-col p-2">
                                <div class="flex items-center gap-1 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-500 text-[9px] font-semibold text-white">
                                        {{ $docLabel }}
                                    </span>
                                    <span class="flex-1 text-[9px] font-semibold text-white truncate" title="{{ $fileTitle }}">
                                        {{ $fileTitle }}
                                    </span>
                                </div>
                                <div class="flex-1 flex items-center justify-center bg-white rounded-xl overflow-hidden">
                                    @if (strpos($type, 'image/') === 0)
                                        <img
                                            src="{{ asset('storage/'.$item->path) }}"
                                            alt="Tugas foto"
                                            class="w-full h-full object-cover"
                                        >
                                    @elseif (strpos($type, 'video/') === 0)
                                        <video
                                            class="w-full h-full object-cover"
                                            muted
                                            playsinline
                                            preload="metadata"
                                            @mouseenter="if ($event.target.paused) { $event.target.play().catch(() => {}); }"
                                            @mouseleave="$event.target.pause(); $event.target.currentTime = 0;"
                                        >
                                            <source src="{{ route('media.view', $item) }}" type="{{ $type }}">
                                        </video>
                                    @else
                                        <span class="text-[10px] font-medium text-gray-700 text-center px-2">
                                            Preview tugas
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                    @endforelse
                </div>

                @if ($canManageSubjects && !empty($activeSubject) && isset($tasks) && $tasks instanceof \Illuminate\Support\Collection)
                    @php
                        $taskStudentCount = $tasks->pluck('user_id')->filter()->unique()->count();
                    @endphp

                    <div
                        class="fixed right-3 bottom-3 md:right-8 md:bottom-6 z-30"
                        x-data="{ openTaskList: false }"
                    >
                        <button
                            type="button"
                            class="flex items-center gap-2 bg-white text-gray-800 text-[11px] sm:text-xs md:text-sm rounded-full shadow-lg border border-gray-200 px-3 sm:px-4 py-2 hover:bg-gray-50"
                            @click="openTaskList = true"
                        >
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-semibold text-white">
                                {{ strtoupper(mb_substr($activeSubject, 0, 1)) }}
                            </span>
                            <span class="whitespace-nowrap">
                                {{ $activeSubject }} <span class="font-semibold">{{ $taskStudentCount }} siswa</span>
                            </span>
                        </button>

                        <div
                            x-show="openTaskList"
                            x-transition.opacity
                            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
                        >
                            <div class="absolute inset-0" @click="openTaskList = false"></div>

                            <div class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-xl p-4 max-h-[80vh] flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-900">Daftar pengumpul tugas</span>
                                        <span class="text-[11px] text-gray-500">
                                            Folder {{ $activeSubject }} &middot; {{ $tasks->count() }} upload &middot; {{ $taskStudentCount }} siswa
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-lg text-gray-400 hover:text-gray-600 px-2"
                                        @click="openTaskList = false"
                                    >
                                        &times;
                                    </button>
                                </div>

                                <div class="mt-2 border-t border-gray-200 pt-2 overflow-y-auto text-xs">
                                    @if ($tasks->isEmpty())
                                        <p class="text-[11px] text-gray-500 text-center py-4">Belum ada siswa yang mengumpulkan tugas di folder ini.</p>
                                    @else
                                        @foreach ($tasks as $index => $task)
                                            @php
                                                $studentUser = optional($task->user);
                                                $studentName = $studentUser->name ?? $studentUser->username ?? 'Tanpa nama';
                                                $studentClass = $studentUser->kelas ?? null;

                                                // Tampilkan waktu pengumpulan dalam zona waktu lokal (Asia/Jakarta)
                                                $uploadedAt = optional($task->created_at)
                                                    ? $task->created_at->timezone('Asia/Jakarta')->format('d M Y H:i')
                                                    : null;
                                            @endphp
                                            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-b-0">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[11px] text-gray-500">{{ $index + 1 }}.</span>
                                                    <div class="flex flex-col">
                                                        {{-- Baris 1: nama lengkap siswa --}}
                                                        <span class="font-semibold text-gray-900">{{ $studentName }}</span>
                                                        {{-- Baris 2: kelas siswa (jika ada) --}}
                                                        @if (!empty($studentClass))
                                                            <span class="text-[11px] text-gray-500">{{ $studentClass }}</span>
                                                        @endif
                                                        {{-- Baris 3: waktu upload --}}
                                                        @if (!empty($uploadedAt))
                                                            <span class="text-[11px] text-gray-400">{{ $uploadedAt }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Daftar post detail (layout mirip dashboard) --}}
            <div class="mt-10 space-y-6" x-show="activePostId" x-ref="detail">
                {{-- Header sticky dengan tombol back, selalu terlihat saat scroll --}}
                <div class="max-w-xl w-full mx-auto sticky top-0 z-20">
                    <div class="flex items-center justify-between bg-white/95 backdrop-blur border-b px-4 py-2">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full text-white text-xl focus:outline-none"
                            @click="
                                activePostId = null;
                                $nextTick(() => {
                                    $refs.grid && $refs.grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                });
                            "
                        >
                            <img src="{{ asset('images/back.png') }}" alt="Back" class="h-8 w-8 object-contain">
                        </button>
                    </div>
                </div>

                @php
                    $detailMedia = ($timelineMedia ?? $media);
                    if (isset($tasks) && $tasks instanceof \Illuminate\Support\Collection && $tasks->isNotEmpty()) {
                        $detailMedia = $detailMedia->concat($tasks);
                    }
                @endphp

                @forelse ($detailMedia as $item)
                    @php
                        $type = $item->type ?? '';
                        $likeCount = $item->likes_count ?? 0;
                        $likedByMe = $item->likes->isNotEmpty();

                        $ownerUser = optional($item->user);

                        // Tentukan tab mana yang relevan untuk post ini.
                        // Jika media punya subject (Tugas), SELALU dianggap sebagai 'dokumen',
                        // meskipun type-nya image/video, supaya muncul di tab Tugas.
                        $hasSubject = !empty($item->subject ?? null);
                        if ($hasSubject) {
                            $itemTab = 'dokumen';
                        } else {
                            $isImagePost = strpos($type, 'image/') === 0;
                            $isVideoPost = strpos($type, 'video/') === 0;
                            $itemTab = $isImagePost ? 'foto' : ($isVideoPost ? 'video' : 'dokumen');
                        }

                        // Header identitas pengunggah:
                        // - Untuk tugas (subject terisi): nama lengkap + kelas
                        // - Untuk postingan biasa: username seperti sebelumnya
                        if ($hasSubject) {
                            $ownerName = $ownerUser->name ?? $ownerUser->username ?? 'Tanpa nama';
                            $ownerSecondary = $ownerUser->kelas ?? '';
                        } else {
                            $ownerName = $ownerUser->username ?? 'username';
                            $ownerSecondary = '';
                        }

                        $currentUser = auth()->user();

                        $commentsPayload = $item->comments->map(function ($comment) use ($currentUser, $item) {
                            $canDelete = $currentUser && (
                                $currentUser->id === $comment->user_id ||
                                $currentUser->id === $item->user_id ||
                                $currentUser->username === 'admin'
                            );

                            return [
                                'id' => $comment->id,
                                'user_name' => $comment->user->username ?? $comment->user->name,
                                'body' => $comment->body,
                                'delete_url' => $canDelete ? route('admin.comments.destroy', $comment) : null,
                            ];
                        })->values();

                        $postData = [
                            'id' => $item->id,
                            'type' => $type,
                            'liked' => $likedByMe,
                            'likeCount' => $likeCount,
                            'comments' => $commentsPayload,
                            'ownerName' => $ownerName,
                            'ownerSecondary' => $ownerSecondary,
                        ];
                    @endphp

                    <div
                        id="profile-post-{{ $item->id }}"
                        class="bg-white border-b border-gray-200 max-w-xl w-full mx-auto"
                        x-data="mobilePostComponent(@js($postData))"
                        x-show="activePostId && activeTab === '{{ $itemTab }}'"
                    >
                        <div class="flex items-center px-4 py-3">
                            <div class="h-8 w-8 rounded-full bg-gray-300 mr-3"></div>
                            <div class="flex flex-col">
                                {{-- Baris pertama: nama lengkap (tugas) atau username (post biasa) --}}
                                <span class="text-sm font-semibold" x-text="ownerName">{{ $ownerName }}</span>
                                {{-- Baris kedua: kelas untuk tugas (jika ada), kosong untuk post biasa --}}
                                <span class="text-xs text-gray-500" x-text="ownerSecondary">{{ $ownerSecondary ?? '' }}</span>
                            </div>

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
                                    controls
                                ></video>
                            @else
                                @php
                                    $extension = strtolower(pathinfo($item->path, PATHINFO_EXTENSION));
                                    $docLabel = strtoupper($extension ?: 'DOC');
                                    $fileTitle = $item->original_name ?? basename($item->path);
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
                                    $captionLimit = 90;
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

                                    <template x-if="showFull">
                                        <span class="ml-1 text-gray-800">{{ $fullCaption }}</span>
                                    </template>
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

                            {{-- Form komentar singkat di profil --}}
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

                            {{-- Popup semua komentar (desktop & mobile) --}}
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
                                                    @mouseenter="if ($event.target.paused) { $event.target.play().catch(() => {}); }"
                                                    @mouseleave="$event.target.pause(); $event.target.currentTime = 0;"
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
                @endforelse
            </div>
        </div>
    </div>

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
                    ownerSecondary: data.ownerSecondary || '',
                    newComment: '',
                    showCommentsModal: false,
                    _videoObserver: null,
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
                    init() {
                        // Auto play/pause video berdasarkan visibility di viewport
                        if (!this.type || !this.type.startsWith('video/')) {
                            return;
                        }

                        const videoEl = this.$refs.player;
                        if (!videoEl || typeof IntersectionObserver === 'undefined') {
                            return;
                        }

                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (!entry.isIntersecting || entry.intersectionRatio < 0.6) {
                                    // Tidak cukup terlihat: pause dan reset ke awal
                                    try {
                                        videoEl.pause();
                                        videoEl.currentTime = 0;
                                    } catch (e) {}
                                    return;
                                }

                                // Terlihat dengan cukup besar di viewport: mulai dari awal dan play
                                try {
                                    videoEl.currentTime = 0;
                                    const playPromise = videoEl.play();
                                    if (playPromise && typeof playPromise.then === 'function') {
                                        playPromise.catch(() => {});
                                    }
                                } catch (e) {}
                            });
                        }, {
                            threshold: [0, 0.6],
                        });

                        observer.observe(videoEl);
                        this._videoObserver = observer;
                    },
                };
            }

            function profilePageState(initialTab = 'foto') {
                return {
                    activeTab: initialTab,
                    activePostId: null,
                    showEditProfileModal: false,
                    init() {
                        this.$watch('activePostId', (value) => {
                            const topbar = document.getElementById('app-topbar-mobile');
                            const bottomnav = document.getElementById('app-bottomnav-mobile');

                            const hide = !!value;

                            [topbar, bottomnav].forEach((el) => {
                                if (!el) return;
                                if (hide) {
                                    el.style.display = 'none';
                                } else {
                                    el.style.display = '';
                                }
                            });
                        });
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
