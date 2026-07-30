<?php
session_start();
require_once 'core/db_connect.php';
require_once 'core/auth.php';

// SEO-данные для страницы "Контакты"
$pageTitle = 'Контакты';
$pageDescription = 'Свяжитесь с администрацией проекта MuzzOnline. Email для предложений, техническая поддержка и другие способы связи.';
$pageKeywords = 'контакты, связь с администрацией, поддержка, email, написать нам';

require_once 'templates/header.php';
?>

<div class="static-page-container">
    <h1>Контакты</h1>
    
    <div class="static-page-content">
        <p>
            Мы всегда открыты для общения, предложений и сотрудничества. Если у вас возникли вопросы, проблемы с сайтом или есть интересные идеи, вы можете связаться с нами одним из следующих способов.
        </p>

        <h2>Основные контакты</h2>
        <ul>
            <li>
                <strong>Техническая поддержка:</strong><br>
                Если вы столкнулись с ошибкой или что-то не работает, напишите нам на email: 
                <a href="mailto:support@muzzonline.ru">support@muzzonline.ru</a>
            </li>
            <li>
                <strong>Вопросы сотрудничества и рекламы:</strong><br>
                По вопросам партнерства, спонсорства конкурсов или размещения рекламы, пожалуйста, обращайтесь по адресу: 
                <a href="mailto:partner@muzzonline.ru">partner@muzzonline.ru</a>
            </li>
            <li>
                <strong>Общие вопросы:</strong><br>
                Для всех остальных вопросов: 
                <a href="mailto:info@muzzonline.ru">info@muzzonline.ru</a>
            </li>
        </ul>

        <h2>Социальные сети</h2>
        <p>
            Следите за нашими новостями и анонсами конкурсов в нашем официальном Telegram-канале:
        </p>
        <ul>
            <li>
                <a href="https://t.me/sunochatmix" target="_blank" rel="noopener noreferrer">Telegram: MuzzOnline News</a>
            </li>
        </ul>
        
        <p>
            Мы стараемся отвечать на все обращения в течение 24 часов.
        </p>
    </div>
</div>

<style>
/* Используем те же стили, что и для страницы "О проекте" */
.static-page-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
    background-color: var(--surface-color);
    border-radius: 8px;
}
.static-page-content h2 {
    color: var(--accent-color);
    border-bottom: 1px solid #333;
    padding-bottom: 10px;
    margin-top: 2rem;
}
.static-page-content a {
    color: var(--accent-color);
    text-decoration: none;
}
.static-page-content a:hover {
    text-decoration: underline;
}
.static-page-content p, .static-page-content li {
    line-height: 1.8;
    color: var(--primary-text-color);
}
</style>

<?php require_once 'templates/footer.php'; ?>