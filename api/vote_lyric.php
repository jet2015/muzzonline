<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

header('Content-Type: application/json');

// --- Проверка авторизации ---
$currentUser = getCurrentUser();
$allowed_roles = ['full', 'admin'];
if (!$currentUser || !in_array($currentUser['access_level'], $allowed_roles)) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
    exit();
}

$lyricId = $_POST['lyric_id'] ?? null;
if (empty($lyricId)) {
    echo json_encode(['success' => false, 'error' => 'Не указан ID текста']);
    exit();
}

$userId = $currentUser['id'];

try {
    // Проверяем, не голосовал ли пользователь уже
    $stmt = $pdo->prepare("SELECT id FROM lyrics_votes WHERE user_id = ? AND lyric_id = ?");
    $stmt->execute([$userId, $lyricId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Вы уже голосовали за этот текст']);
        exit();
    }

    // Добавляем голос
    $stmt = $pdo->prepare("INSERT INTO lyrics_votes (user_id, lyric_id) VALUES (?, ?)");
    $stmt->execute([$userId, $lyricId]);

    // Получаем новый счётчик голосов
    $stmt = $pdo->prepare("SELECT COUNT(id) AS new_count FROM lyrics_votes WHERE lyric_id = ?");
    $stmt->execute([$lyricId]);
    $newVoteCount = $stmt->fetchColumn() ?? 0;

    echo json_encode(['success' => true, 'newVoteCount' => $newVoteCount]);
    exit();

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных.']);
    exit();
}

