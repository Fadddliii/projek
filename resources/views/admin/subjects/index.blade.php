<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">Kelola Mata Pelajaran</h1>

        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama mata pelajaran</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        placeholder="Misal: Matematika"
                        required
                    >
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"
                >
                    Tambah
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Daftar mata pelajaran</h2>
            @if ($subjects->isEmpty())
                <p class="text-sm text-gray-500">Belum ada mata pelajaran.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($subjects as $subject)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-800">{{ $subject->name }}</span>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mata pelajaran ini?');">
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
    </div>
</x-app-layout>
