document.addEventListener('DOMContentLoaded', function() {

    // --- Логика бургер-меню ---
    const burgerMenu = document.querySelector('.burger-menu');
    const navLinks = document.querySelector('.nav-links');

    if (burgerMenu && navLinks) {
        burgerMenu.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            burgerMenu.classList.toggle('active');
        });
    }

    // --- Логика таймера обратного отсчета ---
    const countdownElement = document.getElementById('countdown-timer');
    if (countdownElement) {
        const targetDate = new Date(countdownElement.dataset.countdownTo).getTime();

        const countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(countdownInterval);
                countdownElement.innerHTML = "Время вышло!";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `
                <div class="time-part"><span>${days}</span>дней</div>
                <div class="time-part"><span>${hours}</span>часов</div>
                <div class="time-part"><span>${minutes}</span>минут</div>
                <div class="time-part"><span>${seconds}</span>секунд</div>
            `;
        }, 1000);
    }

    // --- Логика модального окна ---
    const modal = document.getElementById('receipt-modal');
    const modalImage = document.getElementById('modal-image');
    const closeModalBtn = document.getElementById('close-modal');
    const zoomInBtn = document.getElementById('zoom-in');
    const zoomOutBtn = document.getElementById('zoom-out');
    let currentZoom = 1;

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modalImage.src = '';
        currentZoom = 1;
        modalImage.style.transform = 'scale(1)';
        document.body.style.overflow = 'auto';
    }

    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    if (zoomInBtn) zoomInBtn.addEventListener('click', () => {
        currentZoom += 0.1;
        modalImage.style.transform = `scale(${currentZoom})`;
    });

    if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => {
        if (currentZoom > 0.2) {
            currentZoom -= 0.1;
            modalImage.style.transform = `scale(${currentZoom})`;
        }
    });

    // --- ОБЩИЙ ОБРАБОТЧИК КЛИКОВ НА СТРАНИЦЕ ---
    document.body.addEventListener('click', function(event) {
        // Логика для иконки чека
        if (event.target.classList.contains('receipt-icon')) {
            const imgSrc = event.target.dataset.receiptSrc;
            modalImage.src = imgSrc;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        // Логика для внутренних ссылок
        const link = event.target.closest('a');
        if (link && link.href && new URL(link.href).origin === window.location.origin) {
            if (link.href.includes('/api/logout.php')) {
                event.preventDefault();
                if (window.Player) { window.Player.stopAndReset(); }
                window.location.href = link.href;
                return;
            }
            if (window.Player) {
                const snapshot = window.Player.getSnapshot();
                if (snapshot) {
                    sessionStorage.setItem('playerState', JSON.stringify(snapshot));
                } else {
                    sessionStorage.removeItem('playerState');
                }
            }
        }
    });
    
    // --- Логика для списка треков (Play и Vote) ---
    const tracklist = document.querySelector('.tracklist');
    if (tracklist) { 
        tracklist.addEventListener('click', function(event) {
            // Логика для кнопки Play
            const playButton = event.target.closest('.track-play-button');
            if (playButton) {
                const trackItem = playButton.closest('.track-item');
                const clickedTrackId = trackItem.dataset.id;
                const currentTrackId = window.Player.getCurrentTrackId();

                if (clickedTrackId === currentTrackId) {
                    window.Player.togglePause();
                } else {
                    const trackInfo = { 
                        id: trackItem.dataset.id, 
                        filename: trackItem.dataset.filename, 
                        title: trackItem.querySelector('.track-title').textContent, 
                        author: trackItem.querySelector('.track-author').textContent.replace('Автор: ', '') 
                    };
                    window.Player.playTrack(trackInfo);
                }
            }
            
            // Логика для кнопки Vote
            const voteButton = event.target.closest('.vote-button');
            if (voteButton && !voteButton.disabled) {
                handleTrackVote(voteButton);
            }
        }); 
    }

    // --- Логика для списка текстов (Vote и Copy) ---
    const lyricsContainer = document.querySelector('.lyrics-list');
    if (lyricsContainer) {
        lyricsContainer.addEventListener('click', function(event) {
            // Голосование
            const voteButton = event.target.closest('.lyric-vote-button');
            if (voteButton && !voteButton.disabled) {
                handleLyricVote(voteButton);
            }

            // Копирование названия
            const copyTitleBtn = event.target.closest('.copy-title-btn');
            if (copyTitleBtn) {
                const title = copyTitleBtn.parentElement.querySelector('h3').textContent;
                copyToClipboard(title, copyTitleBtn);
            }

            // Копирование текста
            const copyTextBtn = event.target.closest('.copy-text-btn');
            if (copyTextBtn) {
                const text = copyTextBtn.parentElement.querySelector('.lyric-display-textarea').value;
                copyToClipboard(text, copyTextBtn);
            }
        });
    }

    // --- Слушаем события от плеера и обновляем иконки ---
    document.addEventListener('playerStateChange', function(e) {
        const { isPlaying, trackId } = e.detail;
        const allPlayButtons = document.querySelectorAll('.track-play-button');
        allPlayButtons.forEach(btn => btn.textContent = '▶');

        if (isPlaying) {
            const activeTrackItem = document.querySelector(`.track-item[data-id="${trackId}"]`);
            if (activeTrackItem) {
                const activePlayButton = activeTrackItem.querySelector('.track-play-button');
                activePlayButton.textContent = '❚❚';
            }
        }
    });

    // --- Обработка разблокировки голосования за треки ---
    document.addEventListener('votingUnlocked', function(e) {
        const trackId = e.detail.trackId;
        const trackItem = document.querySelector(`.track-item[data-id="${trackId}"]`);
        if (trackItem) {
            const voteButton = trackItem.querySelector('.vote-button');
            if (voteButton && voteButton.dataset.isLoggedIn === 'true') {
                 voteButton.disabled = false;
                 voteButton.title = 'Теперь вы можете голосовать';
            }
        }
    });

    // --- Функция голосования за ТРЕКИ ---
    function handleTrackVote(button) {
        const trackItem = button.closest('.track-item');
        const trackId = trackItem.dataset.id;
        button.disabled = true;
        const formData = new FormData();
        formData.append('track_id', trackId);
        fetch('/api/vote_track.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const voteCountElement = trackItem.querySelector('.vote-count');
                voteCountElement.textContent = data.newVoteCount;
                button.classList.add('voted');
            } else { 
                alert('Ошибка: ' + data.error);
                button.disabled = false;
            }
        })
        .catch(error => { 
            console.error('Fetch Error:', error);
            alert('Произошла ошибка при отправке запроса.');
            button.disabled = false; 
        });
    }

    // --- Функция голосования за ТЕКСТЫ ---
    function handleLyricVote(button) {
        const lyricItem = button.closest('.lyric-item-form-wrapper');
        const lyricId = lyricItem.dataset.id;
        button.disabled = true;
        const formData = new FormData();
        formData.append('lyric_id', lyricId);

        fetch('/api/vote_lyric.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const voteCountElement = lyricItem.querySelector('.vote-count');
                voteCountElement.textContent = data.newVoteCount;
                button.classList.add('voted');
            } else {
                alert('Ошибка: ' + data.error);
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Произошла ошибка при отправке запроса.');
            button.disabled = false;
        });
    }

    // --- ВСПОМОГАТЕЛЬНАЯ ФУНКЦИЯ: Копирование в буфер обмена ---
    function copyToClipboard(text, buttonElement) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            const originalContent = buttonElement.innerHTML;
            buttonElement.innerHTML = '✓';
            setTimeout(() => {
                buttonElement.innerHTML = originalContent;
            }, 1500);
        } catch (err) {
            alert('Не удалось скопировать текст.');
        }
        document.body.removeChild(textarea);
    }
});

