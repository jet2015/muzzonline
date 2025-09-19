<?php 
require_once 'templates/header.php'; 
require_once 'core/db_connect.php';
require_once 'core/auth.php';

try {
    $sql = "SELECT 
                tracks.id, 
                tracks.title, 
                tracks.filename,
                tracks.play_count,
                tracks.download_count,
                users.login AS author,
                COUNT(votes.id) AS vote_count
            FROM tracks
            JOIN users ON tracks.user_id = users.id
            LEFT JOIN votes ON tracks.id = votes.track_id
            WHERE tracks.page_type = 'general'
            GROUP BY tracks.id
            ORDER BY vote_count DESC, tracks.upload_date DESC";
    
    $stmt = $pdo->query($sql);
    $tracks = $stmt->fetchAll();
} catch (\PDOException $e) { $tracks = []; }

$isLoggedIn = isUserLoggedIn();
?>

<h1>Хит-парад 🥇</h1>

<?php if ($isLoggedIn): ?>
    <div class="upload-form-container">
        <h3>Загрузить свой трек</h3>
        <!-- ИЗМЕНЕНИЕ ЗДЕСЬ: action теперь указывает на новый скрипт -->
        <form class="form-styled" action="/api/upload_general_track.php" method="post" enctype="multipart/form-data">
            <div class="form-group"><label for="track_title">Название трека:</label><input type="text" id="track_title" name="track_title" required></div>
            <div class="form-group"><label for="track_file">Аудиофайл (MP3, WAV):</label><input type="file" id="track_file" name="track_file" accept="audio/mpeg, audio/wav" required></div>
            <button type="submit" class="button-primary">Загрузить</button>
        </form>
    </div>
<?php else: ?>
    <p class="centered-text"><a href="/login.php">Войдите</a> или <a href="/registration.php">зарегистрируйтесь</a>, чтобы загрузить свой трек.</p>
<?php endif; ?>

<div class="tracklist">
    <h2>Все треки</h2>
    <?php if (empty($tracks)): ?>
        <p>Треков пока нет. Станьте первым!</p>
    <?php else: ?>
        <?php foreach ($tracks as $index => $track): ?>
            <div class="track-item" data-id="<?php echo $track['id']; ?>" data-filename="<?php echo htmlspecialchars($track['filename']); ?>">
                <div class="track-play-button">▶</div>
                <div class="track-info">
                    <div class="track-title"><?php echo htmlspecialchars($track['title']); ?></div>
                    <div class="track-author">Автор: <?php echo htmlspecialchars($track['author']); ?></div>
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
                        <button class="vote-button" title="Для голосования прослушайте 30 секунд" data-is-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>" disabled>❤</button>
                    </div>
                    <a href="/api/download_track.php?id=<?php echo $track['id']; ?>" class="download-button" title="Скачать трек">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>