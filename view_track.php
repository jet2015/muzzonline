<?php
// Файл: /view_track.php (Baseline v4.11 - Фикс краша колонки)
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'core/settings.php';
require_once 'templates/genres_list.php';

if (!function_exists('parseVideoUrl')) {
    function parseVideoUrl($url) {
        if (empty($url)) return null;
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
            return['type' => 'youtube', 'embed' => "https://www.youtube.com/embed/{$match[1]}", 'thumb' => "https://img.youtube.com/vi/{$match[1]}/mqdefault.jpg"];
        }
        if (preg_match('/rutube\.ru\/video\/([a-zA-Z0-9]+)/i', $url, $match)) {
            return['type' => 'rutube', 'embed' => "https://rutube.ru/play/embed/{$match[1]}"];
        }
        return null;
    }
}

$trackId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$trackId) { die("Неверный ID трека."); }

$currentUser = getCurrentUser();

try {
    $sql = "SELECT t.*, u.id as author_id, u.login as author_login 
            FROM tracks t JOIN users u ON t.user_id = u.id 
            WHERE t.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trackId]);
    $track = $stmt->fetch();
    if (!$track) { die("Трек не найден."); }

    // Голоса
    $voteCountStmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE track_id = ?");
    $voteCountStmt->execute([$trackId]);
    $track['vote_count'] = $voteCountStmt->fetchColumn() + $track['purchased_votes_count'];

    // Комментарии
    $sql_comments = "SELECT c.*, u.login as author, u.avatar_filename, u.is_pro 
                     FROM track_comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.track_id = ? ORDER BY c.created_at DESC";
    $stmt_comments = $pdo->prepare($sql_comments);
    $stmt_comments->execute([$trackId]);
    $comments = $stmt_comments->fetchAll();
} catch (\PDOException $e) { die("Ошибка базы данных"); }

$isAuthor = ($currentUser && $currentUser['id'] === $track['user_id']);

// === ЗАЩИТА ОТ FATAL ERROR ===
// Гарантируем, что $selectedGenres ВСЕГДА будет массивом, даже если в БД мусор
$rawGenres = $track['genres'] ?? '';
$selectedGenres =[];
if (!empty($rawGenres)) {
    $decoded = json_decode($rawGenres, true);
    if (is_array($decoded)) {
        $selectedGenres = $decoded;
    }
}

// --- ПОДГОТОВКА ДАННЫХ ДЛЯ СОЦСЕТЕЙ (Open Graph) ---
$pageTitle = htmlspecialchars($track['title']) . " - " . htmlspecialchars($track['author_login']);
$pageDescription = "Послушайте трек '" . htmlspecialchars($track['title']) . "' от автора " . htmlspecialchars($track['author_login']) . " на MuzzOnline. Оценивайте и скачивайте!";

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
if (!empty($track['cover_art'])) {
    $ogImage = $protocol . '://' . $host . '/uploads/covers/' . htmlspecialchars($track['cover_art']);
} else {
    $ogImage = $protocol . '://' . $host . '/assets/images/og-default.jpg';
}

$watermarkText = function_exists('get_setting') ? get_setting('video_watermark_text', 'REALIST-MUSIC') : 'REALIST-MUSIC';

require_once 'templates/header.php';
?>

<link rel="stylesheet" href="/assets/css/track-view.css">

