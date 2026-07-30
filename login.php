<?php
// Файл: /login.php (Baseline v2.9 - Сброс плеера при входе)
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

if (isUserLoggedIn()) {
    header('Location: /profile.php');
    exit();
}

$pageTitle = 'Вход';
require_once 'templates/header.php';
?>

<link rel="stylesheet" href="/assets/css/forms.css">

<div class="container">
    <div class="auth-card">
        <h1>Вход</h1>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert success">Вы успешно зарегистрированы! Можете войти.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
            <div class="alert error">Неверный логин или пароль.</div>
        <?php endif; ?>

        <form action="/api/login_user.php" method="POST">
            <div class="form-group-row">
                <label for="login">Ваш логин</label>
                <input type="text" id="login" name="login" class="custom-input" placeholder="Введите логин" required>
            </div>

            <div class="form-group-row">
                <label for="password">Ваш пароль</label>
                <input type="password" id="password" name="password" class="custom-input" placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="button-primary">Войти в систему</button>
        </form>

        <div class="auth-links">
            <p><a href="/forgot_password.php">Забыли пароль?</a></p>
            <p>Ещё нет аккаунта? <a href="/registration.php">Зарегистрироваться</a></p>
        </div>
    </div>
</div>

<script>
    // Принудительно останавливаем музыку при попадании на страницу входа
    if (window.Player) {
        window.Player.stopAndReset();
    } else {
        sessionStorage.removeItem('player_state');
        sessionStorage.removeItem('is_navigating');
    }
</script>

<?php require_once 'templates/footer.php'; ?>
