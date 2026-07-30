<?php
// Этот скрипт не требует авторизации, так как чеки должны быть видны всем в футере.
// Безопасность обеспечивается другими методами.

// 1. ПОЛУЧЕНИЕ И ОЧИСТКА ИМЕНИ ФАЙЛА
$filename = $_GET['file'] ?? null;
if (!$filename) {
    header('HTTP/1.1 400 Bad Request');
    die('Файл не указан.');
}

// 2. КРИТИЧЕСКАЯ ЗАЩИТА от атак "Directory Traversal"
// basename() убирает все пути из имени файла, оставляя только само имя.
// Это гарантирует, что никто не сможет запросить файл типа ../core/db_connect.php
$filename = basename($filename);

// 3. ФОРМИРОВАНИЕ ПОЛНОГО И НАДЕЖНОГО ПУТИ К ФАЙЛУ
// Используем $_SERVER['DOCUMENT_ROOT'] - это самый надежный способ получить корневую папку сайта.
$filepath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/receipts/' . $filename;

// 4. ПРОВЕРКА, СУЩЕСТВУЕТ ЛИ ФАЙЛ
if (!file_exists($filepath)) {
    header('HTTP/1.1 404 Not Found');
    die('Файл не найден на сервере.');
}

// 5. ОТДАЧА ФАЙЛА БРАУЗЕРУ
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit();