<?php
// Файл: /hit_parade.php (Baseline v3.28 - Чистка лишних блоков)
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'api/cron/process_boost_queue.php';

processBoostQueue($pdo);

if (!function_exists('parseVideoUrl')) {
    function parseVideoUrl($url) {
        if (empty($url)) return null;
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
            return ['type' => 'youtube', 'embed' => "https://www.youtube.com/embed/" . $match[1] . "?autoplay=1", 'id' => $match[1]];
        }
        if (preg_match('/rutube\.ru\/video\/([a-zA-Z0-9]+)/i', $url, $match)) {
            return ['type' => 'rutube', 'embed' => "https://rutube.ru/play/embed/{$match[1]}?autoplay=1", 'id' => $match[1]];
        }
        return null;
    }
}

if (!function_exists('getOrDownloadThumb')) {
    function getOrDownloadThumb($url, $type, $id) {
        $cacheDir = __DIR__ . '/uploads/thumbs/';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);
        $cacheFile = $cacheDir . $type . '_' . $id . '.jpg';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            return '/uploads/thumbs/' . basename($cacheFile);
        }
        $thumbUrl = null;
        if ($type == 'youtube') {
            $thumbUrl = "https://img.youtube.com/vi/" . $id . "/mqdefault.jpg";
        } elseif ($type == 'rutube') {
            $data = @file_get_contents("https://rutube.ru/api/video/$id/?format=json");
            $json = json_decode($data, true);
            $thumbUrl = $json['thumbnail_url'] ?? null;
        }
        if ($thumbUrl) {
            $img = @file_get_contents($thumbUrl);
            if ($img) {
                file_put_contents($cacheFile, $img);
                return '/uploads/thumbs/' . basename($cacheFile);
            }
        }
        return null;
    }
}

$pageTitle = 'Хит-парад';
require_once 'templates/header.php';

try {
    $sql = "
        SELECT t.id, t.title, t.filename, t.play_count, t.download_count, t.video_url, u.id AS author_id, u.login AS author,
        COALESCE(vc.vote_count, 0) + t.purchased_votes_count AS vote_count, w.place,
        (CASE WHEN t.is_winner_until > NOW() THEN 1 ELSE 0 END) as is_winner,
        (CASE WHEN t.highlighted_until > NOW() THEN 1 ELSE 0 END) as is_highlighted
        FROM tracks t JOIN users u ON t.user_id = u.id
        LEFT JOIN (SELECT track_id, COUNT(id) as vote_count FROM votes GROUP BY track_id) vc ON t.id = vc.track_id
        LEFT JOIN winnings w ON t.id = w.track_id
        WHERE t.page_type = 'general'
        ORDER BY 
            is_winner DESC, 
            w.place ASC, 
            is_highlighted DESC, 
            vote_count DESC, 
            t.download_count DESC, 
            t.play_count DESC, 
            t.upload_date DESC
    ";
    $tracks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) { $tracks = []; }

$isLoggedIn = isUserLoggedIn();
?>

<div class="container">
    <?php if (isset($_GET['upload_status'])): ?>
        <div class="alert <?php echo $_GET['upload_status'] === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 20px; text-shadow: none; font-weight: bold;">
            <?php echo htmlspecialchars($_GET['message'] ?? ($_GET['upload_status'] === 'success' ? 'Трек успешно загружен и опубликован!' : 'Ошибка при загрузке трека.')); ?>
        </div>
    <?php endif; ?>

    <div class="upload-form-container">
        <h3 style="text-align: center; margin-bottom: 25px; font-size: 1.5rem; color: #fff; text-shadow: -1px -1px 0 #FFD700, 1px -1px 0 #FFD700, -1px 1px 0 #FFD700, 1px 1px 0 #FFD700, 0 0 15px rgba(255, 215, 0, 0.4);">
            Загрузить свой трек
        </h3>
        <?php if ($isLoggedIn): ?>
            <form action="/api/upload_general_track.php" method="post" enctype="multipart/form-data">
                <div class="form-group-row"><input type="text" name="track_title" class="custom-input" placeholder="Название трека" required></div>
                <div class="form-group-row"><input type="file" name="track_file" class="custom-input" accept="audio/mpeg, audio/wav" required></div>
                <button type="submit" class="button-primary">ОПУБЛИКОВАТЬ В ХИТ-ПАРАД</button>
            </form>
        <?php else: ?>
            <p style="text-align:center; color:#888;">Пожалуйста, <a href="/login.php" style="color:var(--accent-color); font-weight:bold;">войдите</a>, чтобы загрузить трек.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/boosted_tracks.php'; ?>

