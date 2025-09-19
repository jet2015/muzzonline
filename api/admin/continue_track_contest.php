<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) { die("Доступ запрещен."); }

$contestId = $_POST['contest_id'] ?? null;
$newVotingEnd = $_POST['new_voting_end'] ?? null;

if (empty($contestId) || empty($newVotingEnd)) {
    die("Некорректные данные.");
}

$pdo->beginTransaction();
try {
    // 1. Находим треки-победители, чтобы вернуть их в конкурс
    $stmt = $pdo->prepare("SELECT track_id FROM winnings WHERE track_contest_id = ?");
    $stmt->execute([$contestId]);
    $winnerTrackIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Удаляем старые записи о выигрышах
    $stmt = $pdo->prepare("DELETE FROM winnings WHERE track_contest_id = ?");
    $stmt->execute([$contestId]);

    // 3. Возвращаем треки-победители обратно в статус "конкурсных"
    if (!empty($winnerTrackIds)) {
        $inQuery = implode(',', array_fill(0, count($winnerTrackIds), '?'));
        $stmt = $pdo->prepare("UPDATE tracks SET page_type = 'contest' WHERE id IN ($inQuery)");
        $stmt->execute($winnerTrackIds);
    }

    // 4. Обновляем сам конкурс
    $stmt = $pdo->prepare("UPDATE track_contests SET status = 'voting_active', voting_end = ? WHERE id = ?");
    $stmt->execute([$newVotingEnd, $contestId]);

    $pdo->commit();
    header("Location: /admin/admin_tracks.php");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Ошибка при возобновлении конкурса: " . $e->getMessage());
}