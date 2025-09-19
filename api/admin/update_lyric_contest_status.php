<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) { die("Доступ запрещен."); }

$contestId = $_POST['contest_id'] ?? null;
$newStatus = $_POST['new_status'] ?? null;
// --- ИЗМЕНЕНИЕ: Добавляем 'results' в массив ---
$allowedStatuses = ['pending', 'submission_active', 'voting_active', 'results', 'closed'];

if (empty($contestId) || empty($newStatus) || !in_array($newStatus, $allowedStatuses)) {
    die("Некорректные данные.");
}

try {
    $sql = "UPDATE lyric_contests SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $contestId]);
    header("Location: /admin/admin_lyrics.php");
    exit();
} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}