<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist Music</title>
    <link rel="stylesheet" href="{{ asset('css/playlist.css') }}">
</head>
<body>
    @php
        $totalSongs = $playlists->sum(fn($p) => $p->songs->count());
        $queueData = $playlists->flatMap(function ($pl) {
            return $pl->songs->map(function ($song) use ($pl) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'src' => asset('storage/' . $song->audio_path),
                    'cover' => $pl->cover_path ? asset('storage/' . $pl->cover_path) : null,
                ];
            });
        })->values();
    @endphp

    <div id="playlist-app" class="container" data-queue='@json($queueData)'>
        <div class="header">
            <div class="brand">
                <div class="brand-logo">♪</div>
                <div>
                    <h1>Playlist Music</h1>
                    <p>Kelola playlist, cari lagu, dan putar koleksi musikmu</p>
                </div>
            </div>

            <div class="stats-pill">
                <strong>{{ $playlists->count() }}</strong> Playlist · <strong>{{ $totalSongs }}</strong> Lagu
            </div>
        </div>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid-main">
            {{-- Kolom Kiri --}}
            <div>
                <div class="card">
                    <h2>Buat Playlist Baru</h2>
                    <form action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Nama playlist (mis. Morning Acoustic)" required>
                        </div>
                        <div class="form-group">
                            <textarea name="description" placeholder="Deskripsi singkat (opsional)"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="file" name="cover" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">+ Simpan Playlist</button>
                    </form>
                </div>

                @if($playlists->isNotEmpty())
                    <div class="card card-mt">
                        <h3>Koleksi Playlist</h3>
                        <div class="playlist-list">
                            @foreach($playlists as $index => $playlist)
                                <button
                                    type="button"
                                    class="playlist-item {{ $index === 0 ? 'active' : '' }}"
                                    data-playlist-btn="{{ $playlist->id }}"
                                >
                                    @if($playlist->cover_path)
                                        <img src="{{ asset('storage/' . $playlist->cover_path) }}" alt="{{ $playlist->name }}" class="cover-thumb">
                                    @else
                                        <div class="cover-thumb">♪</div>
                                    @endif
                                    <div class="playlist-item-info">
                                        <div class="playlist-item-name">{{ $playlist->name }}</div>
                                        <div class="playlist-item-count">{{ $playlist->songs->count() }} lagu</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan --}}
            <div>
                @forelse($playlists as $index => $playlist)
                    <div
                        class="card playlist-panel"
                        data-playlist-panel="{{ $playlist->id }}"
                        style="{{ $index === 0 ? '' : 'display: none;' }}"
                    >
                        <div class="playlist-hero-actions">
    <form action="{{ route('playlists.updateCover', $playlist) }}" method="POST" enctype="multipart/form-data" class="cover-upload-form">
        @csrf
        @method('PATCH')
        <input type="file" name="cover" accept="image/*" required>
        <button type="submit" class="btn btn-play cover-submit-btn">Ganti Foto</button>
    </form>

    <form action="{{ route('playlists.destroy', $playlist) }}" method="POST" data-confirm="Hapus playlist ini beserta semua lagunya?" class="delete-playlist-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger-soft">Hapus Playlist</button>
    </form>
</div>

                        <div class="toolbar-row">
                            <h3>Daftar Lagu</h3>
                            <div class="search-box">
                                <input
                                    type="search"
                                    placeholder="Cari judul atau artis..."
                                    data-search-playlist="{{ $playlist->id }}"
                                >
                            </div>
                        </div>

                        <div class="songs-container" data-songs-list="{{ $playlist->id }}">
                            @forelse($playlist->songs as $song)
                                <div
                                    class="song-row"
                                    data-song-row="{{ $playlist->id }}"
                                    data-search="{{ strtolower($song->title . ' ' . $song->artist) }}"
                                >
                                    <div class="song-meta">
                                        <strong>
                                            <span class="eq-slot" data-eq-song="{{ $song->id }}"></span>
                                            {{ $song->title }}
                                        </strong>
                                        <span>{{ $song->artist }}</span>
                                    </div>
                                    <div class="song-actions">
                                        <button
                                            type="button"
                                            class="btn btn-play"
                                            data-song-id="{{ $song->id }}"
                                        >
                                            ▶ Putar
                                        </button>
                                        <form action="{{ route('songs.destroy', $song) }}" method="POST" data-confirm="Hapus lagu ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-soft" title="Hapus lagu">✕</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="empty-songs-text">Belum ada lagu di playlist ini. Tambahkan lagu di bawah.</p>
                            @endforelse
                        </div>

                        <div class="add-song-box">
                            <h3>Tambah Lagu ke Playlist</h3>
                            <form action="{{ route('playlists.songs.store', $playlist) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="song-form-grid">
                                    <input type="text" name="title" placeholder="Judul lagu" required>
                                    <input type="text" name="artist" placeholder="Nama artis" required>
                                    <input type="file" name="audio" accept=".mp3,.wav,.ogg,.m4a,audio/*" required>
                                    <button type="submit" class="btn btn-primary btn-upload">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="card card-empty">
                        <h2>Belum ada playlist</h2>
                        <p>Buat playlist pertamamu di panel sebelah kiri untuk mulai mengunggah musik.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pemutar Musik Bawah --}}
    <div class="player-bar">
        <div class="player-inner">
            <div class="now-playing-box">
                <div id="player-cover" class="cover-thumb">♪</div>
                <div class="now-playing-text">
                    <strong id="np-title">Belum ada lagu diputar</strong>
                    <span id="np-artist">Pilih lagu untuk mulai memutar</span>
                </div>
            </div>

            <div class="controls-center">
                <div class="control-btns">
                    <button type="button" class="ctrl-btn" id="btn-shuffle" title="Acak (Shuffle)" data-action="shuffle">🔀</button>
                    <button type="button" class="ctrl-btn" title="Sebelumnya" data-action="prev">⏮</button>
                    <button type="button" class="ctrl-btn main" id="btn-toggle-play" title="Play / Pause" data-action="toggle-play">▶</button>
                    <button type="button" class="ctrl-btn" title="Berikutnya" data-action="next">⏭</button>
                    <button type="button" class="ctrl-btn" id="btn-repeat" title="Ulangi (Repeat)" data-action="repeat">🔁</button>
                </div>
                <div class="progress-wrap">
                    <span id="time-current">0:00</span>
                    <input type="range" id="seek-bar" min="0" max="100" value="0" step="0.1">
                    <span id="time-duration">0:00</span>
                </div>
            </div>

            <div class="volume-wrap">
                <button type="button" class="ctrl-btn speed-btn" id="btn-speed" title="Kecepatan Putar" data-action="speed">1x</button>
                <span>🔊</span>
                <input type="range" id="volume-bar" class="volume-slider" min="0" max="1" step="0.02" value="0.8">
            </div>
        </div>
        <audio id="audio-player"></audio>
    </div>

    <script src="{{ asset('js/playlist.js') }}"></script>
</body>
</html>