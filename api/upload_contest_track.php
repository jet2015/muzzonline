<?php
session_start();
require_once '../core/auth.php';
require_once '../core/db_connect.php';

// --- Проверка авторизации и уровня доступа ---
$currentUser = getCurrentUser();
if (!$currentUser || !in_array($currentUser['access_level'], ['full', 'admin'])) {
    die("Доступ запрещен.");
}

// --- Находим активный сезон, в котором идет прием работ ---
try {
    $stmt = $pdo->prepare("SELECT id FROM contest_seasons WHERE status = 'submission_active' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $activeSeason = $stmt->fetch();
} catch (\PDOException $e) {
    die("Ошибка проверки сезона: " . $e->getMessage());
}

if (!$activeSeason) {
    die("В данный момент прием работ на конкурс неактивен.");
}
$seasonId = $activeSeason['id'];


// --- Логика загрузки файла (аналогична upload_track.php) ---

// 1. Проверяем файл и название
if (!isset($_FILES['track_file']) || $_FILES['track_file']['error'] !== UPLOAD_ERR_OK) {
    die("Ошибка при загрузке файла.");
}
$title = $_POST['track_title'] ?? null;
if (empty($title)) {
    die("Название трека не может быть пустым.");
}

// 2. Настройки безопасности
$uploadDir = '/var/www/u3237728/data/www/muzzonline.ru/uploads/tracks/';
$maxFileSize = 15 * 1024 * 1024; // 15 МБ
$allowedMimeTypes = ['audio/mpeg', 'audio/wav', 'audio/x-wav'];

// 3. Проверки размера и типа
$tmpFilePath = $_FILES['track_file']['tmp_name'];
if (filesize($tmpFilePath) > $maxFileSize) { die("Файл слишком большой."); }

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$uploadedMimeType = finfo_file($finfo, $tmpFilePath);
finfo_close($finfo);
if (!in_array($uploadedMimeType, $allowedMimeTypes)) { die("Недопустимый тип файла."); }

// 4. Создание безопасного имени
$extension = strtolower(pathinfo($_FILES['track_file']['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['mp3', 'wav'])) { die("Недопустимое расширение файла."); }
$newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
$destinationPath = $uploadDir . $newFileName;

// 5. Перемещение файла и запись в БД
if (move_uploaded_file($tmpFilePath, $destinationPath)) {
    try {
        $userId = $currentUser['id'];
        
        // --- Ключевое отличие: указываем page_type = 'contest' и season_id ---
        $sql = "INSERT INTO tracks (user_id, title, filename, page_type, season_id) VALUES (?, ?, ?, 'contest', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $title, $newFileName, $seasonId]);

        // --- ИЗМЕНЕНИЕ ЗДЕСЬ ---
        // Перенаправляем обратно с параметром успеха
        header('Location: /contest_tracks.php?upload=success');
        exit();

    } catch (\PDOException $e) {
        unlink($destinationPath);
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    die("Ошибка сервера при сохранении файла.");
}