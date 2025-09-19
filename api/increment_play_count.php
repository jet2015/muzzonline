<?php
require_once '../core/db_connect.php';

$trackId = $_POST['track_id'] ?? null;

if (empty($trackId) || !is_numeric($trackId)) {
    // Тихо завершаем работу, если ID некорректен
    exit();
}

try {
    // Используем ON DUPLICATE KEY UPDATE для атомарности или простой UPDATE
    $sql = "UPDATE tracks SET play_count = play_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trackId]);
    
    // Ответ не обязателен, т.к. это фоновый запрос
    echo json_encode(['success' => true]);

} catch (\PDOException $e) {
    // В случае ошибки ничего не делаем, чтобы не прерывать работу пользователя
    // error_log('Play count error: ' . $e->getMessage());
    exit();
}
