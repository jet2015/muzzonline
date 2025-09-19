<?php
require_once 'templates/header.php';
require_once __DIR__ . '/../core/db_connect.php';

$lyricId = $_GET['id'] ?? null;
if (!$lyricId) {
    die("ID текста не указан.");
}

$stmt = $pdo->prepare("SELECT id, title, content, user_id FROM lyrics WHERE id = ?");
$stmt->execute([$lyricId]);
$lyric = $stmt->fetch();

if (!$lyric) {
    die("Текст с таким ID не найден.");
}
?>

<h1>Редактирование текста</h1>

<form class="admin-form" action="/api/admin/update_lyric.php" method="POST">
    <input type="hidden" name="lyric_id" value="<?php echo $lyric['id']; ?>">
    <input type="hidden" name="user_id" value="<?php echo $lyric['user_id']; // Для редиректа ?>">

    <div class="form-group">
        <label for="title">Название:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($lyric['title']); ?>" required>
    </div>

    <div class="form-group">
        <label for="content">Текст:</label>
        <textarea name="content" id="content" rows="20" required><?php echo htmlspecialchars($lyric['content']); ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-save">Сохранить</button>
        <a href="edit_user.php?id=<?php echo $lyric['user_id']; ?>" class="btn btn-cancel">Отмена</a>
    </div>
</form>

<?php require_once 'templates/footer.php'; ?>