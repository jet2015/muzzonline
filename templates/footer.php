<?php
// Файл: footer.php (ПОЛНАЯ ВЕРСИЯ С НОВЫМ ДИЗАЙНОМ ПЛЕЕРА)
?>
        </div>
    </main>

    <!-- ОБНОВЛЕННЫЙ БЛОК СПОНСОРОВ -->
    <div class="sponsors-wrapper">
        <div class="container">
            <div class="sponsors-display">
                <h3>Наши спонсоры</h3>
                <div class="sponsors-list-content">
                    <?php
                    try {
                        $sponsors_sql = "SELECT u.login, d.amount, d.receipt_filename 
                                         FROM donations d
                                         JOIN users u ON d.user_id = u.id
                                         WHERE d.status = 'approved' AND d.receipt_filename IS NOT NULL
                                         ORDER BY d.amount DESC";
                        $sponsors_stmt = $pdo->query($sponsors_sql);
                        $sponsors = $sponsors_stmt->fetchAll();

                        if (empty($sponsors)) {
                            echo '<p style="text-align: center;">Станьте первым спонсором этого сезона!</p>';
                        } else {
                            foreach ($sponsors as $sponsor) {
                                echo '<div class="sponsor-item">';
                                echo '<span>' . htmlspecialchars($sponsor['login']) . ' - ' . htmlspecialchars($sponsor['amount']) . ' руб.</span>';
                                echo '<img src="/assets/images/receipt-icon.svg" class="receipt-icon" data-receipt-src="/uploads/receipts/' . htmlspecialchars($sponsor['receipt_filename']) . '" alt="Чек">';
                                echo '</div>';
                            }
                        }
                    } catch (\PDOException $e) {
                        echo '<p>Не удалось загрузить список спонсоров.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- НОВЫЙ ДИЗАЙН ПЛЕЕРА                                             -->
    <!-- ================================================================= -->
    <footer class="global-player-container">
        <div class="player-wrapper container">
            <!-- Ползунок навигации теперь наверху -->
            <div class="progress-container">
                <span id="current-time">0:00</span>
                <div id="progress-bar"><div id="progress"></div></div>
                <span id="duration-time">0:00</span>
            </div>

            <!-- Нижний ряд со всеми остальными элементами -->
            <div class="player-bottom-row">
                <div class="player-track-details">
                    <span id="track-title-display">Выберите трек</span>
                    <span id="track-author-display"></span>
                </div>

                <div class="player-main-controls">
                    <button id="prev-btn" class="player-btn">⏮</button>
                    <button id="play-pause-btn" class="player-btn">▶</button>
                    <button id="next-btn" class="player-btn">⏭</button>
                </div>

                <div class="player-volume-controls">
                    <div class="volume-slider-group">
                        <span>🔊</span>
                        <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1" title="Громкость">
                    </div>
                    <div class="volume-slider-group gain-slider">
                        <span>🔥</span>
                        <input type="range" id="gain-slider" min="1" max="3" step="0.1" value="1" title="Усиление">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <audio id="audio-player" style="display:none;" crossorigin="anonymous"></audio>

    <!-- МОДАЛЬНОЕ ОКНО ДЛЯ ЧЕКОВ -->
    <div id="receipt-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-controls">
                <button id="zoom-in">+</button>
                <button id="zoom-out">-</button>
                <button id="close-modal">×</button>
            </div>
            <img id="modal-image" src="" alt="Чек">
        </div>
    </div>

    <script src="/assets/js/player.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>