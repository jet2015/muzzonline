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

$activeContest = null;
$contestTracks = [];
$winners = [];
$prizePool = 0;
$userHasVotedInContest = false; 

try {
    // Ищем самый последний созданный конкурс треков для ОТОБРАЖЕНИЯ на странице
    $stmt_active = $pdo->prepare("SELECT * FROM track_contests ORDER BY id DESC LIMIT 1");
    $stmt_active->execute();
    $activeContest = $stmt_active->fetch();

    // Ищем ID будущего конкурса (pending), для которого собираются средства
    $stmt_next_contest = $pdo->prepare("SELECT id FROM track_contests WHERE status = 'pending' ORDER BY id DESC LIMIT 1");
    $stmt_next_contest->execute();
    $nextContest = $stmt_next_contest->fetch();

    if ($nextContest) {
        // Считаем сумму ОДОБРЕННЫХ донатов для будущего конкурса
        $stmt_donations = $pdo->prepare("SELECT SUM(amount) as total FROM donations WHERE track_contest_id = ? AND status = 'approved'");
        $stmt_donations->execute([$nextContest['id']]);
        $result = $stmt_donations->fetch();
        if ($result && $result['total'] > 0) {
            $prizePool = (float)$result['total'];
        }
    }

    if ($activeContest) {
        if ($currentUser) {
            $stmt_vote_check = $pdo->prepare("SELECT 1 FROM votes v JOIN tracks t ON v.track_id = t.id WHERE v.user_id = ? AND t.track_contest_id = ? LIMIT 1");
            $stmt_vote_check->execute([$currentUser['id'], $activeContest['id']]);
            if ($stmt_vote_check->fetch()) {
                $userHasVotedInContest = true;
            }
        }
        
        if ($activeContest['status'] === 'submission_active' || $activeContest['status'] === 'voting_active') {
            $sql_tracks = "SELECT tracks.id, tracks.user_id, tracks.title, tracks.filename, users.login AS author, COUNT(votes.id) AS vote_count FROM tracks JOIN users ON tracks.user_id = users.id LEFT JOIN votes ON tracks.id = votes.track_id WHERE tracks.page_type = 'contest' AND tracks.track_contest_id = ? GROUP BY tracks.id ORDER BY tracks.upload_date DESC";
            $stmt_tracks_list = $pdo->prepare($sql_tracks);
            $stmt_tracks_list->execute([$activeContest['id']]);
            $contestTracks = $stmt_tracks_list->fetchAll();
        }
        elseif ($activeContest['status'] === 'closed') {
            $sql_winners = "SELECT w.place, w.amount, u.login, t.title as track_title FROM winnings w JOIN users u ON w.user_id = u.id JOIN tracks t ON w.track_id = t.id WHERE w.track_contest_id = ? ORDER BY w.place ASC";
            $stmt_winners_list = $pdo->prepare($sql_winners);
            $stmt_winners_list->execute([$activeContest['id']]);
            $winners = $stmt_winners_list->fetchAll();
        }
    }
} catch (\PDOException $e) {
    echo '<div class="alert error">Произошла ошибка при загрузке данных конкурса.</div>';
}
?>

<h1>Конкурс треков <?php if ($activeContest) echo ' - ' . htmlspecialchars($activeContest['name']); ?>
    <span class="tooltip-icon" data-tooltip="Конкурс треков - главное событие сезона! Призовой фонд формируется за счет пожертвований спонсоров. Победители, занявшие первые три места, разделят весь призовой фонд в пропорции 70/20/10.">[?]</span>
</h1>

