<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

// Устанавливаем заголовок, чтобы ответ всегда был в формате JSON
header('Content-Type: application/json');

// --- Проверка авторизации ---
if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit();
}

// --- Получение ID трека ---
$trackId = $_POST['track_id'] ?? null;
if (empty($trackId)) {
    echo json_encode(['success' => false, 'error' => 'Не указан ID трека']);
    exit();
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

try {
    // --- Проверяем, не голосовал ли пользователь за этот трек ранее ---
    $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND track_id = ?");
    $stmt->execute([$userId, $trackId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Вы уже голосовали за этот трек']);
        exit();
    }

    // --- Добавляем голос в базу ---
    $stmt = $pdo->prepare("INSERT INTO votes (user_id, track_id) VALUES (?, ?)");
    $stmt->execute([$userId, $trackId]);

    // --- Получаем новый счётчик голосов ---
    $stmt = $pdo->prepare("SELECT COUNT(id) AS new_count FROM votes WHERE track_id = ?");
    $stmt->execute([$trackId]);
    $result = $stmt->fetch();
    $newVoteCount = $result['new_count'] ?? 0;

    // --- Отправляем успешный ответ ---
    echo json_encode(['success' => true, 'newVoteCount' => $newVoteCount]);
    exit();

} catch (\PDOException $e) {
    // Отправляем ошибку базы данных
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных.']);
    // На реальном проекте здесь должно быть логирование: error_log($e->getMessage());
    exit();
}