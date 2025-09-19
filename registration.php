<?php
// Устанавливаем правильный порядок: сначала ядро, потом отображение
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'templates/header.php'; 
?>

<h1>Регистрация</h1>

<form class="form-styled" action="/api/register_user.php" method="POST">
    <div class="form-group">
        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" required minlength="4" maxlength="50">
        <small>От 4 до 50 символов, только латинские буквы и цифры.</small>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <small>На этот адрес будет отправлена ссылка для восстановления пароля.</small>
    </div>

    <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required minlength="6">
        <small>Минимум 6 символов.</small>
    </div>

    <div class="form-group">
        <input type="hidden" name="ref_source" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">
    </div>

    <button type="submit" class="button-primary">Зарегистрироваться</button>
</form>

<p class="centered-text">Уже есть аккаунт? <a href="/login.php">Войти</a></p>


<?php require_once 'templates/footer.php'; ?>