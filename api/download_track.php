<?php
require_once '../core/db_connect.php';

$trackId = $_GET['id'] ?? null;

if (empty($trackId) || !is_numeric($trackId)) {
    die("Некорректный ID трека.");
}

try {
    // 1. Находим трек в базе
    $stmt = $pdo->prepare("SELECT filename, title FROM tracks WHERE id = ?");
    $stmt->execute([$trackId]);
    $track = $stmt->fetch();

    if (!$track) {
        die("Трек не найден.");
    }
    
    // 2. Увеличиваем счетчик скачиваний
    $updateStmt = $pdo->prepare("UPDATE tracks SET download_count = download_count + 1 WHERE id = ?");
    $updateStmt->execute([$trackId]);

    // 3. Отдаем файл для скачивания
    $filePath = '/var/www/u3237728/data/www/muzzonline.ru/uploads/tracks/' . $track['filename'];
    
    if (file_exists($filePath)) {
        // Получаем расширение для правильного MIME-типа
        $extension = strtolower(pathinfo($track['filename'], PATHINFO_EXTENSION));
        $mime_type = ($extension === 'mp3') ? 'audio/mpeg' : 'audio/wav';
        
        // Формируем красивое имя файла для пользователя
        $userFriendlyFilename = $track['title'] . '.' . $extension;

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $userFriendlyFilename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Очищаем буфер вывода перед отправкой файла
        ob_clean();
        flush();
        
        readfile($filePath);
        exit;
    } else {
        die("Файл не найден на сервере.");
    }

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
