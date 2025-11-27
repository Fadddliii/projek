<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use App\Models\Media;

Route::get('/', function () {
    // Kalau sudah login, masuk ke dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Kalau belum login, ke halaman login
    return redirect()->route('login');
});

// Halaman dashboard untuk user yang SUDAH login
Route::get('/dashboard', function () {
    $media = Media::where('user_id', auth()->id())
        ->latest()
        ->take(9) // misal 9 item terbaru
        ->get();

    return view('dashboard', compact('media'));
})->middleware(['auth'])->name('dashboard');

// Route untuk halaman profil (dipakai oleh navigation.blade.php)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/media/create', [MediaController::class, 'create'])->name('media.create');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
});

// Muat route login & logout dari routes/auth.php
require __DIR__.'/auth.php';
