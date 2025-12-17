<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentLikeController;
use App\Models\Media;

Route::get('/', function (Request $request) {
    // Kalau sudah login (termasuk guest), langsung ke dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Kalau belum login sama sekali, otomatis login sebagai guest,
    // lalu arahkan ke dashboard.
    $controller = app(AuthenticatedSessionController::class);
    return $controller->guestLogin($request);
});

// Halaman dashboard untuk user yang SUDAH login
Route::get('/dashboard', function () {
    $userId = auth()->id();

    // Query dasar untuk media + relasi yang dibutuhkan (hanya foto & video di dashboard, BUKAN tugas)
    $baseQuery = Media::where(function ($query) {
            // Hanya foto & video
            $query->where('type', 'like', 'image/%')
                  ->orWhere('type', 'like', 'video/%');
        })
        // Kecualikan media yang punya subject (tugas)
        ->where(function ($query) {
            $query->whereNull('subject')
                  ->orWhere('subject', '');
        })
        ->withCount('likes')
        ->with([
            'likes' => function ($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                }
            },
            'comments' => function ($query) use ($userId) {
                // Setiap komentar membawa data user + jumlah like + apakah sudah di-like oleh viewer
                $query->with('user')
                      ->withCount('likes');

                if ($userId) {
                    $query->with(['likes' => function ($likeQuery) use ($userId) {
                        $likeQuery->where('user_id', $userId);
                    }]);
                }
            },
            'user',
        ]);

    // 3 postingan dengan like terbanyak (global, tanpa melihat waktu)
    $topLiked = (clone $baseQuery)
        ->orderByDesc('likes_count')
        ->take(3)
        ->get();

    // ID yang sudah diambil di topLiked supaya tidak dobel
    $excludeIds = $topLiked->pluck('id');

    // Postingan lain diurutkan berdasarkan yang terbaru
    $recent = (clone $baseQuery)
        ->when($excludeIds->isNotEmpty(), function ($query) use ($excludeIds) {
            $query->whereNotIn('id', $excludeIds);
        })
        ->latest()
        ->get();

    // Gabungkan: 3 teratas by like, lalu sisanya by terbaru
    $media = $topLiked->concat($recent);

    return view('dashboard', compact('media'));
})->middleware(['auth'])->name('dashboard');

// Route untuk halaman profil (dipakai oleh navigation.blade.php)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/media/create', [MediaController::class, 'create'])->name('media.create');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::get('/media/{media}/view', [MediaController::class, 'view'])->name('media.view');
    Route::post('/media/{media}/like', [LikeController::class, 'toggle'])->name('media.like.toggle');
    Route::post('/media/{media}/comments', [CommentController::class, 'store'])->name('media.comments.store');
    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'toggle'])->name('comments.like.toggle');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/users', [SearchController::class, 'ajax'])->name('search.ajax');

    // Halaman profil publik user lain (gunakan layout profil baru)
    Route::get('/profiles/{user}', [ProfileController::class, 'show'])->name('profiles.show');

    // Route lama (jika masih dipakai di tempat lain)
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');

    // Admin only: hapus media & semua komentarnya
    Route::delete('/admin/media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
    Route::delete('/admin/media/{media}/comments', [CommentController::class, 'destroyByMedia'])->name('admin.media.comments.destroyAll');
    Route::delete('/admin/comments/{comment}', [CommentController::class, 'destroy'])->name('admin.comments.destroy');

    // Admin: kelola mata pelajaran untuk tugas
    Route::get('/admin/subjects', [SubjectController::class, 'index'])->name('admin.subjects.index');
    Route::post('/admin/subjects', [SubjectController::class, 'store'])->name('admin.subjects.store');
    Route::delete('/admin/subjects/{subject}', [SubjectController::class, 'destroy'])->name('admin.subjects.destroy');

    // Admin: hapus akun user lain
    Route::delete('/admin/users/{user}', [ProfileController::class, 'adminDestroy'])->name('admin.users.destroy');
});

// Muat route login & logout dari routes/auth.php
require __DIR__.'/auth.php';
