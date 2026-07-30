<?php
// Файл: /profile.php (Baseline v3.19 - Исправлена реферальная ссылка)
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'core/settings.php';

$currentUser = getCurrentUser();
$profileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: ($currentUser['id'] ?? null);

if (!$profileId) { header('Location: /login.php'); exit(); }

$isMyProfile = ($currentUser && $currentUser['id'] == $profileId);

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$profileId]);
    $userData = $stmt->fetch();
} catch (\PDOException $e) { $userData = null; }

if (!$userData) { die("Пользователь не найден."); }

// Получаем название проекта из настроек
$projectName = function_exists('get_setting') ? get_setting('video_watermark_text', 'Realist-Music') : 'Realist-Music';

// Генерируем ссылку динамически по текущему домену
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$refLink = $protocol . $_SERVER['HTTP_HOST'] . '/registration.php?ref=' . $userData['id'];

$pageTitle = $isMyProfile ? 'Личный кабинет' : 'Профиль ' . htmlspecialchars($userData['login']);
require_once 'templates/header.php';

$userWinnings = [];
try {
    $sql_w = "SELECT w.*, t.title as track_title, tc.name as contest_name FROM winnings w JOIN tracks t ON w.track_id = t.id JOIN track_contests tc ON w.track_contest_id = tc.id WHERE w.user_id = ? ORDER BY w.id DESC";
    $stmt_w = $pdo->prepare($sql_w);
    $stmt_w->execute([$profileId]);
    $userWinnings = $stmt_w->fetchAll();
} catch (\PDOException $e) { }

$purchaseHistory = [];
if ($isMyProfile) {
    try {
        $uId = $currentUser['id'];
        $sql_h = "(SELECT 'boost' as type, purchased_at, cost, 'В топ' as label FROM purchased_boost_log WHERE user_id = ?) 
                  UNION ALL (SELECT 'highlight' as type, purchased_at, cost, 'Цвет' as label FROM purchased_highlight_log WHERE user_id = ?) 
                  UNION ALL (SELECT 'vote' as type, purchased_at, 100 as cost, 'Голос' as label FROM purchased_votes_log WHERE user_id = ?) 
                  ORDER BY purchased_at DESC LIMIT 10";
        $stmt_h = $pdo->prepare($sql_h);
        $stmt_h->execute([$uId, $uId, $uId]);
        $purchaseHistory = $stmt_h->fetchAll();
    } catch (\PDOException $e) { }
}

$referrals = [];
if ($isMyProfile) {
    $stmt_ref = $pdo->prepare("SELECT login, created_at FROM users WHERE referrer_id = ? ORDER BY created_at DESC");
    $stmt_ref->execute([$profileId]);
    $referrals = $stmt_ref->fetchAll();
}

$bonusRef = (int)get_setting('bonus_referral_referrer', 300);
$bonusFriend = (int)get_setting('bonus_referral_referee', 300);
?>

<link rel="stylesheet" href="/assets/css/profile.css">
<link rel="stylesheet" href="/assets/css/forms.css">

