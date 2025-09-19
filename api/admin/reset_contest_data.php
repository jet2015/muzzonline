<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    die("Доступ запрещен.");
}

// --- НАЧИНАЕМ ТРАНЗАКЦИЮ ---
$pdo->beginTransaction();

try {
    // 1. Находим все конкурсные треки, которые нужно удалить
    $stmt = $pdo->prepare("SELECT filename FROM tracks WHERE page_type = 'contest'");
    $stmt->execute();
    $contestTracks = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Удаляем файлы этих треков с сервера
    $uploadDir = '/var/www/u3237728/data/www/muzzonline.ru/uploads/tracks/';
    foreach ($contestTracks as $filename) {
        $filePath = $uploadDir . $filename;
        if ($filename && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 3. Очищаем все связанные таблицы с помощью TRUNCATE
    // TRUNCATE TABLE быстрее, чем DELETE FROM, и сбрасывает автоинкремент
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;"); // Отключаем проверку внешних ключей
    
    $pdo->exec("DELETE FROM tracks WHERE page_type = 'contest'");
    $pdo->exec("TRUNCATE TABLE lyrics");
    $pdo->exec("TRUNCATE TABLE lyrics_votes");
    $pdo->exec("TRUNCATE TABLE winnings");
    $pdo->exec("TRUNCATE TABLE donations");
    $pdo->exec("TRUNCATE TABLE contest_seasons");
    // Таблицу `votes` очищаем через DELETE, так как она связана с `tracks`
    // и часть голосов (за треки хит-парада) должна остаться
    $pdo->exec("DELETE v FROM votes v JOIN tracks t ON v.track_id = t.id WHERE t.page_type = 'contest'");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); // Включаем проверку обратно

    // --- ПОДТВЕРЖДАЕМ ТРАНЗАКЦИЮ ---
    $pdo->commit();

    // Перенаправляем обратно в админку с сообщением об успехе
    header("Location: /admin/index.php?reset=success");
    exit();

} catch (Exception $e) {
    // --- ОТКАТЫВАЕМ ТРАНЗАКЦИЮ ---
    $pdo->rollBack();
    die("Произошла ошибка при сбросе данных: " . $e->getMessage());
}