<x-app-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">
                Upload Foto / Video
            </h1>

            <form method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Pilih file
                    </label>
                    <input
                        type="file"
                        name="file"
                        accept="image/*,video/*"
                        class="block w-full text-sm text-gray-900 dark:text-gray-100
                               file:mr-4 file:py-2 file:px-4
                               file:rounded file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100"
                        required
                    >
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm text-gray-600">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>