<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

// --- ПРОВЕРКА БЕЗОПАСНОСТИ ---
if (!isAdmin()) {
    die("Доступ запрещен.");
}

// Получаем данные из POST-запроса
$seasonId = $_POST['season_id'] ?? null;
$newStatus = $_POST['new_status'] ?? null;

// Допустимые статусы
$allowedStatuses = ['pending', 'submission_active', 'voting_active', 'closed'];

// Валидация
if (empty($seasonId) || empty($newStatus) || !in_array($newStatus, $allowedStatuses)) {
    // В реальном проекте лучше вернуть JSON с ошибкой
    die("Некорректные данные.");
}

try {
    // Обновляем статус только для указанного сезона
    $sql = "UPDATE contest_seasons SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $seasonId]);

    // Перенаправляем обратно на страницу управления конкурсами
    header("Location: /admin/contests.php");
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}