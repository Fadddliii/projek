<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Media;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Media $media)
    {
        $user = $request->user();
        if (!$user || ($user->role ?? null) === 'guest' || $user->username === 'guest') {
            return response()->json([
                'error' => 'Guest account cannot comment.',
            ], 403);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $comment = Comment::create([
            'user_id'  => $user->id,
            'media_id' => $media->id,
            'body'     => $data['body'],
        ]);

        $comment->load('user', 'media');

        $user = $request->user();
        $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');
        $canDelete = $user && (
            $user->id === $comment->user_id ||
            $user->id === optional($comment->media)->user_id ||
            $isAdmin
        );

        return response()->json([
            'id'      => $comment->id,
            'body'    => $comment->body,
            'user'    => [
                'id'       => $comment->user->id,
                'username' => $comment->user->username ?? $comment->user->name,
            ],
            'created_at' => $comment->created_at->toDateTimeString(),
            'delete_url' => $canDelete ? route('admin.comments.destroy', $comment) : null,
        ]);
    }

    public function destroyByMedia(Request $request, Media $media)
    {
        $user = $request->user();
        $isAdmin = $user && (($user->role ?? null) === 'admin' || $user->username === 'admin');
        // Hanya pemilik media atau admin yang boleh menghapus semua komentar pada media ini
        if (!$user || ($user->id !== $media->user_id && !$isAdmin)) {
            abort(403);
        }

        $media->comments()->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $comment->loadMissing('media');

        // Komentar boleh dihapus oleh: pemilik komentar, pemilik media, atau admin
        $isAdmin = ($user->role ?? null) === 'admin' || $user->username === 'admin';
        if (
            $user->id !== $comment->user_id &&
            $user->id !== optional($comment->media)->user_id &&
            !$isAdmin
        ) {
            abort(403);
        }

        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }
}
