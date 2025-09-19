<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'templates/header.php';

// Контроль доступа (без изменений)
$currentUser = getCurrentUser();
$allowed_roles = ['full', 'admin'];
if (!$currentUser || !in_array($currentUser['access_level'], $allowed_roles)) {
    echo '<div class="alert error">Доступ к этому разделу имеют только пользователи с полным доступом.</div>';
    require_once 'templates/footer.php';
    exit();
}

// --- ИЗМЕНЕНИЕ: Логика теперь работает с таблицей track_contests ---
$activeContest = null;
$contestTracks = [];
$winners = [];
$prizePool = 0;

try {
    // Ищем самый последний созданный конкурс треков
    $stmt = $pdo->prepare("SELECT * FROM track_contests ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $activeContest = $stmt->fetch();

    if ($activeContest) {
        // Получаем сумму донатов
        $stmt_donations = $pdo->prepare("SELECT SUM(amount) as total FROM donations WHERE track_contest_id = ? AND status = 'approved'");
        $stmt_donations->execute([$activeContest['id']]);
        $result = $stmt_donations->fetch();
        if ($result && $result['total'] > 0) {
            $prizePool = (float)$result['total'];
        }

        if ($activeContest['status'] === 'voting_active') {
            // Загружаем треки для голосования
            $sql = "SELECT tracks.id, tracks.title, tracks.filename, users.login AS author, COUNT(votes.id) AS vote_count FROM tracks JOIN users ON tracks.user_id = users.id LEFT JOIN votes ON tracks.id = votes.track_id WHERE tracks.page_type = 'contest' AND tracks.track_contest_id = ? GROUP BY tracks.id ORDER BY vote_count DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$activeContest['id']]);
            $contestTracks = $stmt->fetchAll();
        }
        elseif ($activeContest['status'] === 'closed') {
            // Загружаем победителей
            $sql = "SELECT w.place, w.amount, u.login, t.title as track_title FROM winnings w JOIN users u ON w.user_id = u.id JOIN tracks t ON w.track_id = t.id WHERE w.track_contest_id = ? ORDER BY w.place ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$activeContest['id']]);
            $winners = $stmt->fetchAll();
        }
    }
} catch (\PDOException $e) {
    echo '<div class="alert error">Произошла ошибка при загрузке данных конкурса.</div>';
}
?>

<h1>Конкурс треков <?php if ($activeContest) echo ' - ' . htmlspecialchars($activeContest['name']); ?></h1>

<?php
if ($activeContest && in_array($activeContest['status'], ['submission_active', 'voting_active'])) {
    echo '<div class="prize-pool">Текущий призовой фонд: <strong>' . number_format($prizePool, 2, '.', ' ') . ' руб.</strong></div>';
}
if (isset($_GET['upload']) && $_GET['upload'] === 'success') {
    echo '<div class="alert success">Ваш трек успешно загружен и принят на конкурс!</div>';
}

if ($activeContest) {
    switch ($activeContest['status']) {
        case 'pending':
            echo '<div class="contest-placeholder"><p>Конкурс скоро начнется. Прием работ откроется ' . date('d.m.Y в H:i', strtotime($activeContest['submission_start'])) . '. Следите за обновлениями!</p></div>';
            break;
        case 'submission_active':
            // ... (код без изменений) ...
            break;
        case 'voting_active':
            // ... (код без изменений) ...
            break;
        case 'closed':
            // ... (код без изменений) ...
            break;
    }
} else {
    echo '<div class="contest-placeholder"><p>В данный момент конкурсы не проводятся.</p></div>';
}
?>

<?php require_once 'templates/footer.php'; ?>