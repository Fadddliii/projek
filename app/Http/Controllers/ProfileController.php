<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Like;
use App\Models\Media;
use App\Models\User;
use App\Models\Comment;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $userId = $user->id;

        $media = Media::where('user_id', $userId)
            ->withCount('likes')
            ->with([
                'likes' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'comments.user',
                'user',
            ])
            ->latest()
            ->get();

        $postsCount = $media->count();
        $likesCount = $media->sum('likes_count');

        // Anggap media sebagai Tugas hanya jika memiliki subject (folder mapel)
        $allTasks = $media->filter(function ($item) {
            return !empty($item->subject);
        })->values();

        // Media umum (bukan Tugas) adalah semua media yang BUKAN termasuk allTasks.
        $nonTaskMedia = $media->reject(function ($item) use ($allTasks) {
            return $allTasks->contains('id', $item->id ?? null);
        })->values();

        $photos = $nonTaskMedia->filter(function ($item) {
            return str_starts_with((string) $item->type, 'image/');
        })->values();

        $videos = $nonTaskMedia->filter(function ($item) {
            return str_starts_with((string) $item->type, 'video/');
        })->values();

        $documents = $nonTaskMedia->reject(function ($item) {
            return str_starts_with((string) $item->type, 'image/')
                || str_starts_with((string) $item->type, 'video/');
        })->values();

        // Tugas per mata pelajaran: default-nya hanya tugas milik user ini sendiri.
        // Jika folder (subject) dipilih, filter berdasarkan subject; jika belum dipilih,
        // biarkan daftar tugas kosong sampai user memilih folder.
        $activeSubject = $request->query('subject');

        if ($activeSubject) {
            $tasks = $allTasks->filter(function ($item) use ($activeSubject) {
                return $item->subject === $activeSubject;
            })->values();
        } else {
            $tasks = collect();
        }

        // Daftar distinct nama folder tugas yang dimiliki user ini (untuk daftar folder di tab Tugas)
        $taskSubjects = $allTasks->pluck('subject')->filter()->unique()->sort()->values();

        // Hitung user dengan like terbanyak di bulan berjalan
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $topLikeRecord = Like::whereBetween('likes.created_at', [$startOfMonth, $endOfMonth])
            ->join('media', 'likes.media_id', '=', 'media.id')
            ->selectRaw('media.user_id as user_id, COUNT(*) as likes_count')
            ->groupBy('media.user_id')
            ->orderByDesc('likes_count')
            ->first();

        $topLikeUserId = $topLikeRecord->user_id ?? null;

        $isTopLikeThisMonth = $topLikeUserId === $userId;
        $topLikeMonthLabel = $now->translatedFormat('F');

        // Untuk guru/admin: ambil daftar mata pelajaran (folder) yang bisa mereka kelola.
        // - Super admin dengan username 'admin' bisa melihat SEMUA mata pelajaran.
        // - Admin/guru biasa hanya melihat folder milik dirinya sendiri.
        $canManageSubjects = (($user->role ?? null) === 'admin' || $user->username === 'admin');
        if ($canManageSubjects) {
            if ($user->username === 'admin') {
                $subjects = Subject::orderBy('name')->get();
            } else {
                $subjects = Subject::where('user_id', $userId)->orderBy('name')->get();
            }
        } else {
            $subjects = collect();
        }

        // Jika guru/admin memilih sebuah folder mata pelajaran di profilnya sendiri,
        // tampilkan SEMUA tugas dengan subject tersebut (dari semua user), supaya
        // guru bisa melihat upload tugas siswa.
        if ($canManageSubjects && $activeSubject) {
            $tasks = Media::where('subject', $activeSubject)
                ->with('user')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('profile.edit', [
            'user'       => $user,
            'media'      => $media,
            'timelineMedia' => $nonTaskMedia,
            'photos'     => $photos,
            'videos'     => $videos,
            'documents'  => $documents,
            'tasks'      => $tasks,
            'postsCount' => $postsCount,
            'likesCount' => $likesCount,
            'isTopLikeThisMonth' => $isTopLikeThisMonth,
            'topLikeMonthLabel'  => $topLikeMonthLabel,
            'subjects'      => $subjects,
            'taskSubjects'  => $taskSubjects,
            'activeSubject' => $activeSubject,
        ]);
    }

    /**
     * Tampilkan profil publik user lain.
     */
    public function show(User $user): View
    {
        $userId = $user->id;

        $viewerId = auth()->id();

        $mediaQuery = Media::where('user_id', $userId)
            ->withCount('likes')
            ->with([
                'likes' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'comments.user',
                'user',
            ]);

        // Jika yang melihat BUKAN pemilik profil, sembunyikan media tugas:
        // - hanya tampilkan foto & video biasa
        // - eksklusi semua media yang memiliki subject (tugas)
        if (!$viewerId || $viewerId !== $userId) {
            $mediaQuery->where(function ($query) {
                    $query->where('type', 'like', 'image/%')
                          ->orWhere('type', 'like', 'video/%');
                })
                ->where(function ($query) {
                    $query->whereNull('subject')
                          ->orWhere('subject', '');
                });
        }

        $media = $mediaQuery
            ->latest()
            ->get();

        $postsCount = $media->count();
        $likesCount = $media->sum('likes_count');

        $photos = $media->filter(function ($item) {
            return str_starts_with((string) $item->type, 'image/');
        })->values();

        $videos = $media->filter(function ($item) {
            return str_starts_with((string) $item->type, 'video/');
        })->values();

        $documents = $media->reject(function ($item) {
            return str_starts_with((string) $item->type, 'image/')
                || str_starts_with((string) $item->type, 'video/');
        })->values();

        // Hitung user dengan like terbanyak di bulan berjalan (untuk profil publik)
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $topLikeRecord = Like::whereBetween('likes.created_at', [$startOfMonth, $endOfMonth])
            ->join('media', 'likes.media_id', '=', 'media.id')
            ->selectRaw('media.user_id as user_id, COUNT(*) as likes_count')
            ->groupBy('media.user_id')
            ->orderByDesc('likes_count')
            ->first();

        $topLikeUserId = $topLikeRecord->user_id ?? null;

        $isTopLikeThisMonth = $topLikeUserId === $userId;
        $topLikeMonthLabel = $now->translatedFormat('F');

        return view('profile.public', [
            'user'       => $user,
            'media'      => $media,
            'photos'     => $photos,
            'videos'     => $videos,
            'documents'  => $documents,
            'postsCount' => $postsCount,
            'likesCount' => $likesCount,
            'isTopLikeThisMonth' => $isTopLikeThisMonth,
            'topLikeMonthLabel'  => $topLikeMonthLabel,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');

            if (!empty($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $path;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Admin: hapus akun user lain beserta media-nya.
     */
    public function adminDestroy(Request $request, User $user): RedirectResponse
    {
        $current = $request->user();

        if (!$current) {
            abort(403);
        }

        // Fitur hapus akun hanya boleh untuk super admin dengan username 'admin'
        if ($current->username !== 'admin') {
            abort(403);
        }

        // Jangan paksa logout admin sendiri di sini; hanya hapus target user

        // Hapus semua media milik user (termasuk file fisik)
        $medias = Media::where('user_id', $user->id)->get();
        foreach ($medias as $media) {
            if ($media->path) {
                Storage::disk('public')->delete($media->path);
            }

            if (!empty($media->thumbnail_path)) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }

            $media->delete();
        }

        // Hapus semua like & komentar milik user ini
        Like::where('user_id', $user->id)->delete();
        Comment::where('user_id', $user->id)->delete();

        $user->delete();

        return Redirect::route('dashboard');
    }
}
