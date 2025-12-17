<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');
        if (!$isAdmin) {
            abort(403);
        }

        // Jika super admin (username 'admin'), tampilkan SEMUA mata pelajaran.
        // Jika admin/guru biasa, hanya tampilkan mata pelajaran yang dibuat oleh user tersebut.
        if ($user->username === 'admin') {
            $subjects = Subject::orderBy('name')->get();
        } else {
            $subjects = Subject::where('user_id', $user->id)
                ->orderBy('name')
                ->get();
        }

        return view('admin.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');
        if (!$isAdmin) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
        ]);

        $data['user_id'] = $user->id;

        Subject::create($data);

        return redirect()->route('admin.subjects.index');
    }

    public function destroy(Request $request, Subject $subject)
    {
        $user = $request->user();
        $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');
        if (!$isAdmin) {
            abort(403);
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index');
    }
}
