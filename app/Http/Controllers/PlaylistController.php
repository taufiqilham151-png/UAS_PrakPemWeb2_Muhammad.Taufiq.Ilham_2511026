<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with('songs')->latest()->get();
        return view('playlist.index', compact('playlists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        Playlist::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_path' => $coverPath,
        ]);

        return back()->with('success', 'Playlist berhasil dibuat.');
    }

    public function destroy(Playlist $playlist)
    {
        if ($playlist->cover_path) {
            Storage::disk('public')->delete($playlist->cover_path);
        }

        foreach ($playlist->songs as $song) {
            if ($song->audio_path) {
                Storage::disk('public')->delete($song->audio_path);
            }
        }

        $playlist->delete();

        return back()->with('success', 'Playlist beserta lagunya berhasil dihapus.');
    }

    public function storeSong(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'artist' => ['required', 'string', 'max:150'],
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
        ]);

        $audioPath = $request->file('audio')->store('songs', 'public');

        $playlist->songs()->create([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'audio_path' => $audioPath,
        ]);

        return back()->with('success', 'Lagu berhasil ditambahkan ke playlist.');
    }

    public function destroySong(Song $song)
    {
        if ($song->audio_path) {
            Storage::disk('public')->delete($song->audio_path);
        }

        $song->delete();

        return back()->with('success', 'Lagu berhasil dihapus.');
    }

    public function updateCover(Request $request, Playlist $playlist)
{
    $request->validate([
        'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    if ($playlist->cover_path && Storage::disk('public')->exists($playlist->cover_path)) {
        Storage::disk('public')->delete($playlist->cover_path);
    }

    $path = $request->file('cover')->store('playlist-covers', 'public');

    $playlist->update([
        'cover_path' => $path,
    ]);

    return back()->with('success', 'Foto playlist berhasil diperbarui.');
    }
    
}