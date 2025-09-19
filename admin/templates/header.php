<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) {
    http_response_code(403);
    die("Доступ запрещен. Этот раздел только для администраторов.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="/admin/assets/css/admin_style.css">
</head>
<body>

<aside class="admin-sidebar">
    <h2>Админ-панель</h2>
    <nav>
        <a href="/admin/index.php">Главная</a>
        <a href="/admin/users.php">Пользователи</a>
        <!-- ИЗМЕНЕНИЯ ЗДЕСЬ -->
        <a href="/admin/admin_tracks.php">Конкурс Треков</a>
        <a href="/admin/admin_lyrics.php">Конкурс Текстов</a>
        <a href="/admin/donations.php">Донаты</a>
        <a href="/" class="logout">Вернуться на сайт</a>
    </nav>
</aside>

<main class="admin-content">