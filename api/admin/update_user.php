<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

// --- ПРОВЕРКА БЕЗОПАСНОСТИ ---
if (!isAdmin()) {
    die("Доступ запрещен.");
}

// Получаем данные из POST-запроса
$userId = $_POST['user_id'] ?? null;
$login = $_POST['login'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$accessLevel = $_POST['access_level'] ?? null;

// Валидация
if (empty($userId) || empty($login) || empty($email) || empty($accessLevel)) {
    die("Не все обязательные поля были заполнены.");
}
if (!in_array($accessLevel, ['limited', 'full', 'admin'])) {
    die("Недопустимый уровень доступа.");
}

try {
    // Формируем SQL-запрос
    $sql = "UPDATE users SET login = ?, email = ?, access_level = ?";
    $params = [$login, $email, $accessLevel];

    // Если был введен новый пароль, добавляем его в запрос и хэшируем
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $sql .= ", password = ?";
        $params[] = $passwordHash;
    }

    $sql .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Перенаправляем обратно на страницу со списком пользователей
    header("Location: /admin/users.php");
    exit();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}