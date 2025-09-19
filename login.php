<?php
// Устанавливаем правильный порядок: сначала ядро, потом отображение
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'templates/header.php'; 
?>

<h1>Вход</h1>

<?php
// Показываем сообщение об успешной регистрации, если есть параметр ?status=success
if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert success">Вы успешно зарегистрированы! Теперь можете войти.</div>
<?php endif; ?>

<?php
// Показываем сообщение об ошибке, если есть параметр ?error=1
if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
    <div class="alert error">Неверный логин или пароль.</div>
<?php endif; ?>


<form class="form-styled" action="/api/login_user.php" method="POST">
    <div class="form-group">
        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" required>
    </div>

    <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
    </div>

    <button type="submit" class="button-primary">Войти</button>
</form>

<!-- --- ИЗМЕНЕНИЕ ЗДЕСЬ --- -->
<p class="centered-text">
    <a href="/forgot_password.php">Забыли пароль?</a>
</p>
<p class="centered-text">Ещё нет аккаунта? <a href="/registration.php">Зарегистрироваться</a></p>


<?php require_once 'templates/footer.php'; ?>