<div class="container">
    <h1 style="text-align:center; margin-bottom:30px;"><?php echo $isMyProfile ? 'Личный кабинет' : 'Профиль пользователя'; ?></h1>

    <?php if(isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert success" style="margin-bottom:20px;">Данные успешно обновлены!</div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="profile-left">
            <div class="profile-avatar-container">
                <div class="avatar-frame">
                    <?php if (!empty($userData['avatar_filename'])): ?>
                        <img src="/uploads/avatars/<?php echo htmlspecialchars($userData['avatar_filename']); ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="default-avatar-icon"><i class="fas fa-user-circle"></i></div>
                    <?php endif; ?>
                </div>
                <?php if ($isMyProfile): ?>
                    <form action="/api/upload_avatar.php" method="POST" enctype="multipart/form-data">
                        <label for="avatar_file" class="button-primary-styled">Загрузить аватар</label>
                        <input type="file" id="avatar_file" name="avatar_file" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </form>
                <?php else: ?>
                    <a href="/api/start_conversation.php?partner_id=<?php echo $userData['id']; ?>" class="button-primary-styled" style="text-decoration:none;">Написать сообщение</a>
                <?php endif; ?>
            </div>

            <?php if (isAdmin()): ?>
                <div class="profile-section-box" style="text-align:center; margin-bottom:20px;">
                    <a href="/admin/" target="_blank" class="button-primary" style="background:#dc3545; color:#fff;">АДМИН-ПАНЕЛЬ</a>
                </div>
            <?php endif; ?>

            <div class="profile-section-box" style="text-align:center;">
                <h3 style="border-bottom:none;">Ваши данные</h3>
                <div style="font-size:1.1rem; line-height:2.2; margin-top:10px;">
                    <div><strong>Логин:</strong> <?php echo htmlspecialchars($userData['login']); ?> <?php if($userData['is_pro']): ?><span style="color:gold;">👑</span><?php endif; ?></div>
                    <div><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></div>
                    <div><strong>Уровень доступа:</strong> <?php echo strtoupper($userData['access_level']); ?></div>
                    <div><strong>Дата регистрации:</strong> <?php echo date('d.m.Y', strtotime($userData['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="profile-right">
            <?php if (!empty($userWinnings)): ?>
                <div class="profile-section-box">
                    <h3>🏆 Достижения</h3>
                    <div class="awards-grid">
                        <?php foreach($userWinnings as $w): ?>
                            <div class="award-card">
                                <div class="award-icon"><?php echo ($w['place']==1?'🥇':($w['place']==2?'🥈':'🥉')); ?></div>
                                <div class="award-details">
                                    <span class="award-title"><?php echo $w['place']; ?> место: <?php echo htmlspecialchars($w['track_title']); ?></span>
                                    <small style="color:#666;"><?php echo htmlspecialchars($w['contest_name']); ?></small>
                                    <div class="award-amount">+<?php echo number_format($w['amount'], 0, '.', ' '); ?> руб.</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isMyProfile): ?>
                <div class="profile-section-box">
                    <h3><i class="fas fa-lock"></i> Смена пароля</h3>
                    <form action="/api/change_password.php" method="POST">
                        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 200px;">
                                <label style="color:#888; font-size:0.8rem; margin-bottom:5px; display:block;">Текущий пароль</label>
                                <input type="password" name="current_password" class="custom-input" required>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <label style="color:#888; font-size:0.8rem; margin-bottom:5px; display:block;">Новый пароль</label>
                                <input type="password" name="new_password" class="custom-input" minlength="6" required>
                            </div>
                            <button type="submit" class="button-primary" style="width: auto; padding: 12px 25px;">Сменить</button>
                        </div>
                    </form>
                </div>

                <div class="profile-section-box">
                    <h3>🤝 Партнерская программа</h3>
                    <p style="color:#aaa; line-height:1.6; font-size: 0.95rem;">
                        🔥 Приглашайте друзей в <b><?php echo htmlspecialchars($projectName); ?></b> и зарабатывайте вместе! 
                        За каждого приведенного пользователя, который зарегистрируется по вашей ссылке, 
                        вы получите <b><?php echo $bonusRef; ?> баллов</b>, а ваш друг — <b><?php echo $bonusFriend; ?> баллов</b> в качестве приветственного бонуса! 🚀
                        Развивайте свою музыкальную сеть, копите баллы и продвигайте свои треки в ТОП Хит-парада быстрее остальных. 
                        🎵 Творчество — это командная игра, приводите единомышленников и стройте комьюнити вместе с нами! 💸
                    </p>
                    
                    <div class="form-group-row" style="margin-top:20px;">
                        <label>Ваша уникальная реферальная ссылка:</label>
                        <input type="text" id="ref-link" class="custom-input" value="<?php echo $refLink; ?>" readonly>
                        <button class="button-primary" style="margin-top:10px;" onclick="copyRef()">Скопировать ссылку</button>
                    </div>

                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <a href="https://clck.ru/" target="_blank" class="share-btn">Clck.ru</a>
                        <a href="https://vk.cc/" target="_blank" class="share-btn">VK.cc</a>
                        <a href="https://bitly.com/" target="_blank" class="share-btn">Bitly</a>
                    </div>

                    <h4 style="color:#fff; margin-top:20px;">Ваши приглашенные рефералы:</h4>
                    <div class="history-table-wrapper" style="max-height: 250px; overflow-y: auto;">
                        <table class="admin-table">
                            <thead><tr><th>Логин</th><th>Дата регистрации</th></tr></thead>
                            <tbody>
                                <?php if(empty($referrals)): ?><tr><td colspan="2" style="text-align:center;">Пока нет приглашенных.</td></tr><?php else: ?>
                                <?php foreach($referrals as $r): ?>
                                    <tr><td><?php echo htmlspecialchars($r['login']); ?></td><td><?php echo date('d.m.Y', strtotime($r['created_at'])); ?></td></tr>
                                <?php endforeach; ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyRef() {
    const el = document.getElementById('ref-link');
    el.select(); document.execCommand('copy'); alert('Реферальная ссылка скопирована!');
}
</script>

<?php require_once 'templates/footer.php'; ?>