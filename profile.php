<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit();
}

$currentUser = getCurrentUser();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$userData = $stmt->fetch();

// --- Новая логика для определения, можно ли донатить ---
$canDonate = false;
$activeLyricContest = null;
try {
    // Ищем последний конкурс текстов со статусом "Итоги"
    $stmt_lyrics = $pdo->prepare("SELECT id, name FROM lyric_contests WHERE status = 'results' ORDER BY id DESC LIMIT 1");
    $stmt_lyrics->execute();
    $activeLyricContest = $stmt_lyrics->fetch();

    if ($activeLyricContest) {
        $canDonate = true;
    }
} catch (\PDOException $e) { /* Игнорируем ошибку */ }

require_once 'templates/header.php'; 
?>

<h1>Личный кабинет</h1>

<div class="profile-avatar-container">
    <div class="avatar-frame">
        <?php if (!empty($userData['avatar_filename'])): ?>
            <img src="/uploads/avatars/<?php echo htmlspecialchars($userData['avatar_filename']); ?>" alt="Аватар">
        <?php else: ?>
            <svg class="default-avatar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
        <?php endif; ?>
    </div>
    <form action="/api/upload_avatar.php" method="POST" enctype="multipart/form-data">
        <label for="avatar_file" class="button-primary-styled">Загрузить аватар</label>
        <input type="file" id="avatar_file" name="avatar_file" accept="image/*" onchange="this.form.submit()">
    </form>
    <small>Изображение будет обрезано до квадрата 190x190</small>
</div>

<?php 
if (isset($_GET['avatar']) && $_GET['avatar'] === 'success') echo '<div class="alert success">Аватар успешно обновлен!</div>';
if (isset($_GET['error'])) {
    $errorMsg = 'Произошла ошибка при загрузке аватара.';
    if ($_GET['error'] === 'too_large') $errorMsg = 'Ошибка: Файл слишком большой (макс. 5 МБ).';
    if ($_GET['error'] === 'invalid_type') $errorMsg = 'Ошибка: Недопустимый тип файла (разрешены JPG, PNG, GIF).';
    echo '<div class="alert error">' . $errorMsg . '</div>';
}
?>

<div class="profile-info text-center">
    <h3>Ваши данные</h3>
    <p><strong>Логин:</strong> <?php echo htmlspecialchars($userData['login']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
    <p><strong>Уровень доступа:</strong> <?php echo htmlspecialchars($userData['access_level']); ?></p>
    <p><strong>Дата регистрации:</strong> <?php echo date('d.m.Y', strtotime($userData['created_at'])); ?></p>
</div>

<?php if (in_array($currentUser['access_level'], ['full', 'admin'])): ?>
<div class="donation-section">
    <h3>Поддержать конкурс</h3>
    <?php if ($canDonate): ?>
        <p>Сейчас идет сбор средств в призовой фонд для следующего конкурса треков.</p>
        
        <?php if(isset($_GET['donation']) && $_GET['donation'] === 'success') echo '<div class="alert success">Спасибо! Ваш донат отправлен на проверку администратору.</div>'; ?>

        <form class="form-styled" action="/api/make_donation.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="lyric_contest_id" value="<?php echo $activeLyricContest['id']; ?>">
            <div class="form-group"><label for="amount">Сумма пожертвования:</label><input type="number" id="amount" name="amount" min="1" step="0.01" required placeholder="Например, 100.00"></div>
            <div class="form-group"><label for="receipt_file">Скриншот/чек (PNG, JPG):</label><input type="file" id="receipt_file" name="receipt_file" accept="image/png, image/jpeg" required></div>
            <button type="submit" class="button-primary">Пожертвовать</button>
        </form>
    <?php else: ?>
        <p>Сбор пожертвований начнется после подведения итогов конкурса текстов.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="password-change-form">
    <h3>Смена пароля</h3>
    <?php 
    if (isset($_GET['status']) && $_GET['status'] === 'success') echo '<div class="alert success">Пароль успешно изменен!</div>';
    if (isset($_GET['error'])) {
        $errorMsg = 'Произошла ошибка.';
        if ($_GET['error'] === 'wrongpass') $errorMsg = 'Текущий пароль введен неверно.';
        if ($_GET['error'] === 'mismatch') $errorMsg = 'Новый пароль и его подтверждение не совпадают.';
        if ($_GET['error'] === 'short') $errorMsg = 'Новый пароль слишком короткий (минимум 6 символов).';
        echo '<div class="alert error">' . $errorMsg . '</div>';
    }
    ?>
    <form class="form-styled" action="/api/change_password.php" method="POST">
        <div class="form-group"><label for="current_password">Текущий пароль:</label><input type="password" id="current_password" name="current_password" required></div>
        <div class="form-group"><label for="new_password">Новый пароль:</label><input type="password" id="new_password" name="new_password" required minlength="6"><small>Минимум 6 символов.</small></div>
        <div class="form-group"><label for="confirm_password">Подтвердите новый пароль:</label><input type="password" id="confirm_password" name="confirm_password" required></div>
        <button type="submit" class="button-primary">Изменить пароль</button>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>

