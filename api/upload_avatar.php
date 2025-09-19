<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isset($_FILES['avatar_file']) || $_FILES['avatar_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /profile.php?error=upload_failed');
    exit();
}

$tmpFilePath = $_FILES['avatar_file']['tmp_name'];

// --- Проверки безопасности файла (размер, тип) ---
$uploadDir = '/var/www/u3237728/data/www/muzzonline.ru/uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$maxFileSize = 5 * 1024 * 1024; // 5 МБ
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

if (filesize($tmpFilePath) > $maxFileSize) {
    header('Location: /profile.php?error=too_large');
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$uploadedMimeType = finfo_file($finfo, $tmpFilePath);
finfo_close($finfo);
if (!in_array($uploadedMimeType, $allowedMimeTypes)) {
    header('Location: /profile.php?error=invalid_type');
    exit();
}

// --- НОВЫЙ БЛОК: ОБРАБОТКА ИЗОБРАЖЕНИЯ ---

// 1. Загружаем исходное изображение в память
$sourceImage = null;
ob_start(); // Start output buffering
switch ($uploadedMimeType) {
    case 'image/jpeg':
        $sourceImage = imagecreatefromjpeg($tmpFilePath);
        break;
    case 'image/png':
        $sourceImage = imagecreatefrompng($tmpFilePath);
        break;
    case 'image/gif':
        $sourceImage = imagecreatefromgif($tmpFilePath);
        break;
    default:
        // This case is unlikely to be reached due to prior validation
        break;
}
ob_end_clean(); // End and clean the buffer, discarding any warnings

if (!$sourceImage) {
    // If image creation failed, redirect with an error.
    // This handles cases where imagecreatefrom* functions fail
    header('Location: /profile.php?error=image_creation_failed');
    exit();
}


list($originalWidth, $originalHeight) = getimagesize($tmpFilePath);

// 2. Вычисляем размеры для обрезки до квадрата
$cropSize = min($originalWidth, $originalHeight);
$src_x = ($originalWidth - $cropSize) / 2;
$src_y = ($originalHeight - $cropSize) / 2;

// 3. Создаем новое пустое изображение (наш будущий аватар)
$finalSize = 190;
$destImage = imagecreatetruecolor($finalSize, $finalSize);

// Обеспечиваем поддержку прозрачности для PNG
imagealphablending($destImage, false);
imagesavealpha($destImage, true);
$transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
imagefilledrectangle($destImage, 0, 0, $finalSize, $finalSize, $transparent);

// 4. Копируем, обрезаем и сжимаем исходное изображение в новое
imagecopyresampled(
    $destImage,      // Куда копируем
    $sourceImage,    // Откуда копируем
    0, 0,            // Координаты назначения (x, y)
    $src_x, $src_y,  // Координаты источника (x, y)
    $finalSize, $finalSize, // Ширина и высота назначения
    $cropSize, $cropSize    // Ширина и высота источника
);

// --- КОНЕЦ БЛОКА ОБРАБОТКИ ---


// --- Сохранение обработанного аватара ---
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Удаляем старый аватар, если он есть
$stmt = $pdo->prepare("SELECT avatar_filename FROM users WHERE id = ?");
$stmt->execute([$userId]);
$oldAvatar = $stmt->fetchColumn();
if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
    unlink($uploadDir . $oldAvatar);
}

// Генерируем новое имя и сохраняем обработанный файл в формате PNG
$newFileName = 'user_' . $userId . '_' . time() . '.png';
$destinationPath = $uploadDir . $newFileName;

if (imagepng($destImage, $destinationPath, 9)) { // 9 - максимальное сжатие PNG
    try {
        $stmt = $pdo->prepare("UPDATE users SET avatar_filename = ? WHERE id = ?");
        $stmt->execute([$newFileName, $userId]);
        
        // Освобождаем память
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        header('Location: /profile.php?avatar=success');
        exit();

    } catch (\PDOException $e) {
        unlink($destinationPath);
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    header('Location: /profile.php?error=move_failed');
    exit();
}