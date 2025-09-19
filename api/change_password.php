<?php
session_start();
require_once '../core/db_connect.php';
require_once '../core/auth.php';

// --- Контроль доступа ---
if (!isUserLoggedIn()) {
    // Если сессии нет, отправляем на главную
    header('Location: /');
    exit();
}

// 1. Получаем данные из формы
$currentPassword = $_POST['current_password'] ?? null;
$newPassword = $_POST['new_password'] ?? null;
$confirmPassword = $_POST['confirm_password'] ?? null;

// 2. Валидация
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    die("Все поля обязательны для заполнения.");
}

if (strlen($newPassword) < 6) {
    header('Location: /profile.php?error=short');
    exit();
}

if ($newPassword !== $confirmPassword) {
    header('Location: /profile.php?error=mismatch');
    exit();
}

// 3. Проверка текущего пароля
try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];

    // Получаем текущий хэш пароля пользователя из БД
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Сверяем введенный текущий пароль с хэшем в базе
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        // Если пароль неверный, возвращаем ошибку
        header('Location: /profile.php?error=wrongpass');
        exit();
    }

    // 4. Если все проверки пройдены, обновляем пароль
    $newPasswordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newPasswordHash, $userId]);

    // Возвращаем на страницу профиля с сообщением об успехе
    header('Location: /profile.php?status=success');
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}