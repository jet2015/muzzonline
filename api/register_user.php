<?php
// Подключаем скрипт для работы с БД
require_once '../core/db_connect.php';

// --- 1. Получение и базовая валидация данных ---
$login = $_POST['login'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$ref_source = $_POST['ref_source'] ?? null;

if (empty($login) || empty($email) || empty($password)) {
    die("Ошибка: Все поля (Логин, Email, Пароль) обязательны для заполнения.");
}

// Проверка валидности Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Ошибка: Введен неверный формат Email адреса.");
}

// Проверка длины логина и пароля
if (strlen($login) < 4 || strlen($login) > 50) {
    die("Ошибка: Длина логина должна быть от 4 до 50 символов.");
}
if (strlen($password) < 6) {
    die("Ошибка: Пароль должен содержать не менее 6 символов.");
}
// Проверка на разрешенные символы в логине
if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
    die("Ошибка: Логин может содержать только латинские буквы, цифры и знак подчеркивания.");
}


// --- 2. Проверка, не заняты ли логин или email ---
try {
    // Проверяем логин
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $stmt->execute([$login]);
    if ($stmt->fetch()) {
        die("Ошибка: Этот логин уже занят. Пожалуйста, выберите другой.");
    }

    // Проверяем email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Ошибка: Этот Email уже зарегистрирован в системе.");
    }

} catch (\PDOException $e) {
    // На реальном проекте здесь будет логирование ошибки
    die("Ошибка базы данных при проверке данных: " . $e->getMessage());
}


// --- 3. Определение уровня доступа и источника регистрации (согласно ТЗ) ---
$access_level = 'limited'; // Уровень доступа по умолчанию
$registration_source = null;

// Проверяем, пришел ли пользователь с определенного источника
if (!empty($ref_source) && strpos($ref_source, 't.me/sunochatmix') !== false) {
    $access_level = 'full';
    $registration_source = $ref_source;
}


// --- 4. Хэширование пароля (Критически важно для безопасности!) ---
$password_hash = password_hash($password, PASSWORD_ARGON2ID);


// --- 5. Добавление нового пользователя в базу данных ---
try {
    $sql = "INSERT INTO users (login, email, password, access_level, registration_source) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login, $email, $password_hash, $access_level, $registration_source]);

    // Если всё прошло успешно, перенаправляем на страницу входа
    // В будущем можно добавить сообщение об успешной регистрации
    header('Location: /login.php?status=success');
    exit();

} catch (\PDOException $e) {
    // На реальном проекте здесь будет логирование ошибки
    die("Ошибка базы данных при создании пользователя: " . $e->getMessage());
}