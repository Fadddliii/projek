<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Media;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Media $media)
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) === 'guest' || $user->username === 'guest') {
            return response()->json([
                'liked' => false,
                'count' => Like::where('media_id', $media->id)->count(),
                'error' => 'Guest account cannot like posts.',
            ], 403);
        }

        $existing = Like::where('user_id', $user->id)
            ->where('media_id', $media->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id'  => $user->id,
                'media_id' => $media->id,
            ]);
            $liked = true;
        }

        $count = Like::where('media_id', $media->id)->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }
}
