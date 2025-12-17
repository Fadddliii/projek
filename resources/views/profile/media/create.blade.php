@php
    $type = request('type'); // 'photo', 'video', 'document', atau null
    $user = auth()->user();
    // Admin di sini mencakup guru (role 'admin') dan super admin dengan username 'admin'
    $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');

    switch ($type) {
        case 'photo':
            $title = 'Upload Foto';
            $dragLine = 'Drag photos here';
            $accept = 'image/*';
            $showThumbnail = false;
            break;
        case 'video':
            $title = 'Upload Video';
            $dragLine = 'Drag videos here';
            $accept = 'video/*';
            $showThumbnail = true;
            break;
        case 'document':
            if ($isAdmin) {
                $title = 'Kelola Folder Mata Pelajaran';
                $dragLine = null;
                $accept = null;
                $showThumbnail = false;
            } else {
                $title = 'Upload Tugas';
                $dragLine = 'Tarik foto, video, atau file tugas ke sini';
                $accept = 'image/*,video/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt';
                $showThumbnail = true;
            }
            break;

        default:
            $title = 'Upload Foto / Video / Tugas';
            $dragLine = 'Drag photos, videos, or task files here';
            $accept = 'image/*,video/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt';
            $showThumbnail = true; // rekomendasi untuk video
            break;
    }
@endphp

