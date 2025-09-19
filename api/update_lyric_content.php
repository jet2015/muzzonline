<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

if (!isUserLoggedIn()) {
    die("Доступ запрещен.");
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$lyricId = $_POST['lyric_id'] ?? null;
$content = $_POST['content'] ?? null;

if (empty($lyricId) || !isset($content)) {
    die("Недостаточно данных.");
}

try {
    // Проверка безопасности: убеждаемся, что пользователь является автором текста
    $stmt = $pdo->prepare("SELECT user_id FROM lyrics WHERE id = ?");
    $stmt->execute([$lyricId]);
    $authorId = $stmt->fetchColumn();

    if ($authorId != $userId) {
        // Если ID не совпадают, прерываем операцию
        die("У вас нет прав для редактирования этого текста.");
    }

    // Если проверка пройдена, обновляем текст
    $stmt = $pdo->prepare("UPDATE lyrics SET content = ? WHERE id = ?");
    $stmt->execute([$content, $lyricId]);
    
    // Перенаправляем обратно с сообщением об успехе
    header('Location: /contest_lyrics.php?edit=success');
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}