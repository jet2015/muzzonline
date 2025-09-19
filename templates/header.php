<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MuzzOnline</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="container">
            <a href="/" class="logo">
                <span class="logo-desktop">Универсальный музыкальный плеер</span>
                <span class="logo-mobile">MuzzOnline</span>
            </a>
            
            <nav class="main-nav">
                <div class="nav-links">
                    <ul>
                        <li><a href="/">Хит-парад</a></li>
                        <li><a href="/contest_tracks.php">Конкурс треков</a></li>
                        <li><a href="/contest_lyrics.php">Конкурс текстов</a></li>
                        
                        <?php if (isUserLoggedIn()): ?>
                            <!-- Ссылки для авторизованных пользователей -->
                            <li><a href="/profile.php">Профиль</a></li>
                            <li><a href="/api/logout.php">Выйти</a></li>
                        <?php else: ?>
                            <!-- Ссылки для гостей -->
                            <li><a href="/login.php">Войти</a></li>
                            <li><a href="/registration.php">Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <button class="burger-menu" aria-label="Открыть меню">
                    <span class="burger-bar"></span>
                    <span class="burger-bar"></span>
                    <span class="burger-bar"></span>
                </button>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">

