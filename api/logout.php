<?php
// Всегда запускаем сессию перед тем, как с ней работать
session_start();

// 1. Очищаем массив сессии
$_SESSION = [];

// 2. Если используется cookie для сессии, удаляем его
// Это обеспечивает более надежный выход
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Уничтожаем сессию на сервере
session_destroy();

// 4. Перенаправляем на главную страницу
header('Location: /');
exit();