<div class="tracklist">
    <div class="tracklist-header">
        <h2 class="parade-title">Хит-парад 🥇 <span style="font-size: 0.9rem; color: #888; font-weight: normal;">(<?php echo count($tracks); ?> треков)</span></h2>
        <input type="text" id="track-search" class="track-search-input" placeholder="Поиск по названию...">
    </div>
    
    <div class="tracklist-scroll-container">
        <?php foreach ($tracks as $track): ?>
            <div class="track-item <?php echo $track['is_highlighted'] ? 'highlighted' : ''; ?>" 
                 data-id="<?php echo $track['id']; ?>" 
                 data-filename="<?php echo htmlspecialchars($track['filename']); ?>"
                 data-title="<?php echo htmlspecialchars($track['title']); ?>"
                 data-author="<?php echo htmlspecialchars($track['author']); ?>">
                
                <button class="track-play-button">▶</button>
                
                <div class="track-info-zone">
                    <div class="track-title-row">
                        <?php if ($track['is_winner']) echo '🏆 '; ?>
                        <?php echo htmlspecialchars($track['title']); ?>
                        <a href="/view_track.php?id=<?php echo $track['id']; ?>" class="track-details-link-new" title="Подробнее о треке"><i class="fas fa-external-link-alt"></i></a>
                    </div>
                    <div class="track-author-row">
                        <a href="/profile.php?id=<?php echo $track['author_id']; ?>"><?php echo htmlspecialchars($track['author']); ?></a>
                        <a href="/user_tracks.php?id=<?php echo $track['author_id']; ?>" class="all-tracks-link">(все треки)</a>
                    </div>
                    <div class="track-mini-stats">
                        <span>👁 <span class="play-count-display"><?php echo $track['play_count']; ?></span></span>
                        <span>📥 <span class="downloads-count"><?php echo $track['download_count']; ?></span></span>
                    </div>
                </div>

                <?php 
                $videoData = parseVideoUrl($track['video_url'] ?? '');
                if ($videoData): 
                    $thumb = getOrDownloadThumb($track['video_url'], $videoData['type'], $videoData['id']);
                ?>
                    <div class="track-media-core has-video video-trigger" data-embed="<?php echo $videoData['embed']; ?>" data-type="<?php echo $videoData['type']; ?>">
                        <?php if($thumb): ?>
                            <img src="<?php echo $thumb; ?>" alt="Preview">
                        <?php else: ?>
                            <div class="rutube-placeholder"><?php echo strtoupper($videoData['type']); ?></div>
                        <?php endif; ?>
                        <div class="play-icon-overlay">▶</div>
                    </div>
                <?php else: ?>
                    <a href="/view_track.php?id=<?php echo $track['id']; ?>#video-import" class="track-media-core empty-video" title="Добавить видео">Импорт видео<br>YouTube / RuTube</a>
                <?php endif; ?>

                <div class="track-rating-zone">
                    <div class="mobile-likes-wrap">
                        <span class="vote-count"><?php echo $track['vote_count']; ?></span>
                        <button class="vote-button" data-is-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>" data-locked="true">❤</button>
                    </div>
                    <div class="mobile-plays-wrap">П: <b class="mobile-play-num"><?php echo $track['play_count']; ?></b></div>
                    <div class="mobile-downloads-wrap">
                        <span class="stat-item downloads-count"><?php echo $track['download_count']; ?></span>
                        <a href="/api/download_track.php?id=<?php echo $track['id']; ?>" class="download-button-new" title="Скачать трек"><i class="fas fa-cloud-download-alt"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