<?php
if ($prizePool > 0) {
    echo '<div class="prize-pool">Текущий призовой фонд: <strong>' . number_format($prizePool, 2, '.', ' ') . ' руб.</strong></div>';
}
if (isset($_GET['upload']) && $_GET['upload'] === 'success') {
    echo '<div class="alert success">Ваш трек успешно загружен и принят на конкурс!</div>';
}
if (isset($_GET['error']) && $_GET['error'] === 'already_voted') {
    echo '<div class="alert error">Вы уже проголосовали в этом конкурсе.</div>';
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
            ?>
            <div class="upload-form-container">
                <h3>Отправить на конкурс <span class="tooltip-icon" data-tooltip="Вы можете подать на конкурс только один трек. Убедитесь, что это ваша лучшая работа! Редактировать или заменять трек после загрузки нельзя.">[?]</span></h3>
                <form class="form-styled" action="/api/upload_contest_track.php" method="post" enctype="multipart/form-data">
                    <div class="form-group"><label for="track_title">Название трека:</label><input type="text" id="track_title" name="track_title" required></div>
                    <div class="form-group"><label for="track_file">Файл трека (MP3, WAV до 15МБ):</label><input type="file" id="track_file" name="track_file" accept=".mp3,.wav" required></div>
                    <button type="submit" class="button-primary">Отправить на конкурс</button>
                </form>
            </div>
            
            <div class="tracklist-header"><h3>Уже на конкурсе:</h3></div>
            <div class="tracklist contest">
                <?php if (empty($contestTracks)): ?>
                    <p>На конкурс еще не было подано ни одного трека.</p>
                <?php else: ?>
                    <?php foreach ($contestTracks as $track): ?>
                        <div class="track-item" data-id="<?php echo $track['id']; ?>" data-filename="<?php echo htmlspecialchars($track['filename']); ?>">
                            <button class="track-play-button">▶</button>
                            <div class="track-info">
                                <span class="track-title"><?php echo htmlspecialchars($track['title']); ?></span>
                                <span class="track-author">Автор: Автор скрыт до подведения итогов</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
            break;

        case 'voting_active':
            $endDate = date('Y-m-d\TH:i:s', strtotime($activeContest['voting_end']));
            echo '<h3>До конца голосования осталось:</h3>';
            echo '<div id="countdown-timer" class="countdown-timer" data-countdown-to="' . $endDate . '">Загрузка таймера...</div>';
            
            echo '<div class="tracklist contest">';
            if (empty($contestTracks)) {
                echo '<p>На конкурс не было подано ни одного трека.</p>';
            } else {
                foreach ($contestTracks as $track) {
                    $isAuthor = ($currentUser && $currentUser['id'] === $track['user_id']);
                    $canVote = !$userHasVotedInContest && !$isAuthor;

                    echo '<div class="track-item" data-id="' . $track['id'] . '" data-filename="' . htmlspecialchars($track['filename']) . '">';
                    echo '<button class="track-play-button">▶</button>';
                    echo '<div class="track-info">';
                    echo '<span class="track-title">' . htmlspecialchars($track['title']) . '</span>';
                    echo '<span class="track-author">Автор: Автор скрыт до подведения итогов</span>';
                    echo '</div>';
                    echo '<div class="vote-action">';
                    echo '<span class="vote-count">' . $track['vote_count'] . '</span>';
                    echo '<button class="vote-button track-vote-button" title="Голосовать за трек" ' . ($canVote ? '' : 'disabled') . ' data-user-has-voted="' . ($userHasVotedInContest ? 'true' : 'false') . '">❤</button>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
            break;
        
        case 'closed':
            $isTimerActive = false;
            if (!empty($activeContest['closed_at'])) {
                $closedTime = new DateTime($activeContest['closed_at']);
                $now = new DateTime();
                $interval = $now->getTimestamp() - $closedTime->getTimestamp();
                if ($interval < 300) { // 5 минут
                    $isTimerActive = true;
                    $timerEndTime = $closedTime->getTimestamp() + 300;
                }
            }

            if ($isTimerActive) {
                $endDate = date('Y-m-d\TH:i:s', $timerEndTime);
                // =====================================================================
                // НАЧАЛО ИЗМЕНЕНИЯ: МЕНЯЕМ ТЕКСТ
                // =====================================================================
                echo '<h3>Поздравляем победителей конкурса треков. Страница обновится через:</h3>';
                // =====================================================================
                // КОНЕЦ ИЗМЕНЕНИЯ
                // =====================================================================
                echo '<div id="countdown-timer" class="countdown-timer" data-countdown-to="' . $endDate . '" data-reload="true">Загрузка таймера...</div>';

                if (!empty($winners)) {
                    echo '<div class="awards-section dark-style">';
                    echo '<h3>🏆 Победители конкурса</h3>';
                    echo '<div class="awards-grid">';
                    foreach ($winners as $winner) {
                        echo '<div class="award-card">';
                        echo '<div class="award-icon">';
                        if ($winner['place'] == 1) echo '🥇';
                        if ($winner['place'] == 2) echo '🥈';
                        if ($winner['place'] == 3) echo '🥉';
                        echo '</div>';
                        echo '<div class="award-details">';
                        echo '<span class="award-title">' . $winner['place'] . ' место - ' . htmlspecialchars($winner['login']) . '</span>';
                        echo '<span class="award-subtitle">Трек: "' . htmlspecialchars($winner['track_title']) . '"</span>';
                        echo '</div>';
                        echo '<div class="award-amount">+' . number_format($winner['amount'], 2, '.', ' ') . ' руб.</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p>Победители не были определены.</p>';
                }
            } else {
                echo '<div class="contest-placeholder">';
                echo '<h3>Конкурс завершен!</h3>';
                echo '<p>Треки-победители перенесены в <a href="/hit_parade.php">Хит-парад</a>.</p>';
                echo '<p>Ждём запуска следующего конкурса!</p>';
                echo '</div>';
            }
            break;
    }
} else {
    echo '<div class="contest-placeholder"><p>В данный момент конкурсы не проводятся. Ждём запуска следующего конкурса!</p></div>';
}
?>

<?php require_once 'templates/footer.php'; ?>