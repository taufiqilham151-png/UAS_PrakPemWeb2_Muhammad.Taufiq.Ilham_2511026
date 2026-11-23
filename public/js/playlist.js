(() => {
    const appEl = document.getElementById('playlist-app');
    let queue = [];

    if (appEl && appEl.dataset.queue) {
        try {
            queue = JSON.parse(appEl.dataset.queue);
        } catch (err) {
            queue = [];
        }
    }

    let currentIndex = -1;
    let isShuffle = false;
    let isRepeat = false;

    const audio = document.getElementById('audio-player');
    const npTitle = document.getElementById('np-title');
    const npArtist = document.getElementById('np-artist');
    const playerCover = document.getElementById('player-cover');
    const btnToggle = document.getElementById('btn-toggle-play');
    const btnShuffle = document.getElementById('btn-shuffle');
    const btnRepeat = document.getElementById('btn-repeat');
    const btnSpeed = document.getElementById('btn-speed');
    const seekBar = document.getElementById('seek-bar');
    const volumeBar = document.getElementById('volume-bar');
    const timeCurrent = document.getElementById('time-current');
    const timeDuration = document.getElementById('time-duration');

    const speeds = [1, 1.25, 1.5, 2];
    let speedIndex = 0;
    let isSeeking = false;

    if (audio && volumeBar) {
        audio.volume = parseFloat(volumeBar.value);
    }

    function cycleSpeed() {
        if (!audio || !btnSpeed) return;
        speedIndex = (speedIndex + 1) % speeds.length;
        const rate = speeds[speedIndex];
        audio.playbackRate = rate;
        btnSpeed.textContent = rate + 'x';
        btnSpeed.classList.toggle('mode-active', rate !== 1);
    }

    function selectPlaylist(playlistId) {
        document.querySelectorAll('[data-playlist-btn]').forEach((btn) => {
            btn.classList.toggle('active', Number(btn.dataset.playlistBtn) === Number(playlistId));
        });
        document.querySelectorAll('[data-playlist-panel]').forEach((panel) => {
            panel.style.display = Number(panel.dataset.playlistPanel) === Number(playlistId) ? '' : 'none';
        });
    }

    function filterSongs(playlistId, query) {
        const q = query.trim().toLowerCase();
        document.querySelectorAll(`[data-song-row="${playlistId}"]`).forEach((row) => {
            const match = !q || (row.dataset.search || '').includes(q);
            row.style.display = match ? '' : 'none';
        });
    }

    function formatTime(sec) {
        if (!sec || isNaN(sec) || !isFinite(sec)) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function updateActiveSongUI() {
        if (!audio || !btnToggle) return;
        const currentSong = queue[currentIndex];
        document.querySelectorAll('[data-song-id]').forEach((btn) => {
            const isPlaying = currentSong && Number(btn.dataset.songId) === Number(currentSong.id) && !audio.paused;
            btn.classList.toggle('active', Boolean(isPlaying));
            btn.textContent = isPlaying ? '❚❚ Jeda' : '▶ Putar';
        });

        document.querySelectorAll('[data-eq-song]').forEach((slot) => {
            const active = currentSong && Number(slot.dataset.eqSong) === Number(currentSong.id) && !audio.paused;
            slot.innerHTML = active
                ? '<span class="equalizer"><i></i><i></i><i></i></span>'
                : '';
        });

        btnToggle.textContent = audio.paused ? '▶' : '❚❚';
    }

    function playAtIndex(idx) {
        if (!audio || !queue.length || idx < 0 || idx >= queue.length) return;
        currentIndex = idx;
        const song = queue[currentIndex];

        audio.src = song.src;
        audio.playbackRate = speeds[speedIndex];
        if (npTitle) npTitle.textContent = song.title;
        if (npArtist) npArtist.textContent = song.artist;

        if (playerCover) {
            if (song.cover) {
                playerCover.innerHTML = `<img src="${song.cover}" alt="${song.title}" class="cover-thumb">`;
            } else {
                playerCover.innerHTML = '♪';
            }
        }

        audio.play();
        updateActiveSongUI();
    }

    function playSongById(songId) {
        if (!audio) return;
        const idx = queue.findIndex((item) => Number(item.id) === Number(songId));
        if (idx === -1) return;

        if (currentIndex === idx && !audio.paused) {
            audio.pause();
        } else if (currentIndex === idx && audio.paused && audio.src) {
            audio.play();
        } else {
            playAtIndex(idx);
        }
        updateActiveSongUI();
    }

    function togglePlay() {
        if (!audio) return;
        if (!audio.src && queue.length) {
            playAtIndex(0);
            return;
        }
        if (!audio.src) return;
        if (audio.paused) {
            audio.play();
        } else {
            audio.pause();
        }
        updateActiveSongUI();
    }

    function toggleShuffle() {
        isShuffle = !isShuffle;
        if (btnShuffle) {
            btnShuffle.classList.toggle('mode-active', isShuffle);
        }
    }

    function toggleRepeat() {
        isRepeat = !isRepeat;
        if (btnRepeat) {
            btnRepeat.classList.toggle('mode-active', isRepeat);
        }
    }

    function nextSong() {
        if (!queue.length) return;
        if (isShuffle && queue.length > 1) {
            let randIdx = currentIndex;
            while (randIdx === currentIndex) {
                randIdx = Math.floor(Math.random() * queue.length);
            }
            playAtIndex(randIdx);
            return;
        }
        const nextIdx = (currentIndex + 1) % queue.length;
        playAtIndex(nextIdx);
    }

    function prevSong() {
        if (!queue.length) return;
        const prevIdx = (currentIndex - 1 + queue.length) % queue.length;
        playAtIndex(prevIdx);
    }

    // Delegasi klik tombol di halaman
    document.addEventListener('click', (e) => {
        const playlistBtn = e.target.closest('[data-playlist-btn]');
        if (playlistBtn) {
            selectPlaylist(playlistBtn.dataset.playlistBtn);
            return;
        }

        const songBtn = e.target.closest('[data-song-id]');
        if (songBtn) {
            playSongById(songBtn.dataset.songId);
            return;
        }

        const actionBtn = e.target.closest('[data-action]');
        if (!actionBtn) return;

        const action = actionBtn.dataset.action;
        if (action === 'shuffle') toggleShuffle();
        else if (action === 'prev') prevSong();
        else if (action === 'toggle-play') togglePlay();
        else if (action === 'next') nextSong();
        else if (action === 'repeat') toggleRepeat();
        else if (action === 'speed') cycleSpeed();
    });

    // Delegasi pencarian lagu
    document.addEventListener('input', (e) => {
        const searchInput = e.target.closest('[data-search-playlist]');
        if (searchInput) {
            filterSongs(searchInput.dataset.searchPlaylist, searchInput.value);
        }
    });

    // Konfirmasi hapus item
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) {
            e.preventDefault();
        }
    });

    if (audio && seekBar) {
        audio.addEventListener('timeupdate', () => {
            if (isSeeking || !audio.duration || !isFinite(audio.duration)) return;
            seekBar.value = (audio.currentTime / audio.duration) * 100;
            if (timeCurrent) timeCurrent.textContent = formatTime(audio.currentTime);
            if (timeDuration) timeDuration.textContent = formatTime(audio.duration);
        });

        seekBar.addEventListener('pointerdown', () => {
            isSeeking = true;
        });

        seekBar.addEventListener('input', () => {
            if (!audio.duration || !isFinite(audio.duration)) return;
            const targetTime = (parseFloat(seekBar.value) / 100) * audio.duration;
            if (timeCurrent) timeCurrent.textContent = formatTime(targetTime);
        });

        seekBar.addEventListener('change', () => {
            if (audio.duration && isFinite(audio.duration)) {
                const targetTime = (parseFloat(seekBar.value) / 100) * audio.duration;
                audio.currentTime = Math.min(targetTime, Math.max(0, audio.duration - 0.5));
            }
            isSeeking = false;
        });

        window.addEventListener('pointerup', () => {
            isSeeking = false;
        });

        audio.addEventListener('play', updateActiveSongUI);
        audio.addEventListener('pause', updateActiveSongUI);
        audio.addEventListener('ended', () => {
            if (isRepeat && currentIndex !== -1) {
                playAtIndex(currentIndex);
            } else {
                nextSong();
            }
        });
    }

    if (audio && volumeBar) {
        volumeBar.addEventListener('input', () => {
            audio.volume = parseFloat(volumeBar.value);
        });
    }
})();