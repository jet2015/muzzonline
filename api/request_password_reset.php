<?php
session_start();
require_once '../core/db_connect.php';

// 1. Получаем и валидируем email
$email = $_POST['email'] ?? null;
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Неверный формат email.");
}

try {
    // 2. Проверяем, существует ли пользователь с таким email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 3. Если пользователь найден, генерируем токен
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Токен действителен 1 час

        // 4. Сохраняем токен в базу данных
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);

        // 5. Формируем ссылку для сброса
        // ВАЖНО: Замените http://muzzonline.ru на ваш реальный домен
        $resetLink = "http://muzzonline.ru/reset_password.php?token=" . $token;

        // --- СИМУЛЯЦИЯ ОТПРАВКИ EMAIL ---
        // В реальном проекте здесь был бы код для отправки письма с $resetLink.
        // Вместо этого мы передаем ссылку обратно на страницу для тестирования.
        header('Location: /forgot_password.php?status=success&reset_link=' . urlencode($resetLink));
        exit();
        
    } else {
        // Если пользователь не найден, для безопасности можно показать то же сообщение,
        // чтобы злоумышленники не могли проверять существование email.
        // Но для отладки пока будем показывать ошибку.
        header('Location: /forgot_password.php?error=notfound');
        exit();
    }

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}