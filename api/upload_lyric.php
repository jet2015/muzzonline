<?php
session_start();
require_once '../core/auth.php';
require_once '../core/db_connect.php';

// --- Проверка авторизации и уровня доступа ---
$currentUser = getCurrentUser();
if (!$currentUser || !in_array($currentUser['access_level'], ['full', 'admin'])) {
    die("Доступ запрещен.");
}
$userId = $currentUser['id'];

// --- Находим активный конкурс текстов ---
try {
    $stmt = $pdo->prepare("SELECT id FROM lyric_contests WHERE status = 'submission_active' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $activeContest = $stmt->fetch();
} catch (\PDOException $e) {
    die("Ошибка проверки конкурса: " . $e->getMessage());
}

if (!$activeContest) {
    die("В данный момент прием работ на конкурс текстов неактивен.");
}
$contestId = $activeContest['id'];

// --- ИЗМЕНЕНИЕ: Проверяем, сколько текстов пользователь уже подал ---
try {
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM lyrics WHERE user_id = ? AND lyric_contest_id = ?");
    $stmt_check->execute([$userId, $contestId]);
    $submissionCount = $stmt_check->fetchColumn();

    if ($submissionCount >= 2) {
        // Если лимит достигнут, перенаправляем обратно с ошибкой
        header('Location: /contest_lyrics.php?error=limit_reached');
        exit();
    }
} catch (\PDOException $e) {
    die("Ошибка проверки лимита: " . $e->getMessage());
}


// 1. Получаем данные из формы
$title = $_POST['lyric_title'] ?? null;
$content = $_POST['lyric_content'] ?? null;

// 2. Валидация
if (empty($title) || empty($content)) {
    die("Название и текст не могут быть пустыми.");
}

// 3. Запись в БД
try {
    $sql = "INSERT INTO lyrics (user_id, lyric_contest_id, title, content) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $contestId, $title, $content]);

    header('Location: /contest_lyrics.php?upload=success');
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}