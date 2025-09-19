<?php 
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

$contests = $pdo->query("SELECT * FROM track_contests ORDER BY id DESC")->fetchAll();
?>

<h1>Управление Конкурсами Треков</h1>

<div class="admin-form-container">
    <h3>Создать новый конкурс треков</h3>
    <form class="admin-form" action="/api/admin/create_track_contest.php" method="POST">
        <div class="form-group"><label for="name">Название конкурса:</label><input type="text" id="name" name="name" placeholder="Например, Зимний марафон 2025" required></div>
        <div class="form-group"><label for="submission_start">Начало приёма работ:</label><input type="datetime-local" id="submission_start" name="submission_start" required></div>
        <div class="form-group"><label for="submission_end">Конец приёма работ:</label><input type="datetime-local" id="submission_end" name="submission_end" required></div>
        <div class="form-group"><label for="voting_end">Конец голосования:</label><input type="datetime-local" id="voting_end" name="voting_end" required></div>
        <div class="form-actions"><button type="submit" class="btn btn-save">Создать конкурс</button></div>
    </form>
</div>

<h3>История конкурсов треков</h3>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Статус</th>
            <th>Даты</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contests as $contest): ?>
            <tr>
                <td><?php echo $contest['id']; ?></td>
                <td><?php echo htmlspecialchars($contest['name']); ?></td>
                <td><span class="status-badge status-<?php echo $contest['status']; ?>"><?php echo htmlspecialchars($contest['status']); ?></span></td>
                <td>
                    Приём: <?php echo date('d.m H:i', strtotime($contest['submission_start'])); ?> - <?php echo date('d.m H:i', strtotime($contest['submission_end'])); ?><br>
                    Голосование до: <?php echo date('d.m H:i', strtotime($contest['voting_end'])); ?>
                </td>
                <td>
                    <form class="status-form" action="/api/admin/update_track_contest_status.php" method="POST">
                        <input type="hidden" name="contest_id" value="<?php echo $contest['id']; ?>">
                        <select name="new_status">
                            <option value="pending" <?php if ($contest['status'] === 'pending') echo 'selected'; ?>>Ожидает</option>
                            <option value="submission_active" <?php if ($contest['status'] === 'submission_active') echo 'selected'; ?>>Приём работ</option>
                            <option value="voting_active" <?php if ($contest['status'] === 'voting_active') echo 'selected'; ?>>Голосование</option>
                            <option value="closed" <?php if ($contest['status'] === 'closed') echo 'selected'; ?>>Завершён</option>
                        </select>
                        <button type="submit" class="btn btn-save">OK</button>
                    </form>
                    
                    <?php if ($contest['status'] === 'closed'): ?>
                        <form class="continue-form" action="/api/admin/continue_track_contest.php" method="POST">
                             <input type="hidden" name="contest_id" value="<?php echo $contest['id']; ?>">
                             <label for="new_voting_end_<?php echo $contest['id']; ?>">Новая дата окончания:</label>
                             <input type="datetime-local" id="new_voting_end_<?php echo $contest['id']; ?>" name="new_voting_end" required>
                             <button type="submit" class="btn btn-warning">Продолжить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'templates/footer.php'; ?>