<?php
// Файл: /registration.php (Baseline v2.7 - Исправлена ошибка 500)
session_start();
require_once 'core/db_connect.php'; // ОБЯЗАТЕЛЬНО: Подключаем БД ПЕРЕД auth.php
require_once 'core/auth.php';

// Если пользователь уже залогинен, отправляем в профиль
if (isUserLoggedIn()) {
    header('Location: /profile.php');
    exit();
}

// Получаем источник перехода для реферальной системы
$ref_source = $_SERVER['HTTP_REFERER'] ?? null;

$pageTitle = 'Регистрация';
require_once 'templates/header.php';
?>

<link rel="stylesheet" href="/assets/css/forms.css">

<div class="container">
    <div class="auth-card">
        <h1>Регистрация</h1>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert error" style="margin-bottom: 20px;">
                <?php 
                    if($_GET['error'] == 'user_exists') echo 'Пользователь с таким логином или Email уже существует.';
                    else echo 'Ошибка при регистрации. Проверьте правильность заполнения полей.';
                ?>
            </div>
        <?php endif; ?>

        <form action="/api/register_user.php" method="POST" id="registration-form">
            <?php if ($ref_source): ?>
                <input type="hidden" name="ref_source" value="<?php echo htmlspecialchars($ref_source); ?>">
            <?php endif; ?>

            <div class="form-group-row">
                <label for="login">Ваш логин</label>
                <input type="text" id="login" name="login" class="custom-input" placeholder="Введите логин" required minlength="4" maxlength="50" pattern="^[a-zA-Z0-9_]+$">
                <small style="color:#555; font-size:0.75rem; display:block; margin-top:5px;">Только латинские буквы, цифры и нижнее подчеркивание</small>
            </div>

            <div class="form-group-row">
                <label for="email">Электронная почта</label>
                <input type="email" id="email" name="email" class="custom-input" placeholder="example@mail.com" required>
            </div>

            <div class="form-group-row">
                <label for="password">Придумайте пароль</label>
                <input type="password" id="password" name="password" class="custom-input" placeholder="Минимум 6 символов" required minlength="6">
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="terms_agree" name="terms_agree" value="1" required>
                <label for="terms_agree">Я принимаю условия <a href="/terms.php" target="_blank">Пользовательского соглашения</a></label>
            </div>

            <button type="submit" class="button-primary" id="register-button" disabled>Создать аккаунт</button>
            
            <p style="text-align:center; margin-top:25px; color:#888; font-size:0.9rem;">
                Уже зарегистрированы? <a href="/login.php" style="color:var(--accent-color); font-weight:bold; text-decoration:none;">Войти в профиль</a>
            </p>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const termsCheckbox = document.getElementById('terms_agree');
    const registerButton = document.getElementById('register-button');

    if (termsCheckbox && registerButton) {
        termsCheckbox.addEventListener('change', function() {
            registerButton.disabled = !this.checked;
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
