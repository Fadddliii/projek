<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// Route untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    // Form login
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Proses login
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Login sebagai guest (akun baca-saja)
    Route::post('/guest-login', [AuthenticatedSessionController::class, 'guestLogin'])
        ->name('login.guest');

    // Form register
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Proses register
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Route untuk user yang sudah login
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});