<x-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-black/40 md:bg-black/60 px-4">
        <div class="w-full max-w-xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden">

            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-3 text-center">
                <h1 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ $title }}
                </h1>
            </div>

            @if ($type === 'document' && $isAdmin)
                {{-- Admin: popup khusus untuk pembuatan / pengelolaan folder mata pelajaran --}}
                <div class="px-6 py-8">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <form method="POST" action="{{ route('admin.subjects.store') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-end">
                            @csrf
                            <div class="flex-1">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama folder / mata pelajaran</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    placeholder="Misal: Matematika, Bahasa Indonesia"
                                    required
                                >
                            </div>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"
                            >
                                Tambah Folder
                            </button>
                        </form>

                        <div class="mt-6">
                            <h2 class="text-sm font-semibold text-gray-700 mb-3">Daftar folder mata pelajaran</h2>
                            @php
                                $subjectList = $subjects ?? collect();
                            @endphp
                            @if ($subjectList->isEmpty())
                                <p class="text-sm text-gray-500">Belum ada folder mata pelajaran.</p>
                            @else
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($subjectList as $subject)
                                        <li class="flex items-center justify-between py-2">
                                            <span class="text-sm text-gray-800">{{ $subject->name }}</span>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus folder mata pelajaran ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                                                    Hapus
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
                                Tutup
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div
                    x-data="{
                        dragging: false,
                        step: 1,
                        previewUrl: null,
                        fileName: '',
                        hasFile: false,
                        isVideoPreview: false,
                        caption: '',
                        handleDrop(event) {
                            this.dragging = false;
                            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
                                this.handleFiles(event.dataTransfer.files, true);
                            }
                        },
                        handleFiles(files, fromDrop = false) {
                            if (!files || !files.length) return;
                            const file = files[0];

                            // Saat drag & drop, paksa masukkan file ke input menggunakan DataTransfer
                            if (fromDrop && this.$refs.fileInput) {
                                try {
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(file);
                                    this.$refs.fileInput.files = dataTransfer.files;
                                } catch (e) {
                                    // Abaikan jika browser tidak mendukung, user masih bisa klik Select
                                }
                            }

                            this.fileName = file.name;
                            this.hasFile = true;

                            if (file.type && file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    this.previewUrl = e.target.result;
                                    this.isVideoPreview = false;
                                };
                                reader.readAsDataURL(file);
                            } else if (file.type && file.type.startsWith('video/')) {
                                this.previewUrl = URL.createObjectURL(file);
                                this.isVideoPreview = true;
                            } else {
                                this.previewUrl = null;
                                this.isVideoPreview = false;
                            }
                        },
                        fillCaptionWithNow() {
                            // Fungsi ini tidak lagi mengisi otomatis; dibiarkan kosong
                            return;
                        },
                    }"
                    class="px-6 py-10 flex flex-col items-center justify-center text-center"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="handleDrop($event)"
                >
                    {{-- Icon + teks area drop --}}
                    <div
                        class="w-full max-w-sm rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center px-6 py-8 mb-4 transition-colors duration-150 overflow-hidden"
                        :class="dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-gray-800/70' : 'bg-transparent'"
                    >
                        {{-- Jika ada preview (foto / video) tampilkan di sini --}}
                        <template x-if="previewUrl">
                            <div class="w-full flex items-center justify-center">
                                <template x-if="isVideoPreview">
                                    <video
                                        :src="previewUrl"
                                        class="max-h-64 w-full object-contain rounded-md"
                                        controls
                                        muted
                                        playsinline
                                    ></video>
                                </template>
                                <template x-if="!isVideoPreview">
                                    <img :src="previewUrl" alt="Preview" class="max-h-64 w-full object-contain rounded-md">
                                </template>
                            </div>
                        </template>

                        {{-- Jika belum ada previewUrl tapi sudah ada file (kemungkinan dokumen), tampilkan kartu dokumen sederhana --}}
                        <template x-if="!previewUrl && hasFile">
                            <div class="w-full flex items-center justify-center">
                                <div class="w-full max-w-xs bg-white rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="h-10 w-10 rounded-md bg-indigo-50 flex items-center justify-center">
                                        <span class="text-lg">📄</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate" x-text="fileName"></p>
                                        <p class="text-[11px] text-gray-500">Preview tugas (dokumen)</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Jika belum ada file sama sekali, tampilkan placeholder default --}}
                        <template x-if="!previewUrl && !hasFile">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                    <span class="text-4xl text-gray-500">🖼️</span>
                                </div>

                                <p class="text-base text-gray-900 dark:text-gray-100 font-medium mb-1">
                                    {{ $dragLine }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    or use the button below
                                </p>
                            </div>
                        </template>
                    </div>

                    <form method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data" class="mt-2 flex flex-col items-center gap-3">

                        @csrf

                        <input
                            x-ref="fileInput"
                            id="file-input"
                            type="file"
                            name="file"
                            accept="{{ $accept }}"
                            class="hidden"
                            required
                            @change="handleFiles($event.target.files, false)"
                        >

                        @error('file')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <button
                            type="button"
                            onclick="document.getElementById('file-input').click()"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Select from computer
                        </button>

                        <div x-show="step === 2" class="w-full max-w-sm mt-4 text-left space-y-4 text-sm text-gray-600">
                            <div>
                                <label for="caption" class="font-semibold text-gray-800">Keterangan</label>
                                <textarea
                                    id="caption"
                                    name="caption"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-gray-50"
                                    placeholder="Tambahkan keterangan (opsional)"
                                    x-model="caption"
                                ></textarea>
                            </div>

                            @if ($type === 'document')
                                @php
                                    // Ambil daftar mata pelajaran langsung dari database
                                    $subjectOptions = \App\Models\Subject::orderBy('name')->get();
                                @endphp

                                <div>
                                    <label for="subject" class="font-semibold text-gray-800">Folder / Mata pelajaran</label>
                                    <select
                                        id="subject"
                                        name="subject"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                                    >
                                        <option value="">Pilih mata pelajaran</option>
                                        @foreach ($subjectOptions as $subject)
                                            <option value="{{ $subject->name }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 mt-6">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
                                Batal
                            </a>

                            <button
                                x-show="step === 1"
                                type="button"
                                class="text-sm text-indigo-600 font-semibold hover:text-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed"
                                :disabled="!hasFile"
                                @click="step = 2"
                            >
                                Selanjutnya
                            </button>

                            <button
                                x-show="step === 2"
                                type="submit"
                                class="text-sm text-indigo-600 font-semibold hover:text-indigo-700"
                            >
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>