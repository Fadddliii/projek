<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q'));

        $results = collect();

        if ($query !== '') {
            $results = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('username', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                })
                ->orderBy('username')
                ->limit(20)
                ->get();
        }

        // Ambil beberapa media terbaru untuk ditampilkan di panel kanan (seperti feed di IG)
        $media = Media::latest()->take(12)->get();

        return view('search.index', [
            'query'   => $query,
            'results' => $results,
            'media'   => $media,
        ]);
    }

    /**
     * Endpoint JSON sederhana untuk popup search.
     */
    public function ajax(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q'));

        $results = [];

        if ($query !== '') {
            $results = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('username', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                })
                ->orderBy('username')
                ->limit(20)
                ->get()
                ->map(function (User $user) {
                    return [
                        'id'          => $user->id,
                        'username'    => $user->username,
                        'name'        => $user->name,
                        'kelas'       => $user->kelas ?? null,
                        'profile_url' => route('profiles.show', $user),
                    ];
                })
                ->values()
                ->toArray();
        }

        return response()->json([
            'results' => $results,
        ]);
    }
}
