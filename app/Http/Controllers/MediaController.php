<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        if ($user && (($user->role ?? null) === 'guest' || $user->username === 'guest')) {
            abort(403);
        }

        $subjects = Subject::query()
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        return view('profile.media.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user && (($user->role ?? null) === 'guest' || $user->username === 'guest')) {
            abort(403);
        }

        $data = $request->validate([
            'file' => [
                'required',
                'file',
                // Foto + video + dokumen umum
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf,doc,docx,ppt,pptx,xls,xlsx,txt',
                'max:102400', // ~100MB
            ],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            'caption'   => ['nullable', 'string', 'max:1000'],
            'subject'   => ['nullable', 'string', 'max:100'],
        ]);

        $file = $data['file'];
        $path = $file->store('media', 'public'); // storage/app/public/media
        $mime = $file->getMimeType();
        $originalName = $file->getClientOriginalName();

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('media_thumbnails', 'public');
        }

        $media = Media::create([
            'user_id'        => $request->user()->id,
            'path'           => $path,
            'original_name'  => $originalName,
            'thumbnail_path' => $thumbnailPath,
            'type'           => $mime,
            'caption'        => $data['caption'] ?? null,
            'subject'        => $data['subject'] ?? null,
        ]);

        // Jika ada subject (folder mata pelajaran), anggap ini upload Tugas
        // Arahkan ke halaman profil dengan tab Tugas dan folder tersebut aktif.
        if (!empty($media->subject)) {
            return redirect()->route('profile.edit', ['subject' => $media->subject]);
        }

        // Kalau tidak ada subject, perlakukan sebagai upload biasa (foto/video) dan kembali ke dashboard
        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, Media $media)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $isAdmin = (($user->role ?? null) === 'admin' || $user->username === 'admin');
        // Hanya pemilik media atau admin yang boleh menghapus media
        if ($user->id !== $media->user_id && !$isAdmin) {
            abort(403);
        }

        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        if (!empty($media->thumbnail_path)) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }

        $media->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Tampilkan file media secara inline di browser.
     */
    public function view(Media $media)
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($media->path)) {
            abort(404);
        }

        $fullPath = $disk->path($media->path);
        $mime = $media->type ?: $disk->mimeType($media->path);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
        ]);
    }
}