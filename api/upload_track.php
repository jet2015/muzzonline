<?php
// Запускаем сессию для проверки авторизации
session_start();
require_once '../core/auth.php';
require_once '../core/db_connect.php';

// --- Проверка авторизации ---
if (!isUserLoggedIn()) {
    die("Доступ запрещен. Только для авторизованных пользователей.");
}

$currentUser = getCurrentUser();

// --- ИЗМЕНЕНИЕ: Находим активный КОНКУРС ТРЕКОВ ---
try {
    $stmt = $pdo->prepare("SELECT id FROM track_contests WHERE status = 'submission_active' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $activeContest = $stmt->fetch();
} catch (\PDOException $e) {
    die("Ошибка проверки конкурса: " . $e->getMessage());
}

if (!$activeContest) {
    die("В данный момент прием работ на конкурс треков неактивен.");
}
$contestId = $activeContest['id'];


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

// 3. Проверки
$tmpFilePath = $_FILES['track_file']['tmp_name'];
if (filesize($tmpFilePath) > $maxFileSize) { die("Файл слишком большой."); }

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$uploadedMimeType = finfo_file($finfo, $tmpFilePath);
finfo_close($finfo);
if (!in_array($uploadedMimeType, $allowedMimeTypes)) { die("Недопустимый тип файла."); }

// 4. Безопасное имя файла
$extension = strtolower(pathinfo($_FILES['track_file']['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['mp3', 'wav'])) { die("Недопустимое расширение файла."); }
$newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
$destinationPath = $uploadDir . $newFileName;

// 5. Перемещение файла и запись в БД
if (move_uploaded_file($tmpFilePath, $destinationPath)) {
    try {
        $userId = $currentUser['id'];
        
        // --- ИЗМЕНЕНИЕ: Указываем page_type = 'contest' и track_contest_id ---
        $sql = "INSERT INTO tracks (user_id, title, filename, page_type, track_contest_id) VALUES (?, ?, ?, 'contest', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $title, $newFileName, $contestId]);

        header('Location: /contest_tracks.php?upload=success');
        exit();

    } catch (\PDOException $e) {
        unlink($destinationPath);
        die("Ошибка базы данных: " . $e->getMessage());
    }

} else {
    die("Ошибка сервера при сохранении файла.");
}
