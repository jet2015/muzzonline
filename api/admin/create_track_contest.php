<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    die("Доступ запрещен.");
}

$name = $_POST['name'] ?? null;
$submission_start = $_POST['submission_start'] ?? null;
$submission_end = $_POST['submission_end'] ?? null;
$voting_end = $_POST['voting_end'] ?? null;

if (empty($name) || empty($submission_start) || empty($submission_end) || empty($voting_end)) {
    die("Все поля обязательны для заполнения.");
}

$pdo->beginTransaction();
try {
    // 1. Создаем новый конкурс треков
    $sql = "INSERT INTO track_contests (name, submission_start, submission_end, voting_end) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $submission_start, $submission_end, $voting_end]);
    $newTrackContestId = $pdo->lastInsertId();

    // 2. --- ИЗМЕНЕНИЕ: Находим донаты, собранные после последнего конкурса текстов ---
    $stmt_lyrics = $pdo->prepare("SELECT id FROM lyric_contests WHERE status = 'results' ORDER BY id DESC LIMIT 1");
    $stmt_lyrics->execute();
    $lyricContestId = $stmt_lyrics->fetchColumn();

    if ($lyricContestId) {
        // "Присваиваем" эти донаты новому конкурсу треков
        $stmt_update = $pdo->prepare("UPDATE donations SET track_contest_id = ? WHERE lyric_contest_id = ? AND track_contest_id IS NULL");
        $stmt_update->execute([$newTrackContestId, $lyricContestId]);
    }

    $pdo->commit();
    header("Location: /admin/admin_tracks.php");
    exit();

} catch (\PDOException $e) {
    $pdo->rollBack();
    die("Ошибка базы данных: " . $e->getMessage());
}