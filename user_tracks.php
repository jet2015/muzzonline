<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

// Получаем ID пользователя из URL
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId) {
    die("Неверный ID пользователя.");
}

// Получаем информацию о пользователе и его треках
try {
    // Получаем логин пользователя для заголовка
    $stmt_user = $pdo->prepare("SELECT login FROM users WHERE id = ?");
    $stmt_user->execute([$userId]);
    $user = $stmt_user->fetch();

    if (!$user) {
        die("Пользователь не найден.");
    }
    $userLogin = $user['login'];

    // SQL-запрос, похожий на Хит-парад, но только для одного пользователя
    $sql = "
        SELECT 
            t.id, t.title, t.filename, t.play_count, t.download_count,
            u.id AS author_id, u.login AS author,
            COALESCE(vc.vote_count, 0) + t.purchased_votes_count AS vote_count,
            (CASE WHEN t.highlighted_until > NOW() THEN 1 ELSE 0 END) as is_highlighted
        FROM tracks t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN (
            SELECT track_id, COUNT(id) as vote_count 
            FROM votes 
            GROUP BY track_id
        ) vc ON t.id = vc.track_id
        WHERE t.page_type = 'general' AND t.user_id = ?
        ORDER BY 
            vote_count DESC,
            t.play_count DESC,
            t.upload_date DESC
    ";
    
    $stmt_tracks = $pdo->prepare($sql);
    $stmt_tracks->execute([$userId]);
    $tracks = $stmt_tracks->fetchAll(PDO::FETCH_ASSOC);
    
} catch (\PDOException $e) { 
    echo '<div class="alert error">Не удалось загрузить треки: ' . $e->getMessage() . '</div>';
    $tracks = []; 
}

$isLoggedIn = isUserLoggedIn();
$pageTitle = 'Все треки пользователя ' . htmlspecialchars($userLogin);
require_once 'templates/header.php';
?>

<style>
/* Стили для контейнера с прокруткой */
.tracklist-scroll-container {
    max-height: <?php echo count($tracks) > 15 ? '700px' : 'auto'; ?>;
    overflow-y: <?php echo count($tracks) > 15 ? 'auto' : 'visible'; ?>;
    border: 2px solid #195279;
    border-radius: 5px;
    padding: 8px;
    margin: 20px 0;
    background-color: #2b2b45;
}

.tracklist-scroll-container::-webkit-scrollbar { width: 2px; }
.tracklist-scroll-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.tracklist-scroll-container::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
.tracklist-scroll-container::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

/* ===== НАЧАЛО ИЗМЕНЕНИЯ: СТИЛИ ДЛЯ ИНДИКАТОРА СКОПИРОВАНЫ ИЗ ХИТ-ПАРАДА ===== */
.tracks-count-indicator {
    background-color: #2b2b45;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 15px;
    color: #93ebff;
    margin-bottom: 15px;
    display: inline-block;
}
/* ===== КОНЕЦ ИЗМЕНЕНИЯ ===== */

@media (max-width: 768px) {
    .tracklist-scroll-container { max-height: <?php echo count($tracks) > 15 ? '500px' : 'auto'; ?>; margin: 15px 0; }
}
@media (max-width: 480px) {
    .tracklist-scroll-container { max-height: <?php echo count($tracks) > 15 ? '450px' : 'auto'; ?>; padding: 8px; }
}
</style>

<h1>Все треки пользователя: <?php echo htmlspecialchars($userLogin); ?></h1>

<div class="tracklist" style="margin-top: 2rem;">
    <!-- ===== НАЧАЛО ИЗМЕНЕНИЯ: ДОБАВЛЕН ИНДИКАТОР КОЛИЧЕСТВА ТРЕКОВ ===== -->
    <span class="tracks-count-indicator">
        Всего треков: <?php echo count($tracks); ?>
        <?php if (count($tracks) > 15): ?>
            (показаны с прокруткой)
        <?php endif; ?>
    </span>
    <!-- ===== КОНЕЦ ИЗМЕНЕНИЯ ===== -->

    <?php if (empty($tracks)): ?>
        <p class="centered-text">У этого пользователя еще нет треков в Хит-параде.</p>
    <?php else: ?>
        <div class="tracklist-scroll-container">
        <?php foreach ($tracks as $index => $track): ?>
            <?php
            $itemClasses = 'track-item';
            if (!empty($track['is_highlighted'])) {
                $itemClasses .= ' highlighted';
            }
            ?>
            <div 
                class="<?php echo $itemClasses; ?>" 
                data-id="<?php echo $track['id']; ?>" 
                data-filename="<?php echo htmlspecialchars($track['filename']); ?>"
                data-title="<?php echo htmlspecialchars($track['title']); ?>"
                data-author="<?php echo htmlspecialchars($track['author']); ?>"
            >
                <div class="track-play-button">▶</div>
                <div class="track-info">
                    <div class="track-title">
                        <?php echo htmlspecialchars($track['title']); ?>
                        <a href="/view_track.php?id=<?php echo $track['id']; ?>" class="track-details-link" title="Подробнее о треке">🎵</a>
                    </div>
                    <div class="track-author">Автор: <a href="/profile.php?id=<?php echo $track['author_id']; ?>"><?php echo htmlspecialchars($track['author']); ?></a>  </div>
                    <div class="track-stats">
                        <span class="stat-item plays" title="Прослушиваний">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"></path></svg>
                            <span class="play-count-display"><?php echo $track['play_count']; ?></span>
                        </span>
                        <span class="stat-item downloads" title="Скачиваний">
                             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg>
                            <span><?php echo $track['download_count']; ?></span>
                        </span>
                    </div>
                </div>
                <div class="track-actions">
                    <div class="track-votes">
                        <span class="vote-count"><?php echo $track['vote_count']; ?></span>
                        <button 
                            class="vote-button" 
                            title="<?php echo $isLoggedIn ? 'Для голосования прослушайте 30 секунд' : 'Войдите, чтобы голосовать'; ?>" 
                            data-is-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>" 
                            disabled
                        >❤</button>
                    </div>
                    <a href="/api/download_track.php?id=<?php echo $track['id']; ?>" class="download-button" title="Скачать трек">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        </div> 
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>