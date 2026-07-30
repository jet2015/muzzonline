<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';
// =====================================================================
// НАЧАЛО ИЗМЕНЕНИЯ: ОПРЕДЕЛЯЕМ SEO-ДАННЫЕ ДЛЯ СТРАНИЦЫ
// =====================================================================
$pageTitle = 'Хит-парад';
$pageDescription = 'Слушайте и голосуйте за лучшие треки независимых исполнителей. Самые популярные песни недели в нашем Хит-параде.';
$pageKeywords = 'хит-парад, новая музыка, слушать онлайн, рейтинг песен, топ треков';
// =====================================================================
// КОНЕЦ ИЗМЕНЕНИЯ
// =====================================================================
require_once 'templates/header.php';

// Контроль доступа
$currentUser = getCurrentUser();
$allowed_roles = ['full', 'admin'];
if (!$currentUser || !in_array($currentUser['access_level'], $allowed_roles)) {
    echo '<div class="alert error">Доступ к этому разделу имеют только пользователи с полным доступом.</div>';
    require_once 'templates/footer.php'; 
    exit();
}

// =====================================================================
// НАЧАЛО ИЗМЕНЕНИЯ: ДИНАМИЧЕСКИЙ ЛИМИТ ДЛЯ PRO-ПОЛЬЗОВАТЕЛЕЙ
// =====================================================================
$submissionLimit = isUserPro() ? 4 : 2; // 4 для PRO, 2 для остальных
// =====================================================================
// КОНЕЦ ИЗМЕНЕНИЯ
// =====================================================================

// Логика определения текущей фазы
$activeContest = null;
$lyricsList = [];
$lyricWinner = null;
$userSubmissionCount = 0;
$userHasVotedInContest = false; 

