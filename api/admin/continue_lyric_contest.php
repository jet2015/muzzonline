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
    // 1. Возвращаем всем участникам статус 'submitted'
    $stmt = $pdo->prepare("UPDATE lyrics SET status = 'submitted' WHERE lyric_contest_id = ?");
    $stmt->execute([$contestId]);
    
    // 2. Обновляем сам конкурс
    $stmt = $pdo->prepare("UPDATE lyric_contests SET status = 'voting_active', voting_end = ? WHERE id = ?");
    $stmt->execute([$newVotingEnd, $contestId]);

    $pdo->commit();
    header("Location: /admin/admin_lyrics.php");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Ошибка при возобновлении конкурса: " . $e->getMessage());
}