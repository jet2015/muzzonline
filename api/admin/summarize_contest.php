<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) { die("Доступ запрещен."); }

$contestId = $_POST['contest_id'] ?? null;
if (empty($contestId)) {
    die("Не указан ID конкурса треков.");
}

$pdo->beginTransaction();
try {
    // --- Часть 1: Обработка ТРЕКОВ ---
    // Подсчет призового фонда
    $prizePool = 0;
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM donations WHERE track_contest_id = ? AND status = 'approved'");
    $stmt->execute([$contestId]);
    $result = $stmt->fetch();
    if ($result && $result['total'] > 0) {
        $prizePool = (float)$result['total'];
    }
    // Распределение призов
    $prizes = [
        1 => $prizePool * 0.70,
        2 => $prizePool * 0.20,
        3 => $prizePool * 0.10
    ];

    // Получаем все треки сезона
    $sql = "SELECT t.id, t.user_id, t.filename, COUNT(v.id) as vote_count 
            FROM tracks t 
            LEFT JOIN votes v ON t.id = v.track_id 
            WHERE t.track_contest_id = ? AND t.page_type = 'contest' 
            GROUP BY t.id 
            ORDER BY vote_count DESC, t.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$contestId]);
    $allTracks = $stmt->fetchAll();

    $winners = array_slice($allTracks, 0, 3);
    $losers = array_slice($allTracks, 3);

    // Обрабатываем победителей
    foreach ($winners as $place => $track) {
        $currentPlace = $place + 1;
        $prizeAmount = $prizes[$currentPlace] ?? 0;
        $sql = "INSERT INTO winnings (user_id, track_id, track_contest_id, place, amount) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$track['user_id'], $track['id'], $contestId, $currentPlace, $prizeAmount]);
        
        $sql = "UPDATE tracks SET page_type = 'general', track_contest_id = NULL WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$track['id']]);
    }

    // Обрабатываем остальных
    $uploadDir = '/var/www/u3237728/data/www/muzzonline.ru/uploads/tracks/';
    foreach ($losers as $track) {
        $filePath = $uploadDir . $track['filename'];
        if ($track['filename'] && file_exists($filePath)) {
            unlink($filePath);
        }
        $sql = "DELETE FROM tracks WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$track['id']]);
    }
    
    // --- Часть 2: Окончательное закрытие Конкурса Текстов ---
    // Находим последний конкурс текстов, у которого уже подведены итоги
    $stmt = $pdo->prepare("SELECT id FROM lyric_contests WHERE status = 'results' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $lyricContestToClose = $stmt->fetchColumn();
    
    if ($lyricContestToClose) {
        // Окончательно закрываем его
        $stmt = $pdo->prepare("UPDATE lyric_contests SET status = 'closed' WHERE id = ?");
        $stmt->execute([$lyricContestToClose]);
    }
    
    // --- Часть 3: Закрытие конкурса ТРЕКОВ ---
    $sql = "UPDATE track_contests SET status = 'closed' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$contestId]);

    $pdo->commit();
    header("Location: /admin/admin_tracks.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Произошла ошибка при подведении итогов: " . $e->getMessage());
}

