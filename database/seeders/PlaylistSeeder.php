<?php

namespace Database\Seeders;

use App\Models\Playlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $playlist = Playlist::firstOrCreate(
            ['name' => 'Starter Mix'],
            [
                'description' => 'Koleksi lagu bawaan yang langsung siap diputar.',
                'cover_path' => null,
            ]
        );

        $songsDir = storage_path('app/public/songs');
        if (! File::exists($songsDir)) {
            return;
        }

        $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
        $files = collect(File::files($songsDir))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $allowedExtensions))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        foreach ($files as $index => $file) {
            $filename = $file->getFilename();
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            // Jika nama file formatnya "Artis - Judul Lagu", pisahkan otomatis
            if (str_contains($baseName, ' - ')) {
                [$artistPart, $titlePart] = explode(' - ', $baseName, 2);
                $artist = trim(str_replace('_', ' ', $artistPart));
                $title = trim(str_replace('_', ' ', $titlePart));
            } else {
                $artist = 'Unknown Artist';
                $title = trim(str_replace('_', ' ', $baseName));
            }

            $audioPath = 'songs/' . $filename;

            $playlist->songs()->updateOrCreate(
                ['audio_path' => $audioPath],
                [
                    'title' => Str::limit($title, 150, ''),
                    'artist' => Str::limit($artist, 150, ''),
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}