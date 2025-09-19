<?php
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

$seasonId = $_GET['season_id'] ?? null;
if (!$seasonId) {
    die("Не указан ID сезона.");
}

// Получаем информацию о сезоне
$stmt_season = $pdo->prepare("SELECT name FROM contest_seasons WHERE id = ?");
$stmt_season->execute([$seasonId]);
$seasonName = $stmt_season->fetchColumn();

if (!$seasonName) {
    die("Сезон не найден.");
}

// Получаем все тексты для этого сезона с авторами и количеством голосов
$sql = "SELECT l.title, u.login as author, COUNT(v.id) as vote_count
        FROM lyrics l
        JOIN users u ON l.user_id = u.id
        LEFT JOIN lyrics_votes v ON l.id = v.lyric_id
        WHERE l.season_id = ?
        GROUP BY l.id
        ORDER BY vote_count DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$seasonId]);
$lyrics = $stmt->fetchAll();
?>

<h1>Участники конкурса текстов</h1>
<h2>Сезон: <?php echo htmlspecialchars($seasonName); ?></h2>

<table class="admin-table">
    <thead>
        <tr>
            <th>Место</th>
            <th>Название</th>
            <th>Автор</th>
            <th>Голосов</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($lyrics)): ?>
            <tr><td colspan="4">На этот сезон еще не было подано ни одного текста.</td></tr>
        <?php else: ?>
            <?php foreach ($lyrics as $index => $lyric): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($lyric['title']); ?></td>
                    <td><?php echo htmlspecialchars($lyric['author']); ?></td>
                    <td><?php echo $lyric['vote_count']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 2rem;">
    <a href="contests.php" class="btn btn-cancel">Назад к сезонам</a>
</div>

<?php require_once 'templates/footer.php'; ?>