<?php
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

$trackId = $_GET['id'] ?? null;
if (!$trackId) {
    die("ID трека не указан.");
}

$stmt = $pdo->prepare("SELECT id, title, user_id FROM tracks WHERE id = ?");
$stmt->execute([$trackId]);
$track = $stmt->fetch();

if (!$track) {
    die("Трек с таким ID не найден.");
}
?>

<h1>Редактирование трека</h1>

<form class="admin-form" action="/api/admin/update_track.php" method="POST">
    <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
    <input type="hidden" name="user_id" value="<?php echo $track['user_id']; // Для редиректа ?>">

    <div class="form-group">
        <label for="title">Название трека:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($track['title']); ?>" required>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-save">Сохранить</button>
        <a href="edit_user.php?id=<?php echo $track['user_id']; ?>" class="btn btn-cancel">Отмена</a>
    </div>
</form>

<?php require_once 'templates/footer.php'; ?>