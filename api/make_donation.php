<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

$currentUser = getCurrentUser();
if (!$currentUser || !in_array($currentUser['access_level'], ['full', 'admin'])) {
    die("Доступ запрещен.");
}

// Получаем ID конкурса текстов, сумму и ID пользователя
$lyricContestId = $_POST['lyric_contest_id'] ?? null;
$amount = $_POST['amount'] ?? null;
$userId = $currentUser['id'];

if (empty($lyricContestId) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
    die("Некорректные данные.");
}

// --- Обработка загруженного файла чека ---
$receiptFilename = null;
if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '/var/www/u3237728/data/www/muzzonline.ru/uploads/receipts/';
    // Создаем директорию, если ее нет
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Настройки безопасности
    $maxFileSize = 5 * 1024 * 1024; // 5 МБ
    $allowedMimeTypes = ['image/jpeg', 'image/png'];
    
    $tmpFilePath = $_FILES['receipt_file']['tmp_name'];
    
    // Проверка размера
    if (filesize($tmpFilePath) > $maxFileSize) { 
        die("Файл слишком большой."); 
    }

    // Проверка MIME-типа
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $uploadedMimeType = finfo_file($finfo, $tmpFilePath);
    finfo_close($finfo);
    if (!in_array($uploadedMimeType, $allowedMimeTypes)) { 
        die("Недопустимый тип файла. Разрешены только JPG и PNG."); 
    }

    // Создание безопасного имени
    $extension = strtolower(pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION));
    $receiptFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destinationPath = $uploadDir . $receiptFilename;
    
    if (!move_uploaded_file($tmpFilePath, $destinationPath)) {
        die("Ошибка сервера при сохранении файла.");
    }
} else {
    die("Файл чека не был загружен или произошла ошибка.");
}


// Запись в БД
try {
    // Сохраняем с ID конкурса текстов
    $sql = "INSERT INTO donations (user_id, lyric_contest_id, amount, receipt_filename) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $lyricContestId, $amount, $receiptFilename]);

    header('Location: /profile.php?donation=success');
    exit();
} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

