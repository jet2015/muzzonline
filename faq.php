<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

// --- Загрузка данных из БД ---
try {
    // Получаем все вопросы и ответы, сортируя их по полю sort_order
    $stmt = $pdo->query("SELECT question, answer FROM faq ORDER BY sort_order ASC, id ASC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $faqs = [];
}

// Устанавливаем метаданные для страницы
$pageTitle = 'Вопросы и ответы (FAQ)';
$pageDescription = 'Ответы на часто задаваемые вопросы о работе платформы MuzzOnline, конкурсах, баллах и многом другом.';
$pageKeywords = 'faq, вопросы и ответы, помощь, поддержка, правила';

require_once 'templates/header.php';
?>

<style>
/* ===== НАЧАЛО: НОВЫЕ, УЛУЧШЕННЫЕ СТИЛИ ДЛЯ FAQ ===== */
.faq-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 15px 2rem 15px; /* Добавлен отступ снизу */
}

.faq-container h1 {
    color: var(--accent-color);
    margin-bottom: 2.5rem;
    text-shadow: 0 0 10px rgba(187, 134, 252, 0.3);
}

.faq-list {
    display: flex;
    flex-direction: column;
    gap: 1rem; /* Расстояние между вопросами */
}

.faq-item {
    border: 1px solid #282828;
    border-radius: 8px;
    background-color: var(--surface-color);
    transition: all 0.3s ease;
}
.faq-item.active {
    border-color: var(--accent-color);
    box-shadow: 0 0 15px rgba(187, 134, 252, 0.2);
}

.faq-question {
    width: 100%;
    background-color: transparent;
    border: none;
    padding: 1.2rem 1.5rem;
    text-align: left;
    font-size: 1.1rem;
    font-weight: bold;
    color: var(--primary-text-color);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.faq-icon {
    font-size: 1.5rem;
    font-weight: 300;
    color: var(--accent-color);
    transition: transform 0.3s ease;
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease-out, padding 0.3s ease-out;
    padding: 0 1.5rem; /* Горизонтальные отступы для плавности анимации */
}

.faq-answer p {
    margin: 0;
    padding-bottom: 1.5rem; /* Отступ появляется вместе с текстом */
    line-height: 1.7;
    color: var(--secondary-text-color);
    border-top: 1px solid #282828;
    padding-top: 1.5rem;
}

.faq-item.active .faq-question {
    color: var(--accent-color);
}

.faq-item.active .faq-icon {
    transform: rotate(45deg);
}
/* ===== КОНЕЦ: НОВЫЕ, УЛУЧШЕННЫЕ СТИЛИ ДЛЯ FAQ ===== */
</style>

<div class="faq-container">
    <h1>Вопросы и ответы (FAQ)</h1>

    <?php if (empty($faqs)): ?>
        <p class="centered-text">Раздел находится в стадии наполнения. Скоро здесь появятся ответы на часто задаваемые вопросы.</p>
    <?php else: ?>
        <div class="faq-list">
            <?php foreach ($faqs as $faq): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span><?php echo htmlspecialchars($faq['question']); ?></span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');

        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            // Закрываем все открытые ответы, кроме текущего (если он уже открыт)
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.faq-answer').style.maxHeight = '0';
                }
            });

            // Переключаем состояние текущего элемента
            item.classList.toggle('active');
            if (item.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
            } else {
                answer.style.maxHeight = '0';
            }
        });
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>