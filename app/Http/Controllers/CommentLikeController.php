<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function toggle(Request $request, Comment $comment)
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) === 'guest' || $user->username === 'guest') {
            return response()->json([
                'liked' => false,
                'count' => CommentLike::where('comment_id', $comment->id)->count(),
                'error' => 'Guest account cannot like comments.',
            ], 403);
        }

        $existing = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'user_id' => $user->id,
                'comment_id' => $comment->id,
            ]);
            $liked = true;
        }

        $count = CommentLike::where('comment_id', $comment->id)->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }
}
