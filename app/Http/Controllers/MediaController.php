<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function create()
{
    return view('profile.media.create');
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi', 'max:102400'], // max ~100MB
        ]);

        $path = $data['file']->store('media', 'public'); // storage/app/public/media

        Media::create([
            'user_id' => $request->user()->id,
            'path'    => $path,
            'type'    => $data['file']->getMimeType(),
        ]);

        return redirect()->route('dashboard');
    }
}