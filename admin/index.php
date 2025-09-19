<?php 
// Подключаем шапку, которая содержит всю логику проверки доступа
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php'; // Подключаем БД для статистики

// --- Сбор статистики (без изменений) ---
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalTracks = $pdo->query("SELECT COUNT(*) FROM tracks")->fetchColumn();
    $activeContests = $pdo->query("SELECT COUNT(*) FROM contest_seasons WHERE status IN ('submission_active', 'voting_active')")->fetchColumn();
    $pendingDonations = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'")->fetchColumn();
} catch (\PDOException $e) {
    echo '<div class="alert error">Не удалось загрузить статистику: ' . $e->getMessage() . '</div>';
    $totalUsers = $totalTracks = $activeContests = $pendingDonations = 'N/A';
}
?>

<h1>Добро пожаловать, <?php echo htmlspecialchars(getCurrentUser()['login']); ?>!</h1>

<!-- Карточки со статистикой (без изменений) -->
<div class="stats-container">
    <div class="stat-card">
        <div class="stat-card-icon users">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-number"><?php echo $totalUsers; ?></span>
            <span class="stat-label">Пользователей</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon tracks">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-number"><?php echo $totalTracks; ?></span>
            <span class="stat-label">Треков на сайте</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon contests">
             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 1H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V3c0-1.1-.9-2-2-2zm-1 14H6c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v10c0 .55-.45 1-1 1zM9 12l2.25 3L14 12l3 4H7l2-2.75z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-number"><?php echo $activeContests; ?></span>
            <span class="stat-label">Активных конкурсов</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon donations">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-number"><?php echo $pendingDonations; ?></span>
            <span class="stat-label">Донатов на проверке</span>
        </div>
    </div>
</div>

<!-- --- НОВЫЙ БЛОК: ОПАСНАЯ ЗОНА --- -->
<div class="danger-zone">
    <h2>Опасная зона</h2>
    <p>Это действие необратимо. Будут удалены все конкурсные треки (кроме победителей), все тексты, все голоса, донаты, призы и сезоны. Хит-парад останется нетронутым.</p>
    <form action="/api/admin/reset_contest_data.php" method="POST" onsubmit="return confirm('ВЫ УВЕРЕНЫ, ЧТО ХОТИТЕ ПОЛНОСТЬЮ СБРОСИТЬ ВСЕ КОНКУРСНЫЕ ДАННЫЕ? ЭТО ДЕЙСТВИЕ НЕЛЬЗЯ ОТМЕНИТЬ!');">
        <button type="submit" class="btn btn-danger">Сбросить все данные конкурсов</button>
    </form>
</div>


<?php 
// Подключаем подвал
require_once 'templates/footer.php'; 
?>