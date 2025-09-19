<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    die("Доступ запрещен.");
}

$trackId = $_POST['track_id'] ?? null;
$userId = $_POST['user_id'] ?? null;
$title = $_POST['title'] ?? null;

if (empty($trackId) || empty($userId) || empty($title)) {
    die("Не все данные были переданы.");
}

try {
    $stmt = $pdo->prepare("UPDATE tracks SET title = ? WHERE id = ?");
    $stmt->execute([$title, $trackId]);
    
    header("Location: /admin/edit_user.php?id=" . $userId);
    exit();
} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}