try {
    $stmt = $pdo->prepare("SELECT * FROM lyric_contests WHERE status != 'closed' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $activeContest = $stmt->fetch();

    if ($activeContest) {
        if ($currentUser) {
            $stmt_vote_check = $pdo->prepare("SELECT 1 FROM lyrics_votes v JOIN lyrics l ON v.lyric_id = l.id WHERE v.user_id = ? AND l.lyric_contest_id = ? LIMIT 1");
            $stmt_vote_check->execute([$currentUser['id'], $activeContest['id']]);
            if ($stmt_vote_check->fetch()) {
                $userHasVotedInContest = true;
            }
        }

        if ($activeContest['status'] === 'submission_active' || $activeContest['status'] === 'voting_active') {
            $sql = "SELECT l.id, l.user_id, l.title, l.content, u.login as author, COUNT(v.id) as vote_count
                    FROM lyrics l
                    JOIN users u ON l.user_id = u.id
                    LEFT JOIN lyrics_votes v ON l.id = v.lyric_id
                    WHERE l.lyric_contest_id = ?
                    GROUP BY l.id ORDER BY vote_count DESC, l.id ASC";
            $stmt_lyrics = $pdo->prepare($sql);
            $stmt_lyrics->execute([$activeContest['id']]);
            $lyricsList = $stmt_lyrics->fetchAll();

            foreach ($lyricsList as $lyric) {
                if ($lyric['user_id'] === $currentUser['id']) {
                    $userSubmissionCount++;
                }
            }
        } 
        elseif ($activeContest['status'] === 'results') {
            $sql = "SELECT l.title, l.content, u.login as author FROM lyrics l JOIN users u ON l.user_id = u.id WHERE l.lyric_contest_id = ? AND l.status = 'winner' LIMIT 1";
            $stmt_winner = $pdo->prepare($sql);
            $stmt_winner->execute([$activeContest['id']]);
            $lyricWinner = $stmt_winner->fetch();
        }
    }
} catch (\PDOException $e) { 
    echo '<div class="alert error">Ошибка получения данных о конкурсе.</div>'; 
}

function renderLyricsList($lyrics, $currentUser, $isVotingActive, $userHasVoted) {
    if (empty($lyrics)) {
        echo '<p style="text-align: center;">На конкурс еще не было подано ни одного текста. Станьте первым!</p>';
    } else {
        foreach($lyrics as $lyric) {
            $isAuthor = ($currentUser && $currentUser['id'] === $lyric['user_id']);
            $canVote = $isVotingActive && !$userHasVoted && !$isAuthor;
            
            echo '<div class="lyric-item-form-wrapper" data-id="' . $lyric['id'] . '">';
            echo '<form action="/api/update_lyric_content.php" method="POST">';
            echo '<input type="hidden" name="lyric_id" value="' . $lyric['id'] . '">';
            echo '<div class="lyric-title-header">';
            echo '<h3>' . htmlspecialchars($lyric['title']) . '</h3>';
            echo '<button type="button" class="copy-btn copy-title-btn" title="Копировать название"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>';
            echo '</div>';
            echo '<p class="lyric-author">Автор: Автор скрыт до подведения итогов</p>';
            echo '<div class="form-group lyric-content-wrapper">';
            echo '<textarea name="content" class="lyric-display-textarea" rows="10" ' . ($isAuthor ? '' : 'readonly') . '>' . htmlspecialchars($lyric['content']) . '</textarea>';
            echo '<button type="button" class="copy-btn copy-text-btn" title="Копировать текст"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>';
            echo '</div>';
            echo '<div class="lyric-actions">';
            echo '<div class="save-action">';
            if ($isAuthor && !$isVotingActive) {
                echo '<button type="submit" class="button-primary btn-save-lyric">Сохранить</button>';
            }
            echo '</div>';
            echo '<div class="vote-action">';
            echo '<span class="vote-count">' . $lyric['vote_count'] . '</span>';
            echo '<button type="button" class="vote-button lyric-vote-button" title="Голосовать за текст" ' . ($canVote ? '' : 'disabled') . ' data-user-has-voted="' . ($userHasVoted ? 'true' : 'false') . '">❤</button>';
            echo '</div>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
        }
    }
}
?>

<h1>Конкурс текстов <?php if ($activeContest) echo ' - ' . htmlspecialchars($activeContest['name']); ?>
    <span class="tooltip-icon" data-tooltip="Конкурс текстов проходит в несколько этапов: сначала все желающие подают свои работы, затем открывается голосование. Победитель определяется по наибольшему количеству голосов. Имена авторов скрыты до подведения итогов.">[?]</span>
</h1>

<?php
if (isset($_GET['upload']) && $_GET['upload'] === 'success') echo '<div class="alert success">Ваш текст успешно отправлен на конкурс!</div>';
if (isset($_GET['edit']) && $_GET['edit'] === 'success') echo '<div class="alert success">Ваш текст успешно обновлен!</div>';
if (isset($_GET['error'])) {
    $errorMsg = 'Произошла ошибка.';
    if ($_GET['error'] === 'limit_reached') $errorMsg = 'Вы уже добавили максимальное количество текстов (' . $submissionLimit . ') в этот конкурс.';
    if ($_GET['error'] === 'already_voted') $errorMsg = 'Вы уже проголосовали в этом конкурсе.';
    echo '<div class="alert error">' . $errorMsg . '</div>';
}

if ($activeContest) {
    switch ($activeContest['status']) {
        case 'pending':
            echo '<div class="contest-placeholder"><p>Конкурс скоро начнется. Прием работ откроется ' . date('d.m.Y в H:i', strtotime($activeContest['submission_start'])) . '. Следите за обновлениями!</p></div>';
            break;
        case 'submission_active':
            $endDate = date('Y-m-d\TH:i:s', strtotime($activeContest['submission_end']));
            echo '<h3>До конца приёма работ осталось:</h3>';
            echo '<div id="countdown-timer" class="countdown-timer" data-countdown-to="' . $endDate . '">Загрузка таймера...</div>';
            
            // =====================================================================
            // НАЧАЛО ИЗМЕНЕНИЯ: ИСПОЛЬЗУЕМ ПЕРЕМЕННУЮ $submissionLimit
            // =====================================================================
            if ($userSubmissionCount < $submissionLimit) {
            ?>
                <div class="upload-form-container">
                    <h3>Отправить на конкурс <span class="tooltip-icon" data-tooltip="Вы можете подать на конкурс не более <?php echo $submissionLimit; ?> текстов. После отправки вы сможете редактировать свой текст до окончания этапа приема работ.">[?]</span></h3>
                    <form class="form-styled" action="/api/upload_lyric.php" method="post">
                        <div class="form-group"><label for="lyric_title">Название текста/песни:</label><input type="text" id="lyric_title" name="lyric_title" required></div>
                        <div class="form-group"><label for="lyric_content">Ваш текст:</label><textarea id="lyric_content" name="lyric_content" rows="15" required></textarea></div>
                        <button type="submit" class="button-primary">Отправить на конкурс</button>
                    </form>
                </div>
            <?php
            } else {
                echo '<div class="alert">Вы достигли лимита (' . $submissionLimit . ' текста) в этом конкурсе.</div>';
            }
            // =====================================================================
            // КОНЕЦ ИЗМЕНЕНИЯ
            // =====================================================================
            
            echo '<div class="lyrics-list"><h2>Уже на конкурсе</h2>';
            renderLyricsList($lyricsList, $currentUser, false, $userHasVotedInContest);
            echo '</div>';
            break;

        case 'voting_active':
            $endDate = date('Y-m-d\TH:i:s', strtotime($activeContest['voting_end']));
            echo '<h3>До конца голосования осталось:</h3>';
            echo '<div id="countdown-timer" class="countdown-timer" data-countdown-to="' . $endDate . '">Загрузка таймера...</div>';
            echo '<div class="lyrics-list">';
            renderLyricsList($lyricsList, $currentUser, true, $userHasVotedInContest);
            echo '</div>';
            break;
        
        case 'results':
            echo '<div class="contest-placeholder">';
            echo '<h3>Голосование завершено!</h3>';
            echo '<p>Спасибо всем за участие. Победитель этого этапа определен и будет отображаться здесь до окончания всего сезона.</p>';
            echo '</div>';

            if ($lyricWinner) {
                echo '<div class="lyric-item-form-wrapper winner-display">';
                echo '<div class="lyric-title-header">';
                echo '<h3>🥇 ' . htmlspecialchars($lyricWinner['title']) . '</h3>';
                echo '<button type="button" class="copy-btn copy-title-btn" title="Копировать название"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>';
                echo '</div>';
                echo '<p class="lyric-author">Автор: ' . htmlspecialchars($lyricWinner['author']) . '</p>';
                echo '<div class="form-group lyric-content-wrapper">';
                echo '<textarea class="lyric-display-textarea" rows="15" readonly>' . htmlspecialchars($lyricWinner['content']) . '</textarea>';
                echo '<button type="button" class="copy-btn copy-text-btn" title="Копировать текст"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>';
                echo '</div>';
                echo '</div>';
            } else {
                 echo '<div class="alert">Победитель не был определен (возможно, не было участников).</div>';
            }
            break;
        case 'closed':
            echo '<div class="contest-placeholder"><h3>Этот конкурс завершен!</h3><p>Спасибо всем за участие.</p></div>';
            break;
    }
} else {
    echo '<div class="contest-placeholder"><p>В данный момент конкурсы не проводятся.</p></div>';
}
?>

<?php require_once 'templates/footer.php'; ?>