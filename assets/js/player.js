// Файл: assets/js/player.js (ПОЛНЫЙ КОД - БЕЗ ИЗМЕНЕНИЙ)

(function() {
    const audio = document.getElementById('audio-player');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const progressBar = document.getElementById('progress-bar');
    const progress = document.getElementById('progress');
    const currentTimeEl = document.getElementById('current-time');
    const durationTimeEl = document.getElementById('duration-time');
    const trackTitleDisplay = document.getElementById('track-title-display');
    const trackAuthorDisplay = document.getElementById('track-author-display');
    const volumeSlider = document.getElementById('volume-slider');
    const gainSlider = document.getElementById('gain-slider');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    let audioContext;
    let trackSource;
    let gainNode;

    function initAudioContext() {
        if (!audioContext) {
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                gainNode = audioContext.createGain();
                trackSource = audioContext.createMediaElementSource(audio);
                trackSource.connect(gainNode).connect(audioContext.destination);
            } catch (e) {
                console.error("Web Audio API is not supported in this browser", e);
            }
        }
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }
    }

    let currentTrackInfo = null;
    let isVoteUnlocked = false;
    let hasCountedPlay = false;

    window.Player = {
        playTrack: function(trackInfo) {
            initAudioContext();
            currentTrackInfo = trackInfo;
            audio.src = '/uploads/tracks/' + trackInfo.filename;
            trackTitleDisplay.textContent = trackInfo.title;
            trackAuthorDisplay.textContent = ' - ' + trackInfo.author;
            isVoteUnlocked = false;
            hasCountedPlay = false;
            audio.play();
        },
        togglePause: function() {
            if (audio.paused) {
                audio.play();
            } else {
                audio.pause();
            }
        },
        getCurrentTrackId: function() {
            return currentTrackInfo ? currentTrackInfo.id : null;
        },
        getSnapshot: function() {
            if (audio.paused && audio.currentTime === 0) { return null; }
            return { trackInfo: currentTrackInfo, currentTime: audio.currentTime, isPlaying: !audio.paused };
        },
        loadFromSnapshot: function(snapshot) {
            initAudioContext();
            currentTrackInfo = snapshot.trackInfo;
            audio.src = '/uploads/tracks/' + currentTrackInfo.filename;
            trackTitleDisplay.textContent = currentTrackInfo.title;
            trackAuthorDisplay.textContent = ' - ' + currentTrackInfo.author;
            audio.currentTime = snapshot.currentTime;
            if (snapshot.isPlaying) {
                audio.play();
            } else {
                playPauseBtn.textContent = '▶';
            }
        },
        stopAndReset: function() {
            audio.pause();
            audio.src = '';
            const oldTrackInfo = currentTrackInfo;
            currentTrackInfo = null;
            sessionStorage.removeItem('playerState');
            trackTitleDisplay.textContent = 'Выберите трек';
            trackAuthorDisplay.textContent = '';
            currentTimeEl.textContent = '0:00';
            durationTimeEl.textContent = '0:00';
            progress.style.width = '0%';
            playPauseBtn.textContent = '▶';
            if (oldTrackInfo) {
                document.dispatchEvent(new CustomEvent('playerStateChange', { detail: { isPlaying: false, trackId: oldTrackInfo.id } }));
            }
        }
    };

    function updateVolume() {
        if (!gainNode) return;
        const masterVolume = parseFloat(volumeSlider.value) * parseFloat(gainSlider.value);
        gainNode.gain.value = masterVolume;
    }
    volumeSlider.addEventListener('input', updateVolume);
    gainSlider.addEventListener('input', updateVolume);
    updateVolume();

    playPauseBtn.addEventListener('click', () => {
        if (!currentTrackInfo) return;
        Player.togglePause();
    });
    
    // Добавляем обработчики для prev/next
    prevBtn.addEventListener('click', () => {
        // Логика для проигрывания предыдущего трека (нужно реализовать в main.js)
        document.dispatchEvent(new CustomEvent('playPrevTrack'));
    });
    nextBtn.addEventListener('click', () => {
        // Логика для проигрывания следующего трека (нужно реализовать в main.js)
        document.dispatchEvent(new CustomEvent('playNextTrack'));
    });

    audio.addEventListener('play', () => {
        initAudioContext();
        playPauseBtn.textContent = '❚❚';
        document.dispatchEvent(new CustomEvent('playerStateChange', { detail: { isPlaying: true, trackId: currentTrackInfo.id } }));
        if (!hasCountedPlay) {
            hasCountedPlay = true;
            const formData = new FormData();
            formData.append('track_id', currentTrackInfo.id);
            fetch('/api/increment_play_count.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const trackItem = document.querySelector(`.track-item[data-id="${currentTrackInfo.id}"]`);
                    if(trackItem) {
                        const playCountEl = trackItem.querySelector('.play-count-display');
                        let currentCount = parseInt(playCountEl.textContent, 10);
                        playCountEl.textContent = currentCount + 1;
                    }
                }
            })
            .catch(error => console.error('Could not increment play count:', error));
        }
    });

    audio.addEventListener('pause', () => {
        playPauseBtn.textContent = '▶';
        document.dispatchEvent(new CustomEvent('playerStateChange', { detail: { isPlaying: false, trackId: currentTrackInfo.id } }));
    });
    
    audio.addEventListener('timeupdate', () => {
        const { currentTime, duration } = audio;
        if (isNaN(duration)) return;
        const progressPercent = (currentTime / duration) * 100;
        progress.style.width = `${progressPercent}%`;
        currentTimeEl.textContent = formatTime(currentTime);
        if (currentTime >= 30 && !isVoteUnlocked) {
            isVoteUnlocked = true;
            const event = new CustomEvent('votingUnlocked', { detail: { trackId: currentTrackInfo.id } });
            document.dispatchEvent(event);
        }
    });
    
    audio.addEventListener('ended', () => {
        // Сообщаем main.js, что нужно включить следующий трек
        document.dispatchEvent(new CustomEvent('playNextTrack'));
    });

    audio.addEventListener('loadedmetadata', () => {
        durationTimeEl.textContent = formatTime(audio.duration);
    });
    
    progressBar.addEventListener('click', (e) => {
        if (isNaN(audio.duration)) return;
        const width = progressBar.clientWidth;
        const clickX = e.offsetX;
        const duration = audio.duration;
        audio.currentTime = (clickX / width) * duration;
    });
    
    function formatTime(seconds) {
        if (isNaN(seconds)) return "0:00";
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }
    
    const savedState = sessionStorage.getItem('playerState');
    if (savedState) {
        try {
            const snapshot = JSON.parse(savedState);
            if (snapshot && snapshot.trackInfo) {
                window.Player.loadFromSnapshot(snapshot);
            }
        } catch (e) {
            console.error("Failed to parse player state from sessionStorage", e);
            sessionStorage.removeItem('playerState');
        }
    }
})();