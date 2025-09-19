<?php
// Этот файл нужно будет подключать на страницах, где важна авторизация.
// Убедитесь, что session_start() вызвана до подключения этого файла.

/**
 * Проверяет, авторизован ли текущий пользователь.
 *
 * @return bool True, если пользователь авторизован, иначе false.
 */
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Возвращает данные авторизованного пользователя из сессии.
 *
 * @return array|null Массив с данными пользователя или null, если не авторизован.
 */
function getCurrentUser()
{
    if (!isUserLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'login' => $_SESSION['user_login'],
        'access_level' => $_SESSION['user_access_level'],
    ];
}

/**
 * --- НОВАЯ ФУНКЦИЯ ---
 * Проверяет, является ли текущий пользователь администратором.
 *
 * @return bool True, если пользователь является администратором, иначе false.
 */
function isAdmin()
{
    $user = getCurrentUser();
    return $user !== null && $user['access_level'] === 'admin';
}