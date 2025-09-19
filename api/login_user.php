<?php
// <-- 1. Убедитесь, что session_start() находится в самом верху, до любого вывода.
session_start();

// Подключаем скрипт для работы с БД
require_once '../core/db_connect.php';

// --- Получение данных из формы ---
// <-- 2. Проверьте, что имена 'login' и 'password' совпадают с атрибутами name="..." в HTML-форме в login.php
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;

if (empty($login) || empty($password)) {
    header('Location: /login.php?error=1');
    exit();
}

// --- Поиск пользователя в базе данных ---
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    // <-- 3. ЭТО САМАЯ ВАЖНАЯ СТРОКА!
    // Она сравнивает введенный пароль с хэшем в базе.
    // Если она не срабатывает, смотрите пункт 1 про VARCHAR(255).
    if ($user && password_verify($password, $user['password'])) {
        
        // <-- 4. Здесь происходит "вход". Данные пользователя записываются в сессию.
        session_regenerate_id(); // Защита от фиксации сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_access_level'] = $user['access_level'];

        // <-- 5. Если всё успешно, происходит перенаправление на главную страницу.
        // Если вы видите белый экран, возможно, перед этой строкой есть какой-то случайный вывод (echo, пробелы).
        header('Location: /');
        exit();

    } else {
        // Если пользователь не найден или пароль неверный, возвращаем на страницу входа с ошибкой.
        header('Location: /login.php?error=1');
        exit();
    }

} catch (\PDOException $e) {
    // На реальном проекте здесь будет логирование ошибки
    die("Ошибка базы данных: " . $e->getMessage());
}