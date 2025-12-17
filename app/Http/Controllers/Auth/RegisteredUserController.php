<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; 
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pembuatan akun baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'siswa');

        // Aturan dasar untuk kolom "email" (dipakai untuk NISN atau mata pelajaran)
        $emailRules = ['required', 'string', 'max:255', 'unique:users,email'];

        // Jika siswa, wajib hanya angka (NISN)
        if ($role === 'siswa') {
            $emailRules[] = 'regex:/^[0-9]+$/';
        }

        $validated = $request->validate([
            'role' => ['required', 'in:siswa,admin'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'different:name'],
            'email' => $emailRules,
            'kelas' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'kelas' => $validated['kelas'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // Setelah berhasil daftar, arahkan ke halaman login
        return redirect()->route('login')->with('status', 'Akun berhasil dibuat, silakan login.');
    }
}
