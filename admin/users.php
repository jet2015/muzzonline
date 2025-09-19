<?php 
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

// Логика поиска
$searchQuery = $_GET['search'] ?? '';
$sqlParams = [];

$sql = "SELECT id, login, email, access_level, created_at FROM users";
if (!empty($searchQuery)) {
    $sql .= " WHERE login LIKE ?";
    $sqlParams[] = '%' . $searchQuery . '%';
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($sqlParams);
$users = $stmt->fetchAll();
?>

<h1>Управление пользователями</h1>

<div class="search-form">
    <form method="GET" action="users.php">
        <input type="text" name="search" placeholder="Поиск по логину..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit">Найти</button>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Email</th>
            <th>Уровень доступа</th>
            <th>Дата регистрации</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="6">Пользователи не найдены.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['login']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['access_level']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">Редактировать</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>


<?php require_once 'templates/footer.php'; ?>