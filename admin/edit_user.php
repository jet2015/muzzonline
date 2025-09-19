<?php
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

$userId = $_GET['id'] ?? null;
if (!$userId) {
    die("ID пользователя не указан.");
}

// Запрос данных пользователя
$stmt = $pdo->prepare("SELECT id, login, email, access_level FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("Пользователь с таким ID не найден.");
}

// --- ИЗМЕНЕНИЕ: Запрос теперь использует новую таблицу `lyric_contests` ---
$sql_lyrics = "SELECT l.id, l.title, l.content, l.status, lc.name as contest_name 
               FROM lyrics l 
               JOIN lyric_contests lc ON l.lyric_contest_id = lc.id 
               WHERE l.user_id = ? 
               ORDER BY lc.id DESC, l.id DESC";
$stmt_lyrics = $pdo->prepare($sql_lyrics);
$stmt_lyrics->execute([$userId]);
$userLyrics = $stmt_lyrics->fetchAll();

// Запрос всех треков пользователя (без изменений)
$sql_tracks = "SELECT id, title, page_type, upload_date FROM tracks WHERE user_id = ? ORDER BY upload_date DESC";
$stmt_tracks = $pdo->prepare($sql_tracks);
$stmt_tracks->execute([$userId]);
$userTracks = $stmt_tracks->fetchAll();

// Запрос всех голосований пользователя (без изменений)
$sql_votes = "SELECT t.title, 'track' as type, v.voted_at FROM votes v JOIN tracks t ON v.track_id = t.id WHERE v.user_id = ?
              UNION ALL
              SELECT l.title, 'lyric' as type, lv.voted_at FROM lyrics_votes lv JOIN lyrics l ON lv.lyric_id = l.id WHERE lv.user_id = ?
              ORDER BY voted_at DESC";
$stmt_votes = $pdo->prepare($sql_votes);
$stmt_votes->execute([$userId, $userId]);
$userVotes = $stmt_votes->fetchAll();

?>

<h1>Редактирование пользователя: <?php echo htmlspecialchars($user['login']); ?></h1>

<!-- Форма редактирования данных (без изменений) -->
<form class="admin-form" action="/api/admin/update_user.php" method="POST">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <div class="form-group"><label for="login">Логин:</label><input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required></div>
    <div class="form-group"><label for="email">Email:</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
    <div class="form-group"><label for="password">Новый пароль:</label><input type="password" id="password" name="password"><small>Оставьте пустым, чтобы не менять пароль.</small></div>
    <div class="form-group">
        <label for="access_level">Уровень доступа:</label>
        <select id="access_level" name="access_level">
            <option value="limited" <?php if ($user['access_level'] === 'limited') echo 'selected'; ?>>Limited</option>
            <option value="full" <?php if ($user['access_level'] === 'full') echo 'selected'; ?>>Full</option>
            <option value="admin" <?php if ($user['access_level'] === 'admin') echo 'selected'; ?>>Admin</option>
        </select>
    </div>
    <div class="form-actions"><button type="submit" class="btn btn-save">Сохранить изменения</button><a href="users.php" class="btn btn-cancel">Отмена</a></div>
</form>

<!-- Блок с историей активности -->
<div class="user-activity-container">

    <!-- История текстов -->
    <div class="user-content-history">
        <h2>История участия в конкурсах текстов</h2>
        <?php if (empty($userLyrics)): ?>
            <p>Этот пользователь еще не участвовал в конкурсах текстов.</p>
        <?php else: ?>
            <?php foreach ($userLyrics as $lyric): ?>
                <details class="history-item <?php if ($lyric['status'] === 'winner') echo 'lyric-winner'; ?>">
                    <summary>
                        <div>
                            <span class="history-title"><?php echo htmlspecialchars($lyric['title']); ?></span>
                            <!-- --- ИЗМЕНЕНИЕ: Отображаем название конкурса, а не сезона --- -->
                            <span class="history-season">(Конкурс: <?php echo htmlspecialchars($lyric['contest_name']); ?>)</span>
                        </div>
                        <a href="edit_lyric.php?id=<?php echo $lyric['id']; ?>" class="btn btn-edit">Редактировать</a>
                    </summary>
                    <div class="history-content"><?php echo nl2br(htmlspecialchars($lyric['content'])); ?></div>
                </details>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- История треков -->
    <div class="user-content-history">
        <h2>Загруженные треки</h2>
        <?php if (empty($userTracks)): ?>
            <p>Этот пользователь еще не загружал треки.</p>
        <?php else: ?>
            <ul class="history-list">
                <?php foreach ($userTracks as $track): ?>
                    <li>
                        <div>
                            <span class="history-title"><?php echo htmlspecialchars($track['title']); ?></span>
                            <span class="history-meta">
                                (<?php echo $track['page_type'] === 'general' ? 'Хит-парад' : 'Конкурс'; ?>, <?php echo date('d.m.Y', strtotime($track['upload_date'])); ?>)
                            </span>
                        </div>
                        <a href="edit_track.php?id=<?php echo $track['id']; ?>" class="btn btn-edit">Редактировать</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- История голосований -->
    <div class="user-content-history">
        <h2>История голосований</h2>
        <?php if (empty($userVotes)): ?>
            <p>Этот пользователь еще не голосовал.</p>
        <?php else: ?>
            <ul class="history-list">
                <?php foreach ($userVotes as $vote): ?>
                    <li>
                        <span class="history-title">Проголосовал за "<?php echo htmlspecialchars($vote['title']); ?>"</span>
                        <span class="history-meta">
                            (<?php echo $vote['type'] === 'track' ? 'Трек' : 'Текст'; ?>, <?php echo date('d.m.Y H:i', strtotime($vote['voted_at'])); ?>)
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>