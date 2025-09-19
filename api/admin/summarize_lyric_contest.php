<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) { die("Доступ запрещен."); }

$contestId = $_POST['contest_id'] ?? null;
if (empty($contestId)) {
    die("Не указан ID конкурса.");
}

$pdo->beginTransaction();
try {
    // 1. Находим текст с наибольшим количеством голосов
    $sql = "SELECT l.id
            FROM lyrics l
            LEFT JOIN lyrics_votes v ON l.id = v.lyric_id
            WHERE l.lyric_contest_id = ?
            GROUP BY l.id
            ORDER BY COUNT(v.id) DESC, l.id ASC
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$contestId]);
    $winnerId = $stmt->fetchColumn();

    // 2. Присваиваем статусы
    if ($winnerId) {
        // Статус 'winner' для победителя
        $stmt = $pdo->prepare("UPDATE lyrics SET status = 'winner' WHERE id = ?");
        $stmt->execute([$winnerId]);

        // Статус 'loser' для всех остальных
        $stmt = $pdo->prepare("UPDATE lyrics SET status = 'loser' WHERE lyric_contest_id = ? AND id != ?");
        $stmt->execute([$contestId, $winnerId]);
    } else {
        // Если участников не было, просто присваиваем всем (пустому списку) статус 'loser'
        $stmt = $pdo->prepare("UPDATE lyrics SET status = 'loser' WHERE lyric_contest_id = ?");
        $stmt->execute([$contestId]);
    }
    
    // 3. Переводим сам конкурс в статус "Итоги"
    $stmt = $pdo->prepare("UPDATE lyric_contests SET status = 'results' WHERE id = ?");
    $stmt->execute([$contestId]);

    $pdo->commit();
    header("Location: /admin/admin_lyrics.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Произошла ошибка при подведении итогов: " . $e->getMessage());
}