<?php
session_start();
require_once 'core/auth.php';
require_once 'templates/header.php';

$status = $_GET['status'] ?? 'fail';

// Если оплата успешна, запускаем процесс выхода
if ($status === 'success' && isUserLoggedIn()) {
    // Выполняем выход на стороне сервера, чтобы сессия точно завершилась
    logoutUser();
}
?>

<div class="container" style="text-align: center; padding: 4rem 15px;">
    <?php if ($status === 'success'): ?>
        <h1>✅ Оплата прошла успешно!</h1>
        <p>Спасибо за вашу покупку! PRO-статус был успешно начислен на ваш аккаунт.</p>
        <p>Для того чтобы все изменения вступили в силу, необходимо повторно войти в систему.</p>
        <!-- ===== НАЧАЛО ИЗМЕНЕНИЯ: ИЗМЕНЕН ID ЭЛЕМЕНТА ===== -->
        <p>Вы будете автоматически перенаправлены на страницу входа через <span id="logout-countdown">5</span> секунд.</p>
        <!-- ===== КОНЕЦ ИЗМЕНЕНИЯ ===== -->
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let seconds = 5;
                // ===== НАЧАЛО ИЗМЕНЕНИЯ: ИСПОЛЬЗУЕМ НОВЫЙ, УНИКАЛЬНЫЙ ID =====
                const timerElement = document.getElementById('logout-countdown');
                // ===== КОНЕЦ ИЗМЕНЕНИЯ =====

                const countdownInterval = setInterval(function() {
                    seconds--;
                    if (timerElement) {
                        timerElement.textContent = seconds;
                    }

                    if (seconds <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = '/login.php';
                    }
                }, 1000);
            });
        </script>

    <?php else: ?>
        <h1>❌ Оплата не удалась</h1>
        <p>К сожалению, во время оплаты произошла ошибка или вы отменили платеж.</p>
        <p>Вы можете попробовать снова со страницы <a href="/profile.php">вашего профиля</a>.</p>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer.php';
?>