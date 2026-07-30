<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
require_once 'core/settings.php';

// Проверяем, что пользователь авторизован
if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit();
}

// Получаем данные из формы в профиле
$durationMonths = (int)($_POST['duration_months'] ?? 1);
if ($durationMonths < 1 || $durationMonths > 12) {
    // Если кто-то пытается подделать количество месяцев, сбрасываем до 1
    $durationMonths = 1;
}

// Получаем цену из настроек
$pricePerMonth = (float)get_setting('pro_price_per_month', 150);
$totalPrice = $pricePerMonth * $durationMonths;

require_once 'templates/header.php';
?>

<h1>Подтверждение покупки PRO-статуса</h1>

<div class="purchase-summary">
    <p>Вы собираетесь приобрести премиум-статус для вашего аккаунта.</p>
    
    <div class="summary-details">
        <div class="summary-item">
            <span>Срок подписки:</span>
            <strong><?php echo $durationMonths; ?> месяц(ев)</strong>
        </div>
        <div class="summary-item total">
            <span>Итоговая стоимость:</span>
            <strong><?php echo number_format($totalPrice, 2, '.', ' '); ?> руб.</strong>
        </div>
    </div>

    <div class="pro-benefits">
        <h4>Что вы получите с PRO-статусом:</h4>
        <ul>
            <li>👑 Значок PRO рядом с вашим ником.</li>
            <li>💰 Увеличенные ежедневные и еженедельные бонусы баллов.</li>
            <li>🚀 Увеличенные лимиты на участие в конкурсах.</li>
            <li>🌟 Эксклюзивные скидки в магазине услуг.</li>
            <li>✨ Особое оформление в списках и комментариях.</li>
        </ul>
    </div>
    
    <!-- В будущем здесь будет форма для перехода на ЮMoney -->
    <div class="payment-gateway-form">
        <form action="/api/initiate_yoomoney_payment.php" method="POST">
            <input type="hidden" name="amount" value="<?php echo $totalPrice; ?>">
            <input type="hidden" name="duration" value="<?php echo $durationMonths; ?>">
            <button type="submit" class="button-primary large-button">Перейти к оплате (ЮMoney)</button>
        </form>
    </div>
    
    <p class="small-text">Нажимая на кнопку, вы будете перенаправлены на сайт платежной системы ЮMoney для безопасного совершения платежа. После успешной оплаты PRO-статус будет активирован автоматически.</p>
</div>

<style>
.purchase-summary {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: var(--surface-color);
    border: 1px solid #282828;
    border-radius: 8px;
    text-align: center;
}
.summary-details {
    margin: 2rem 0;
    padding: 1.5rem;
    background-color: var(--bg-color);
    border-radius: 8px;
}
.summary-item {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
    padding: 0.8rem 0;
}
.summary-item:not(:last-child) {
    border-bottom: 1px solid #282828;
}
.summary-item.total strong {
    font-size: 1.5rem;
    color: var(--accent-color);
}
.pro-benefits {
    text-align: left;
    margin: 2rem 0;
}
.pro-benefits h4 {
    text-align: center;
}
.pro-benefits ul {
    list-style: none;
    padding: 0;
}
.pro-benefits li {
    padding: 5px 0;
}
.payment-gateway-form .large-button {
    font-size: 1.3rem;
    padding: 1rem;
}
.small-text {
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: var(--secondary-text-color);
}
</style>

<?php require_once 'templates/footer.php'; ?>