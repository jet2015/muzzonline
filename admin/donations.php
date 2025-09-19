<?php 
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

// Получаем все донаты с информацией о пользователе и сезоне
$sql = "SELECT d.*, u.login, s.name as season_name 
        FROM donations d
        JOIN users u ON d.user_id = u.id
        JOIN contest_seasons s ON d.season_id = s.id
        ORDER BY d.id DESC";
$donations = $pdo->query($sql)->fetchAll();
?>

<h1>Управление Донатами</h1>
<p>Здесь вы можете одобрять или отклонять пожертвования в призовой фонд.</p>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Сумма</th>
            <th>Сезон</th>
            <th>Статус</th>
            <th>Дата</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($donations)): ?>
            <tr><td colspan="7">Пожертвований пока не было.</td></tr>
        <?php else: ?>
            <?php foreach ($donations as $donation): ?>
                <tr>
                    <td><?php echo $donation['id']; ?></td>
                    <td><?php echo htmlspecialchars($donation['login']); ?></td>
                    <td><?php echo htmlspecialchars($donation['amount']); ?></td>
                    <td><?php echo htmlspecialchars($donation['season_name']); ?></td>
                    <td><span class="status-badge status-<?php echo $donation['status']; ?>"><?php echo $donation['status']; ?></span></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($donation['created_at'])); ?></td>
                    <td>
                        <?php if ($donation['status'] === 'pending'): ?>
                            <form action="/api/admin/process_donation.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-approve">Одобрить</button>
                            </form>
                            <form action="/api/admin/process_donation.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-reject">Отклонить</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'templates/footer.php'; ?>