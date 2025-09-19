<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    die("Доступ запрещен.");
}

// --- ИЗМЕНЕНИЕ: Получаем все данные из формы ---
$name = $_POST['name'] ?? null;
$submission_start = $_POST['submission_start'] ?? null;
$submission_end = $_POST['submission_end'] ?? null;
$voting_end = $_POST['voting_end'] ?? null;

// Валидация
if (empty($name) || empty($submission_start) || empty($submission_end) || empty($voting_end)) {
    die("Все поля обязательны для заполнения.");
}
if (strtotime($submission_start) >= strtotime($submission_end) || strtotime($submission_end) >= strtotime($voting_end)) {
    die("Даты указаны некорректно. Проверьте, что они идут в правильной последовательности.");
}

try {
    // --- ИЗМЕНЕНИЕ: Вставляем все данные в базу ---
    $sql = "INSERT INTO lyric_contests (name, submission_start, submission_end, voting_end) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $submission_start, $submission_end, $voting_end]);
    
    header("Location: /admin/admin_lyrics.php");
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}