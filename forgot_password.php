<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'templates/header.php'; 
?>

<h1>Восстановление пароля</h1>

<?php // Уведомления
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    echo '<div class="alert success">Инструкции по сбросу пароля были отправлены на ваш email.</div>';
    
    // ВАЖНО: ДЛЯ ТЕСТИРОВАНИЯ мы выводим ссылку прямо здесь,
    // так как у нас нет реальной отправки писем.
    if (isset($_GET['reset_link'])) {
        echo '<div class="test-link-container"><strong>Тестовая ссылка для сброса:</strong><br><a href="' . htmlspecialchars($_GET['reset_link']) . '">' . htmlspecialchars($_GET['reset_link']) . '</a></div>';
    }
}
if (isset($_GET['error'])) {
    $errorMsg = 'Пользователь с таким email не найден.';
    echo '<div class="alert error">' . $errorMsg . '</div>';
}
?>

<form class="form-styled" action="/api/request_password_reset.php" method="POST">
    <div class="form-group">
        <label for="email">Введите ваш Email:</label>
        <input type="email" id="email" name="email" required>
        <small>Мы отправим вам ссылку для создания нового пароля.</small>
    </div>
    <button type="submit" class="button-primary">Отправить</button>
</form>

<?php require_once 'templates/footer.php'; ?>