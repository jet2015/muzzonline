<?php
// Файл: /shop.php (Baseline v3.47 - Выбор дней для выделения цветом)
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'core/settings.php';

if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit();
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

try {
    $stmt = $pdo->prepare("SELECT points_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentBalance = (int)$stmt->fetchColumn();
} catch (\PDOException $e) { $currentBalance = 0; }

$canPurchase = ($currentBalance >= 100);

$userTracks = [];
try {
    $stmt_tracks = $pdo->prepare("SELECT id, title FROM tracks WHERE user_id = ? AND page_type = 'general' ORDER BY title ASC");
    $stmt_tracks->execute([$userId]);
    $userTracks = $stmt_tracks->fetchAll();
} catch (\PDOException $e) { }

$purchaseHistory = [];
try {
    $sql_history = "
        (SELECT 'boost' as type, b.purchased_at, b.hours, b.cost, t.title as track_title, tr.boosted_until, tr.boost_queue_position 
         FROM purchased_boost_log b 
         JOIN tracks t ON b.track_id = t.id 
         JOIN tracks tr ON b.track_id = tr.id 
         WHERE b.user_id = ? AND b.purchased_at > NOW() - INTERVAL 3 DAY)
        UNION ALL
        (SELECT 'highlight' as type, h.purchased_at, NULL as hours, h.cost, t.title as track_title, tr.highlighted_until as boosted_until, NULL as boost_queue_position 
         FROM purchased_highlight_log h 
         JOIN tracks t ON h.track_id = t.id 
         JOIN tracks tr ON h.track_id = tr.id 
         WHERE h.user_id = ? AND h.purchased_at > NOW() - INTERVAL 3 DAY)
        UNION ALL
        (SELECT 'vote' as type, v.purchased_at, NULL as hours, NULL as cost, t.title as track_title, NULL as boosted_until, NULL as boost_queue_position 
         FROM purchased_votes_log v 
         JOIN tracks t ON v.track_id = t.id 
         WHERE v.user_id = ? AND v.purchased_at > NOW() - INTERVAL 3 DAY)
        ORDER BY purchased_at DESC
    ";
    $stmt_history = $pdo->prepare($sql_history);
    $stmt_history->execute([$userId, $userId, $userId]);
    $purchaseHistory = $stmt_history->fetchAll();
} catch (\PDOException $e) { }

$priceBoost = get_setting('price_boost_track', 5000);
$maxHoursBoost = get_setting('max_hours_boost_track', 12);
$priceHighlight = get_setting('price_highlight_track', 3000);
$maxDaysHighlight = get_setting('max_days_highlight_track', 30);
$priceVote = get_setting('price_buy_vote', 100);

$pageTitle = 'Магазин услуг';
require_once 'templates/header.php';
?>

<link rel="stylesheet" href="/assets/css/forms.css">
<link rel="stylesheet" href="/assets/css/shop.css">

<div class="container">
    <h1 style="text-align: center; color: #fff;">Магазин услуг</h1>
    <p class="centered-text" style="color: #fff;">Ваш баланс: <strong><?php echo number_format($currentBalance, 0, '.', ' '); ?></strong> баллов.</p>
    
    <?php if (!$canPurchase): ?>
        <div class="alert error" style="margin-bottom: 20px;">Внимание! Для совершения покупок необходимо иметь на балансе не менее 100 баллов.</div>
    <?php endif; ?>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert <?php echo $_GET['status'] === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($_GET['message'] ?? ($_GET['status'] === 'success' ? 'Услуга успешно приобретена!' : 'Произошла ошибка.')); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($userTracks)): ?>
        <div class="contest-placeholder" style="text-align:center; padding: 2rem; color:#fff;">
            <p>У вас пока нет треков в Хит-параде. Сначала <a href="/hit_parade.php" style="color:var(--accent-color);">загрузите трек</a>.</p>
        </div>
    <?php else: ?>
        <div class="shop-grid">
            <!-- Поднять в топ -->
            <div class="shop-item">
                <h3>🚀 В топ Хит-парада</h3>
                <p class="shop-item-desc">Ваш трек будет закреплен на первом месте в Хит-параде.</p>
                <form action="/api/purchase_service.php" method="POST" class="shop-form">
                    <input type="hidden" name="service_key" value="boost_track">
                    <div class="form-group-row">
                        <label>Выберите свой трек</label>
                        <select name="track_id" class="select2-searchable custom-input" <?php if(!$canPurchase) echo 'disabled'; ?>>
                            <option value="">Название трека...</option>
                            <?php foreach($userTracks as $track): ?>
                                <option value="<?php echo $track['id']; ?>"><?php echo htmlspecialchars($track['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-row">
                        <label>Количество часов</label>
                        <select id="boost_hours" name="hours" data-price-per-hour="<?php echo $priceBoost; ?>" class="custom-input" <?php if(!$canPurchase) echo 'disabled'; ?>>
                            <?php for ($i = 1; $i <= $maxHoursBoost; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> час(а)</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="button-primary" <?php if(!$canPurchase) echo 'disabled'; ?>>КУПИТЬ ЗА <span class="price-display-boost"><?php echo number_format($priceBoost, 0, '', ' '); ?></span> б.</button>
                </form>
            </div>

            <!-- Выделить цветом -->
            <div class="shop-item">
                <h3>🌟 Выделить цветом</h3>
                <p class="shop-item-desc">Ваш трек будет подсвечен в списке Хит-парада на выбранный срок.</p>
                <form action="/api/purchase_service.php" method="POST" class="shop-form">
                    <input type="hidden" name="service_key" value="highlight_track">
                    <div class="form-group-row">
                        <label>Выберите свой трек</label>
                        <select name="track_id" class="select2-searchable custom-input" <?php if(!$canPurchase) echo 'disabled'; ?>>
                            <option value="">Название трека...</option>
                            <?php foreach($userTracks as $track): ?>
                                <option value="<?php echo $track['id']; ?>"><?php echo htmlspecialchars($track['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-row">
                        <label>Количество дней</label>
                        <select id="highlight_days" name="days" data-price-per-day="<?php echo $priceHighlight; ?>" class="custom-input" <?php if(!$canPurchase) echo 'disabled'; ?>>
                            <?php for ($i = 1; $i <= $maxDaysHighlight; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> день/дней</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="button-primary" <?php if(!$canPurchase) echo 'disabled'; ?>>КУПИТЬ ЗА <span class="price-display-highlight"><?php echo number_format($priceHighlight, 0, '', ' '); ?></span> б.</button>
                </form>
            </div>

            <!-- Купить голос -->
            <div class="shop-item">
                <h3>❤️ Дополнительный голос</h3>
                <p class="shop-item-desc">Мгновенно добавьте один голос к рейтингу трека.</p>
                <form action="/api/purchase_service.php" method="POST" class="shop-form">
                    <input type="hidden" name="service_key" value="buy_vote">
                    <div class="form-group-row">
                        <label>Выберите свой трек</label>
                        <select name="track_id" class="select2-searchable custom-input" <?php if(!$canPurchase) echo 'disabled'; ?>>
                            <option value="">Название трека...</option>
                            <?php foreach($userTracks as $track): ?>
                                <option value="<?php echo $track['id']; ?>"><?php echo htmlspecialchars($track['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="button-primary" <?php if(!$canPurchase) echo 'disabled'; ?>>КУПИТЬ ЗА <?php echo number_format($priceVote, 0, '', ' '); ?> б.</button>
                </form>
            </div>
        </div>
        
        <!-- История -->
        <div class="purchase-history-section">
            <h2>История услуг и очередей (за 3 суток)</h2>
            <div class="history-scroll-box-shop">
                <?php if (empty($purchaseHistory)): ?>
                    <p class="centered-text" style="color:#aaa;">Ваших покупок за последние 3 дня не найдено.</p>
                <?php else: ?>
                    <table class="shop-history-table">
                        <thead><tr><th>Услуга</th><th>Трек</th><th>Статус / Детали</th><th>Дата</th></tr></thead>
                        <tbody>
                            <?php foreach($purchaseHistory as $item): ?>
                                <tr>
                                    <td><?php if ($item['type'] === 'boost') echo '🚀 Топ'; elseif ($item['type'] === 'highlight') echo '🌟 Цвет'; else echo '❤️ Голос'; ?></td>
                                    <td><?php echo htmlspecialchars($item['track_title']); ?></td>
                                    <td><?php if ($item['type'] === 'boost') { echo $item['boost_queue_position'] ? "В очереди (№{$item['boost_queue_position']})" : (strtotime($item['boosted_until']) > time() ? "До: ".date('d.m H:i', strtotime($item['boosted_until'])) : "Завершен"); } elseif ($item['type'] === 'highlight') { echo strtotime($item['boosted_until']) > time() ? "До: ".date('d.m H:i', strtotime($item['boosted_until'])) : "Завершен"; } else echo "+1 голос"; ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($item['purchased_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
        $(document).ready(function() {
            $('.select2-searchable').select2({ width: '100%', placeholder: "Выберите трек..." });
            
            // Расчет для ТОПа
            $('#boost_hours').on('change', function() {
                const p = parseInt($(this).data('price-per-hour'));
                const h = parseInt($(this).val());
                $(this).closest('form').find('.price-display-boost').text((p * h).toLocaleString('ru-RU'));
            });

            // Расчет для Цвета
            $('#highlight_days').on('change', function() {
                const p = parseInt($(this).data('price-per-day'));
                const d = parseInt($(this).val());
                $(this).closest('form').find('.price-display-highlight').text((p * d).toLocaleString('ru-RU'));
            });
        });
        </script>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