<div class="track-card-page">
    
    <!-- ПЛЕЕР КАРТОЧКИ ТРЕКА -->
    <div id="local-player-controls" class="media-controls" 
         data-id="<?php echo $track['id']; ?>" 
         data-filename="<?php echo htmlspecialchars($track['filename']); ?>"
         data-title="<?php echo htmlspecialchars($track['title']); ?>"
         data-author="<?php echo htmlspecialchars($track['author_login']); ?>">
        
        <div class="player-track-info">
            <div class="player-track-title"><?php echo htmlspecialchars($track['title']); ?></div>
            <div class="player-track-author">
                Автор: <a href="/profile.php?id=<?php echo $track['author_id']; ?>"><?php echo htmlspecialchars($track['author_login']); ?></a>
                (<a href="/user_tracks.php?id=<?php echo $track['author_id']; ?>">(все треки)</a>)
            </div>
        </div>
        
        <div class="media-buttons">
            <button class="media-button" id="card-rewind"><i class="fas fa-backward button-icons"></i></button>
            <button class="media-button play-button track-play-button"><i class="fas fa-play button-icons"></i></button>
            <button class="media-button" id="card-forward"><i class="fas fa-forward button-icons"></i></button>
        </div>
        
        <div class="media-progress">
            <div class="progress-bar-wrapper" id="card-progress-wrapper">
                <div class="local-progress-bar" id="local-progress"></div>
            </div>
            <div class="time-display">
                <span id="local-current-time">0:00</span>
                <span id="local-duration-time">0:00</span>
            </div>
        </div>

        <div class="media-extra-controls">
            <div class="volume-group">
                <i class="fas fa-volume-down"></i>
                <input type="range" id="card-volume" class="local-volume-slider" min="0" max="1" step="0.01" value="1">
                <i class="fas fa-volume-up"></i>
            </div>
            <div class="player-actions-group">
                <div class="vote-control-local">
                    <span class="vote-count-local" id="sync-vote-card"><?php echo $track['vote_count']; ?></span>
                    <button class="vote-button-local vote-button" data-is-logged-in="<?php echo $currentUser ? 'true' : 'false'; ?>" data-locked="true">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                <a href="/api/download_track.php?id=<?php echo $track['id']; ?>" class="download-button-local card-download-trigger">
                    <i class="fas fa-download"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="track-card-grid">
        <!-- ЛЕВАЯ КОЛОНКА -->
        <div class="track-card-left">
            <div class="track-cover-art">
                <?php if (!empty($track['cover_art'])): ?>
                    <img src="/uploads/covers/<?php echo htmlspecialchars($track['cover_art']); ?>" alt="Cover">
                <?php else: ?>
                    <div style="background:#222;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;font-size:4rem;border-radius:8px;">🎵</div>
                <?php endif; ?>
            </div>

            <?php if ($isAuthor): ?>
                <div class="track-card-box">
                    <h3>Сменить обложку</h3>
                    <form action="/api/update_track_details.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                        <input type="file" name="cover_art_file" class="track-card-input" required>
                        <button type="submit" class="button-primary" style="margin-top:10px; width:100%;">Загрузить</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="track-card-box">
                <h3>Статистика</h3>
                <ul class="track-stats-list">
                    <li><strong>Голосов:</strong> <span class="vote-count" id="sync-vote-sidebar"><?php echo $track['vote_count']; ?></span></li>
                    <li><strong>Скачиваний:</strong> <span id="sync-download-sidebar"><?php echo $track['download_count']; ?></span></li>
                    <li><strong>Прослушиваний:</strong> <span class="play-count-display" id="sync-play-sidebar"><?php echo $track['play_count']; ?></span></li>
                </ul>
                <?php if ($currentUser && !$isAuthor): ?>
                    <div style="margin-top: 20px; border-top: 1px solid #282845; padding-top: 15px;">
                        <a href="/api/start_conversation.php?partner_id=<?php echo $track['author_id']; ?>" class="button-primary" style="text-decoration:none; display:block; text-align:center;">Написать автору</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="track-card-box">
                <h3>Поделиться треком</h3>
                <button class="button-primary share-trigger" 
                        data-url="<?php echo $protocol . '://' . $host . '/view_track.php?id=' . $trackId; ?>"
                        data-title="<?php echo $pageTitle; ?>"
                        data-image="<?php echo $ogImage; ?>">
                    <i class="fas fa-share-alt"></i> ПОДЕЛИТЬСЯ
                </button>
            </div>
        </div>

        <!-- ПРАВАЯ КОЛОНКА (ТЕПЕРЬ ГАРАНТИРОВАННО ЗАГРУЗИТСЯ) -->
        <div class="track-card-right">
            <div class="track-card-box" id="video-import">
                <h3>Видеоклип</h3>
                <?php if($isAuthor): ?>
                    <form action="/api/update_track_video.php" method="POST">
                        <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                        <div class="form-group-row">
                            <label>Ссылка на видео (YouTube / RuTube):</label>
                            <input type="text" name="video_url" class="track-card-input" value="<?php echo htmlspecialchars($track['video_url'] ?? ''); ?>" placeholder="https://...">
                        </div>
                        <button type="submit" class="button-primary" style="margin-top:10px;">Сохранить видео</button>
                    </form>
                <?php endif; ?>
                
                <?php 
                $vData = parseVideoUrl($track['video_url'] ?? '');
                if ($vData): ?>
                    <div class="video-preview-container" style="margin-top: 20px; position: relative; aspect-ratio: 16/9; background: #000; border-radius: 8px; overflow: hidden; border: 1px solid #334;">
                        <?php if($vData['type'] == 'youtube'): ?>
                            <div class="vpn-warning-overlay" id="preview-vpn-warning" style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); color:#fff; display:flex; align-items:center; justify-content:center; z-index:20; flex-direction:column;">
                                <h4 style="color:#ff4d4d;">Включите VPN для просмотра YouTube</h4>
                            </div>
                            <script>setTimeout(() => { const vpn = document.getElementById('preview-vpn-warning'); if(vpn) vpn.style.display = 'none'; }, 3000);</script>
                        <?php endif; ?>
                        <iframe src="<?php echo $vData['embed']; ?>" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>
                        <div class="video-watermark" style="position:absolute; bottom:15px; right:15px; background:#000; color:#FFD700; padding:4px 10px 4px 4px; border-radius:4px; font-weight:bold; font-size:0.8rem; pointer-events:none; border:1px solid #333; z-index: 10; display:flex; align-items:center; gap:8px;">
                            <span style="background:#fff; color:#000; padding:2px 6px; border-radius:4px;">🎵</span>
                            <span><?php echo htmlspecialchars($watermarkText); ?></span>
                        </div>
                    </div>
                <?php elseif(!$isAuthor): ?>
                    <p style="color:#666;">Автор еще не добавил видео.</p>
                <?php endif; ?>
            </div>

            <div class="track-card-box">
                <h3>Основная информация</h3>
                <form action="/api/update_track_details.php" method="POST">
                    <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                    <div class="form-group-row"><label>Автор текста:</label><?php if($isAuthor): ?><input type="text" name="lyrics_author" class="track-card-input" value="<?php echo htmlspecialchars($track['lyrics_author'] ?? ''); ?>"><?php else: ?><div class="field-display"><?php echo htmlspecialchars($track['lyrics_author'] ?: 'Не указан'); ?></div><?php endif; ?></div>
                    <div class="form-group-row"><label>Автор музыки:</label><?php if($isAuthor): ?><input type="text" name="music_author" class="track-card-input" value="<?php echo htmlspecialchars($track['music_author'] ?? ''); ?>"><?php else: ?><div class="field-display"><?php echo htmlspecialchars($track['music_author'] ?: 'Не указан'); ?></div><?php endif; ?></div>
                    <div class="form-group-row"><label>Исполнитель:</label><?php if($isAuthor): ?><input type="text" name="performer" class="track-card-input" value="<?php echo htmlspecialchars($track['performer'] ?? ''); ?>"><?php else: ?><div class="field-display"><?php echo htmlspecialchars($track['performer'] ?: 'Не указан'); ?></div><?php endif; ?></div>
                    <div class="form-group-row"><label>Дата создания трека:</label><?php if($isAuthor): ?><input type="date" name="creation_date" class="track-card-input" value="<?php echo $track['creation_date']; ?>"><?php else: ?><div class="field-display"><?php echo $track['creation_date'] ? date('d.m.Y', strtotime($track['creation_date'])) : 'Не указана'; ?></div><?php endif; ?></div>
                    
                    <!-- БЛОК ЖАНРОВ С ЗАЩИТОЙ -->
                    <div class="form-group-row"><label>Стили и жанры:</label>
                        <?php if($isAuthor): ?>
                            <select name="genres[]" multiple class="genres-select-search track-card-input" style="width: 100%;">
                                <?php if(isset($genresList) && is_array($genresList)): ?>
                                    <?php foreach ($genresList as $groupName => $subGenres): ?>
                                        <optgroup label="<?php echo htmlspecialchars($groupName); ?>">
                                            <?php foreach ($subGenres as $genre): ?>
                                                <option value="<?php echo htmlspecialchars($genre); ?>" <?php echo in_array($genre, $selectedGenres) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($genre); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        <?php else: ?>
                            <div class="field-display"><?php echo !empty($selectedGenres) ? implode(', ', array_map('htmlspecialchars', $selectedGenres)) : 'Не указаны'; ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if($isAuthor): ?><button type="submit" class="button-primary" style="margin-top:10px;">Сохранить изменения</button><?php endif; ?>
                </form>
            </div>
            
            <div class="track-card-box">
                <h3>Текст песни</h3>
                <?php if($isAuthor && empty($track['lyrics'])): ?>
                    <form action="/api/add_track_lyrics.php" method="POST"><input type="hidden" name="track_id" value="<?php echo $track['id']; ?>"><textarea name="lyrics" class="track-card-input" rows="10" placeholder="Введите текст..."></textarea><button type="submit" class="button-primary" style="margin-top:10px;">Добавить текст и получить +1 голос</button></form>
                <?php else: ?>
                    <div class="lyrics-display-area"><?php echo htmlspecialchars($track['lyrics'] ?: 'Текст еще не добавлен.'); ?></div>
                <?php endif; ?>
            </div>

            <div class="track-card-box">
                <h3>Отзывы</h3>
                <div class="comments-container">
                    <?php if(empty($comments)): ?><p style="color:#666;">Отзывов пока нет.</p><?php else: ?>
                        <?php foreach($comments as $c): ?>
                            <div class="comment-item">
                                <div class="comment-author">
                                    <img src="/uploads/avatars/<?php echo $c['avatar_filename'] ?: 'default.png'; ?>" class="comment-avatar">
                                    <strong><?php echo htmlspecialchars($c['author']); ?></strong>
                                </div>
                                <div class="comment-text"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if($currentUser): ?>
                    <form action="/api/add_track_comment.php" method="POST" style="margin-top:20px;">
                        <input type="hidden" name="track_id" value="<?php echo $trackId; ?>">
                        <textarea name="comment" class="track-card-input" rows="4" placeholder="Напишите ваш отзыв..." required></textarea>
                        <button type="submit" class="button-primary" style="margin-top:10px;">Отправить</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackId = "<?php echo $trackId; ?>";
    const localProgress = document.getElementById('local-progress');
    const timeCurrent = document.getElementById('local-current-time');
    const timeTotal = document.getElementById('local-duration-time');
    const cardVol = document.getElementById('card-volume');
    const cardRewind = document.getElementById('card-rewind');
    const cardForward = document.getElementById('card-forward');
    const progressWrapper = document.getElementById('card-progress-wrapper');

    if(cardRewind) cardRewind.onclick = () => { if(window.Player && window.Player.getCurrentTrackId() == trackId) window.Player.seekRelative(-5); };
    if(cardForward) cardForward.onclick = () => { if(window.Player && window.Player.getCurrentTrackId() == trackId) window.Player.seekRelative(5); };
    if(progressWrapper) progressWrapper.onclick = (e) => { if(window.Player && window.Player.getCurrentTrackId() == trackId) window.Player.seek(e.offsetX / progressWrapper.offsetWidth); };
    if(cardVol) cardVol.oninput = (e) => { if(window.Player) window.Player.setVolume(e.target.value); };

    document.addEventListener('playerTimeUpdate', function(e) {
        if (String(e.detail.trackId) === String(trackId)) {
            const pct = (e.detail.currentTime / e.detail.duration) * 100;
            if(localProgress) localProgress.style.width = pct + '%';
            if(timeCurrent) timeCurrent.textContent = formatTime(e.detail.currentTime);
            if(timeTotal) timeTotal.textContent = formatTime(e.detail.duration);
        }
    });

    function formatTime(s) { if (isNaN(s)) return "0:00"; const m = Math.floor(s / 60); const sec = Math.floor(s % 60); return m + ":" + (sec < 10 ? '0' : '') + sec; }
    
    // Инициализация Select2 для безопасного массива жанров
    if (typeof jQuery !== 'undefined') {
        $('.genres-select-search').select2({ maximumSelectionLength: 10, placeholder: 'Выберите стили...', allowClear: true });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
