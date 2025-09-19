<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    die("Доступ запрещен.");
}

$lyricId = $_POST['lyric_id'] ?? null;
$userId = $_POST['user_id'] ?? null;
$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;

if (empty($lyricId) || empty($userId) || empty($title) || empty($content)) {
    die("Не все данные были переданы.");
}

try {
    $stmt = $pdo->prepare("UPDATE lyrics SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $lyricId]);
    
    header("Location: /admin/edit_user.php?id=" . $userId);
    exit();
} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}