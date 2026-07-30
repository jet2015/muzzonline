<?php
// Файл: /messages.php (Baseline v2.4 - Отправка по Enter + Живой чат)
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit();
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];
$activeConversationId = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);
$conversations = [];
$messages = [];
$partnerData = null;

try {
    $sql_conv = "
        SELECT 
            c.id, c.last_message_at,
            (CASE WHEN c.user1_id = ? THEN u2.id ELSE u1.id END) as partner_id,
            (CASE WHEN c.user1_id = ? THEN u2.login ELSE u1.login END) as partner_login,
            (CASE WHEN c.user1_id = ? THEN u2.avatar_filename ELSE u1.avatar_filename END) as partner_avatar,
            (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.is_read = 0 AND m.sender_id != ?) as unread_count,
            (SELECT message_text FROM messages m WHERE m.conversation_id = c.id ORDER BY m.sent_at DESC LIMIT 1) as last_msg
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.id
        JOIN users u2 ON c.user2_id = u2.id
        WHERE c.user1_id = ? OR c.user2_id = ?
        ORDER BY c.last_message_at DESC
    ";
    $stmt = $pdo->prepare($sql_conv);
    $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
    $conversations = $stmt->fetchAll();

    if ($activeConversationId) {
        $stmt_check = $pdo->prepare("SELECT user1_id, user2_id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
        $stmt_check->execute([$activeConversationId, $userId, $userId]);
        $conv_check = $stmt_check->fetch();
        
        if ($conv_check) {
            $pId = ($conv_check['user1_id'] == $userId) ? $conv_check['user2_id'] : $conv_check['user1_id'];
            $stmt_p = $pdo->prepare("SELECT id, login, avatar_filename FROM users WHERE id = ?");
            $stmt_p->execute([$pId]);
            $partnerData = $stmt_p->fetch();

            $stmt_m = $pdo->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC");
            $stmt_m->execute([$activeConversationId]);
            $messages = $stmt_m->fetchAll();
            
            $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?")->execute([$activeConversationId, $userId]);
        } else {
            $activeConversationId = null;
        }
    }
} catch (\PDOException $e) { /* Error */ }

$pageTitle = 'Сообщения';
require_once 'templates/header.php';
?>

<link rel="stylesheet" href="/assets/css/messenger.css">

<div class="messenger-container" id="messenger-app">
    <aside class="messenger-sidebar <?php echo $activeConversationId ? 'mobile-hidden' : ''; ?>">
        <div class="sidebar-header"><h3>Мои диалоги</h3></div>
        <div class="conversation-list">
            <?php if (empty($conversations)): ?>
                <div class="no-data">У вас пока нет сообщений.</div>
            <?php else: ?>
                <?php foreach ($conversations as $c): ?>
                    <a href="/messages.php?conversation_id=<?php echo $c['id']; ?>" class="conversation-item <?php echo ($c['id'] == $activeConversationId) ? 'active' : ''; ?>">
                        <img src="/uploads/avatars/<?php echo $c['partner_avatar'] ?: 'default.png'; ?>" class="avatar">
                        <div class="conv-info">
                            <div class="conv-top">
                                <span class="partner-name"><?php echo htmlspecialchars($c['partner_login']); ?></span>
                                <?php if($c['unread_count'] > 0): ?><span class="unread-badge"><?php echo $c['unread_count']; ?></span><?php endif; ?>
                            </div>
                            <div class="last-preview"><?php echo htmlspecialchars(mb_substr($c['last_msg'] ?? '', 0, 30)) . '...'; ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <main class="messenger-chat <?php echo !$activeConversationId ? 'mobile-hidden' : ''; ?>">
        <?php if ($activeConversationId && $partnerData): ?>
            <div class="chat-header">
                <a href="/messages.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
                <img src="/uploads/avatars/<?php echo $partnerData['avatar_filename'] ?: 'default.png'; ?>" class="avatar">
                <h4><?php echo htmlspecialchars($partnerData['login']); ?></h4>
            </div>
            
            <div class="chat-messages" id="chat-window">
                <?php foreach($messages as $m): ?>
                    <div class="message-bubble <?php echo ($m['sender_id'] == $userId) ? 'sent' : 'received'; ?>">
                        <div class="message-content"><?php echo nl2br(htmlspecialchars($m['message_text'])); ?></div>
                        <div class="message-time"><?php echo date('H:i', strtotime($m['sent_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-input-area">
                <form id="chat-form">
                    <input type="hidden" name="conversation_id" value="<?php echo $activeConversationId; ?>">
                    <textarea id="chat-textarea" name="message_text" placeholder="Напишите сообщение... (Enter для отправки)" required></textarea>
                    <button type="submit" class="button-primary">Отправить</button>
                </form>
            </div>
        <?php else: ?>
            <div class="no-chat-selected">
                <i class="fas fa-comments"></i>
                <p>Выберите диалог, чтобы начать общение.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWindow = document.getElementById('chat-window');
    const chatForm = document.getElementById('chat-form');
    const chatTextarea = document.getElementById('chat-textarea');
    
    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;

    // ОТПРАВКА ПО ENTER
    if (chatTextarea) {
        chatTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Запрещаем перенос строки
                chatForm.dispatchEvent(new Event('submit')); // Триггерим отправку формы
            }
        });
    }

    if (chatForm) {
        chatForm.onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            const msgText = fd.get('message_text').trim();
            if (!msgText) return;

            fetch('/api/send_message.php', { method: 'POST', body: fd })
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        this.querySelector('textarea').value = '';
                        refreshMessages();
                    }
                });
        };
    }

    function refreshMessages() {
        if (!chatWindow) return;
        const cId = "<?php echo $activeConversationId; ?>";
        fetch(`/api/get_messages.php?conversation_id=${cId}`)
            .then(r => r.json()).then(data => {
                if (data.success) {
                    chatWindow.innerHTML = data.html;
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                }
            });
    }

    if ("<?php echo $activeConversationId; ?>") {
        setInterval(refreshMessages, 5000);
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
