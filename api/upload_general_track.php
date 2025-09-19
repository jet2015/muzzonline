<?php
// Файл: api/upload_general_track.php (НОВЫЙ УПРОЩЕННЫЙ СКРИПТ)
session_start();
require_once '../core/db_connect.php'; // Обратите внимание на путь
require_once '../core/auth.php';

// 1. Проверяем, авторизован ли пользователь
if (!isUserLoggedIn()) {
    // В идеале здесь должно быть перенаправление на страницу ошибки или входа
    die("Ошибка: Доступ запрещен. Пожалуйста, войдите в систему.");
}

// 2. Проверяем, что все данные пришли
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['track_file']) || empty($_POST['track_title'])) {
    die("Ошибка: Не все данные были отправлены.");
}

// 3. Валидация и обработка данных (аналогично старому скрипту, но без конкурсов)
$userId = $_SESSION['user_id'];
$trackTitle = trim($_POST['track_title']);
$trackFile = $_FILES['track_file'];

// Проверка на ошибки загрузки
if ($trackFile['error'] !== UPLOAD_ERR_OK) {
    die("Ошибка при загрузке файла. Код: " . $trackFile['error']);
}

// Проверка типа файла
$allowedMimeTypes = ['audio/mpeg', 'audio/wav', 'audio/x-wav'];
$fileMimeType = mime_content_type($trackFile['tmp_name']);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    die("Ошибка: Неверный формат файла. Разрешены только MP3 и WAV.");
}

// 4. Сохранение файла
$uploadDir = '../uploads/tracks/'; // Путь от папки /api/
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$fileExtension = strtolower(pathinfo($trackFile['name'], PATHINFO_EXTENSION));
$newFileName = 'track_' . uniqid() . '.' . $fileExtension;
$uploadFilePath = $uploadDir . $newFileName;

if (!move_uploaded_file($trackFile['tmp_name'], $uploadFilePath)) {
    die("Ошибка: не удалось переместить загруженный файл.");
}

// 5. Запись в базу данных
try {
    $sql = "INSERT INTO tracks (user_id, title, filename, page_type, upload_date) VALUES (?, ?, ?, 'general', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $trackTitle, $newFileName]);
    
    // 6. Перенаправление обратно на главную страницу
    header("Location: /");
    exit();

} catch (\PDOException $e) {
    // В случае ошибки удаляем загруженный файл
    if (file_exists($uploadFilePath)) {
        unlink($uploadFilePath);
    }
    die("Ошибка базы данных: " . $e->getMessage());
}
?>