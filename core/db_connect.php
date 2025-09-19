<?php
// Настройки подключения к базе данных
$db_host = 'localhost'; // Обычно localhost
$db_name = 'u3237728_music_contest_db';  // Имя вашей базы данных
$db_user = 'u3237728_music_player25';      // Ваше имя пользователя БД (замените на своё)
$db_pass = 'oH8uH2tW7u';        // Ваш пароль от БД (замените на свой)
$charset = 'utf8mb4';   // Кодировка

// Настройки DSN (Data Source Name)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// Опции для PDO для более безопасной и эффективной работы
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Включаем режим выбрасывания исключений при ошибках
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Устанавливаем режим выборки по умолчанию (ассоциативный массив)
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Отключаем эмуляцию подготовленных запросов для большей безопасности
];

try {
    // Создаём новый объект PDO для подключения к базе данных
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // В случае ошибки подключения, выводим сообщение и прекращаем выполнение скрипта
    // На реальном проекте здесь должно быть логирование ошибки, а не вывод на экран
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>