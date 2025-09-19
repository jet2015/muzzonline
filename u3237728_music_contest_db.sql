-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 19 2025 г., 09:30
-- Версия сервера: 8.0.25-15
-- Версия PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u3237728_music_contest_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `donations`
--

CREATE TABLE `donations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `lyric_contest_id` int DEFAULT NULL,
  `track_contest_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_filename` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `lyric_contest_id`, `track_contest_id`, `amount`, `receipt_filename`, `status`, `created_at`) VALUES
(1, 1, 2, NULL, 2000.00, '74ba0b882591059e699d4c8ee6106c64.jpeg', 'pending', '2025-08-30 22:32:30'),
(2, 1, 2, NULL, 4000.00, '90a476c30bbdc9c325f79de961762e9b.jpg', 'pending', '2025-08-30 22:43:59'),
(3, 1, 3, NULL, 10000.00, '8eb011f74cca574c5e1442f6ee1baabd.jpg', 'pending', '2025-09-13 09:06:25');

-- --------------------------------------------------------

--
-- Структура таблицы `lyrics`
--

CREATE TABLE `lyrics` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `lyric_contest_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('submitted','winner','loser') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `lyrics`
--

INSERT INTO `lyrics` (`id`, `user_id`, `lyric_contest_id`, `title`, `content`, `status`, `created_at`) VALUES
(1, 1, 1, 'Там белые розы', '[Verse 1]\r\nГлупые снежинки \r\nРозовый закат \r\nДетство пролетело \r\nМного лет назад \r\nМы гулять готовы \r\nБыли до зари \r\nИз жевачек дули пузыри \r\nНа трамвайчик старый \r\nЯ беру билет\r\nПусть везёт меня \r\nВ мои семнадцать лет\r\nТёплыми лучами \r\nНас ласкает май \r\nМузыку включай - вспоминай!\r\n\r\n[Chorus]\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил.\r\n\r\nЯ на остановке,\r\nНо трамвая нет\r\nИ уносит ветер,\r\nКупленный билет\r\nТолько я надеюсь,\r\nЧто придёт трамвай \r\nИ увезёт меня в далёкий май!\r\n\r\n[Chorus]\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. \r\n\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. \r\n\r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. ', 'loser', '2025-08-30 20:33:35'),
(2, 1, 1, 'Hey! Hop! Top! ', '[INTRO]\r\nHey! Hop! Top!\r\nLights are up, we’re ready, ready!\r\nHey! Hop! Top!\r\nWe will burn this floor already!\r\n\r\n[Вступление] \r\nHey! Hop! Top! -Город плавится в неоне, Hey! Hop! Top! -Ночь на огненном перроне. Hey! Hop! Top! -Этот бит — почти цунами, Hey! Hop! Top! -Что-же делает он с нами. \r\nHey! Hop! Top! -В венах — импульс, вспышка света, \r\nHey! Hop! Top! -Начинается всё это.\r\n[Куплет 1] \r\nHey! Hop! Top! -Пульс динамиков по стенам \r\nHey! Hop! Top! -Пробегает по всем венам. \r\nHey! Hop! Top! -Каждый в зале — отраженье \r\nHey! Hop! Top! -Одного сердцебиенья. \r\nHey! Hop! Top! -Больше нет чужих и «если», \r\nHey! Hop! Top! -Ты читаешь мои мысли \r\n[Припев] \r\nHey! Hop! Top! -Танец нас не отпускает, Hey! Hop! Top! -Мир вокруг нас исчезает. \r\nHey! Hop! Top! -Только бит — мы с ним едины, \r\nHey! Hop! Top! -Мы сегодня как пружины. \r\nHey! Hop! Top! -Это наш девятый вал, \r\nHey! Hop! Top! -Ночь срывает карнавал.\r\n[Куплет 2] \r\nHey! Hop! Top! -стробоскопы бьют разряды, Hey! Hop! Top! -Ловим мы чужие взгляды. Hey! Hop! Top! -Силуэты в дымке рваной — Hey! Hop! Top! -Части драмы безымянной. Hey! Hop! Top! -Не смотри сейчас на время, Hey! Hop! Top! -Бит стучится в твоё темя\r\n[Припев] \r\nHey! Hop! Top! -Танец нас не отпускает, Hey! Hop! Top! -Мир вокруг нас исчезает. \r\nHey! Hop! Top! -Только бит — мы с ним едины, \r\nHey! Hop! Top! -Мы сегодня как пружины. \r\nHey! Hop! Top! -Это наш девятый вал, \r\nHey! Hop! Top! -Ночь срывает карнавал.\r\n[Бридж] \r\nHey! Hop! Top! -Там, за стёклами, огни проспектов \r\nHey! Hop! Top! -Ловят эхо наших треков. Hey! Hop! Top! -Дай мне руку, просто слушай, \r\nHey! Hop! Top! -Как рвёт бит наши души. \r\nHey! Hop! Top! -На секунду тише… громче! Hey! Hop! Top! -Этой ночью песни  звонче.\r\n[Финал] \r\nУтро. Город пахнет сталью. \r\nМы покрыты звёздной пылью. \r\nИ пока молчат колонки,\r\nБит стучит по перепонкам. \r\nВ сердце музыка как код, \r\nНовый день нас снова ждёт.\r\n\r\nHey! Hop! Top! Hey! Hop! Top!\r\n\r\nHey! Hop! Top! Hey! Hop! Top!', 'loser', '2025-08-30 21:33:47'),
(3, 2, 1, 'СВАЙПНИ МЕНЯ НЕЖНО', '[Instrumental intro] \r\nБудь смелее! Будь смелей!\r\nЖми быстрее, не робей… [Vocalise intro]\r\n\r\n[Verse 1] \r\nЭкран телефона, полуночный ритуал,\r\nЯ в поисках чуда, чтоб кто-то совпал.\r\nТы смотришь налево, я — только направо,\r\nНа лайки друг другу кричим дружно «браво»!\r\n\r\n[Pre-Chorus] \r\nПять фоток, три факта и рост в сантиметрах,\r\nЛюбовь зарождается в этих анкетах!\r\n\r\n[Chorus] \r\nО, Тиндер, Тиндер — свайпов карусель,\r\nТо принц на фото, то какая-то херь!\r\nО, Тиндер, Тиндер — ты моя отрада,\r\nСовпали? Супер! Встреча? \r\nОй, не надо! [Aaaah][Vocalise chorus]\r\n\r\n[Verse 2] \r\nОгонь - эмОдзи, подмигнул в чат,\r\nА в жизни молчим, никому не рад.\r\nПропал интернет — и любви как не бывало,\r\nДва сердца в сети, и им вечно мало!\r\n\r\n[Pre-Chorus] \r\nКод и провода — электрический угар,\r\nМы пишем «люблю», а в реальности — пар.\r\n\r\n[Chorus] \r\nО, Тиндер, Тиндер — свайпов карусель,\r\nТо принц на фото, то какая-то херь!\r\nО, Тиндер, Тиндер — ты моя отрада,\r\nСовпали? Супер! Встреча?\r\nОй, не надо! [Aaaah][Vocalise chorus]\r\n\r\n[Bridge] \r\nВойти, снова выйти — какая игра,\r\nЛистаем налево, листаем с утра.\r\nА вдруг это чувства? А вдруг это данные?\r\nЗагружены в профиль, чужие и странные...\r\n\r\n[Guitar Solo] [Energetic]\r\n\r\n[Chorus] \r\nО, Тиндер, Тиндер — свайпов карусель,\r\nТо принц на фото, то какая-то херь!\r\nО, Тиндер, Тиндер — ты моя отрада,\r\nСовпали? Супер! Встреча? \r\nОй, не надо! [Aaaah][Vocalise chorus]\r\n\r\n[Outro] [Fade Out]\r\nСвайп... свайп... мэтч!\r\nОй, всё. [Vocalise outro]\r\n\r\n[End]', 'winner', '2025-08-30 21:35:06'),
(4, 2, 1, 'Мера', '[Instrumental intro] \r\n\r\n[Verse 1]\r\nКолготки в сеточку,\r\nРешила девочка.\r\nНам груди белые\r\nТут показать.\r\n\r\n[Pre-Chorus] \r\nТрусы надела ты,\r\nОчки надела ты.\r\nГлаза бесстыжие\r\nСвои скрывать.\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Verse 2]\r\nНеоном светится\r\nТвоя уверенность.\r\nИ каждый смотрит, но\r\nНе видит суть.\r\n\r\n[Pre-Chorus] \r\nЗа этим образом,\r\nХолодным, собранным,\r\nТы хочешь душу свою\r\nСпрятать вглубь.\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Verse 3] \r\nПустые лайки в ряд,\r\nХолодный маскарад.\r\nТы ловишь взгляды, как\r\nДешёвый приз.\r\nНо ночь закончится,\r\nИ одиночество\r\nИсполнит шёпотом\r\nЛюбой каприз.\r\n\r\n[Bridge] \r\nА если снять очки?\r\nИ свет погаснет вдруг...\r\nОстанется лишь страх\r\nИ сердца тихий стук...\r\nЗачем весь этот блеск, зачем эта игра?\r\nВедь ты сама себе уже давно не рада...\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Outro] \r\nМеру знать... меру знать... себя терять...\r\n[Fade to End]', 'loser', '2025-08-30 21:35:59'),
(5, 1, 2, 'Там белые розы', '[Verse 1]\r\nГлупые снежинки \r\nРозовый закат \r\nДетство пролетело \r\nМного лет назад \r\nМы гулять готовы \r\nБыли до зари \r\nИз жевачек дули пузыри \r\nНа трамвайчик старый \r\nЯ беру билет\r\nПусть везёт меня \r\nВ мои семнадцать лет\r\nТёплыми лучами \r\nНас ласкает май \r\nМузыку включай - вспоминай!\r\n\r\n[Chorus]\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил.\r\n\r\nЯ на остановке,\r\nНо трамвая нет\r\nИ уносит ветер,\r\nКупленный билет\r\nТолько я надеюсь,\r\nЧто придёт трамвай \r\nИ увезёт меня в далёкий май!\r\n\r\n[Chorus]\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. \r\n\r\nТам белые розы,белые!\r\nТам розовый был закат.\r\nТам школьные танцы не смелые\r\nУже не вернуть назад. \r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. \r\n\r\nТам белые розы, белые!\r\nСтесняясь тебе дарил, \r\nСтихи о любви неумелые\r\nЯ в памяти сохранил. ', 'loser', '2025-08-30 22:17:02'),
(6, 1, 2, 'Мера', '[Instrumental intro] \r\n\r\n[Verse 1]\r\nКолготки в сеточку,\r\nРешила девочка.\r\nНам груди белые\r\nТут показать.\r\n\r\n[Pre-Chorus] \r\nТрусы надела ты,\r\nОчки надела ты.\r\nГлаза бесстыжие\r\nСвои скрывать.\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Verse 2]\r\nНеоном светится\r\nТвоя уверенность.\r\nИ каждый смотрит, но\r\nНе видит суть.\r\n\r\n[Pre-Chorus] \r\nЗа этим образом,\r\nХолодным, собранным,\r\nТы хочешь душу свою\r\nСпрятать вглубь.\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Verse 3] \r\nПустые лайки в ряд,\r\nХолодный маскарад.\r\nТы ловишь взгляды, как\r\nДешёвый приз.\r\nНо ночь закончится,\r\nИ одиночество\r\nИсполнит шёпотом\r\nЛюбой каприз.\r\n\r\n[Bridge] \r\nА если снять очки?\r\nИ свет погаснет вдруг...\r\nОстанется лишь страх\r\nИ сердца тихий стук...\r\nЗачем весь этот блеск, зачем эта игра?\r\nВедь ты сама себе уже давно не рада...\r\n\r\n[Chorus] \r\nТы вся красавица,\r\nТы хочешь нравиться,\r\nНо надо в этом и меру знать!\r\nТы вся красавица,\r\nИграешь с глянцем ты,\r\nНо можно в этом себя терять!\r\n\r\n[Outro] \r\nМеру знать... меру знать... себя терять...\r\n[Fade to End]\r\n[End]', 'winner', '2025-08-30 22:17:27'),
(7, 1, 3, 'КАРЛСОН СПЕШИТ НА ПРОДЕЛКИ', '[Verse 1]\r\nЯ - озорник и шалун круглый год,\r\nС крыши на крышу лечу наперёд!\r\nСмех и проказы - весёлый секрет,\r\nИгр со мной удивительней нет!\r\n\r\n[Chorus]\r\nЖ-ж-ж! Жужжу, лечу!\r\nХа-ха-ха! Я не молчу!\r\nПлюх-плюх-плюх! Варенье тут,\r\nНям-ням-ням! Его мне в путь!\r\n\r\nХлоп-хлоп-хлоп! Ладошки бей!\r\nУ-ху-ху! Лети скорей!\r\nЛучший друг, шалун большой -\r\nОзорник я, золотой!\r\n\r\n[Verse 2]\r\nСкучно? - ЗовИ, я мигом лечу,\r\nПесню смешную для вас прокричу!\r\nФрЕкен Бок в испуге вздохнЁт,\r\nА у ребят сведёт от смеха живот!\r\n\r\n[Chorus]\r\nЖ-ж-ж! Жужжу, лечу!\r\nХа-ха-ха! Я не молчу!\r\nПлюх-плюх-плюх! Варенье тут,\r\nНям-ням-ням! Его мне в путь!\r\n\r\nХлоп-хлоп-хлоп! Ладошки бей!\r\nУ-ху-ху! Лети скорей!\r\nЛучший друг, шалун большой -\r\nОзорник я, но золотой!\r\n\r\n[Bridge]\r\nЯ друг весёлый, надёжный, родной,\r\nСмех зазываю волной озорной.\r\nВместе обманем тоску и беду -\r\nПрыг на окошко - и снова лечу!\r\n\r\n[Final Chorus]\r\nЖ-ж-ж! Жужжу, лечу!\r\nХа-ха-ха! Я не молчу!\r\nПлюх-плюх-плюх! Варенье тут,\r\nНям-ням-ням! Его мне в путь!\r\n\r\nХлоп-хлоп-хлоп! Ладошки бей!\r\nУ-ху-ху! Лети скорей!\r\nЛучший друг, шалун большой -\r\nОзорник я, но золотой!\r\n\r\n[Outro]\r\n- Раз-два-три! Кто летит с земли?\r\n- Это Карлсон! Посмотри!\r\n- Чик-чирик! Скажите вмиг:\r\n- Это Карлсон-озорник!\r\n- Раз-два-три-четыре-пять! Кто смешит нас всех опять?\r\n- Карлсон! Карлсон! Будем вместе петь, играть!\r\nВеселиться и скакать!\r\nИ опять! И опять!\r\nРаз-два-три-четыре-пять!\r\n\r\n[Fade to End]', 'winner', '2025-09-13 08:01:22'),
(8, 6, 3, 'Перо мастера', 'Куплет I\r\n\r\nВ тиши хранится свет ларца,\r\nТам спит перо, дар Муза скрытый;\r\nОно — не блеск, не звон венца,\r\nА сердца шёпот незабвенный, свитый.\r\n\r\nПрипев\r\n\r\nПеро мастера — в нём судьбы след,\r\nВ нём муки ночи и рассвет;\r\nНе злато чтит его, не пир,\r\nА истина и сердца мир.\r\n\r\nКуплет II\r\n\r\nТолпа кричит: «Хвала! Позор!» —\r\nИ каждый голос мимолётный;\r\nНо стих пером зажжёт костёр,\r\nИ будет жить, пока бессмертный.\r\n\r\nПрипев\r\n\r\nПеро мастера — в нём жизни ток,\r\nВ нём плач, и радость, и урок;\r\nНе крик людской, не звон монет,\r\nА сердца вечного завет.\r\n\r\nКуплет III\r\n\r\nНе для пиров, не для наград,\r\nНе для венцов в толпе неверной,\r\nПеро хранит души парад,\r\nИ свет любви — его вселенна.\r\n\r\nФинал (Припев расширенный)\r\n\r\nПеро мастера — живёт века,\r\nОно сильней и зла, и мрака;\r\nВ нём шёпот веры, трепет свой,\r\nИ песнь, что вечно живет с тобой.', 'loser', '2025-09-13 08:05:32'),
(9, 1, 3, 'Снегурка', '[Verse]\r\nЖили-были дед с бабулей, \r\nВодку пили, ели суп.\r\n Жизнь была довольно скучной —\r\n И сказала бабка вдруг. \r\nЧто ты дед сидишь как дурень? –\r\n Я хочу дитя растить! \r\nА у деда нету силы… \r\nСчастье бабке подарить\r\nИ тогда сказала бабка:\r\n[Chorus]\r\nЭх, чтоб чудо сотворить!\r\nДевку надо молодую\r\nНам из снега вЫлепить!\r\nЧтоб красоткою была,\r\nТебе сил мужских дала!\r\nЧтоб качала колыбели,\r\nМы смотрели и балдели!\r\n[Verse]\r\nДед вздохнул: – Ну что ж, пустяк!\r\nСнег собрал и всё - ништяк!\r\nСлепил девушку-красаву,\r\nЧтоб жила им на забаву!\r\nНа дворе стоит Снегурка,\r\nВся стройна и высока.\r\nБабка ахнула в окошко:\r\nДо чего же хороша!\r\nНа снегурку дед глядит\r\n В нём желание горит.\r\nНо боится прикоснуться,\r\n Бабка кулаком грозит!\r\n[Chorus] \r\nЭх, вот это то, что надо!\r\nДевка слишком хороша\r\nВся  из снега создана!\r\nКак не глянь - краса одна,\r\nСразу деду сил дала,\r\nДед с бабулей прибалдели ,\r\nИ запрыгали в постели!\r\n[Verse]\r\nВдруг девица оживает,\r\nСловно ангел говорит:\r\n– Буду я вам помогать,\r\nМыть посуду, убирать.\r\nДом поставила, скотину\r\nСобрала в большой загон.\r\nСтарики лежат на печке,\r\nИ у деда сил вагон.\r\n[Verse ]\r\nВот зима уже проходит,\r\nСолнце светит высоко…\r\nБабка с дедом забоялись:\r\nДевка что-то не того...\r\nМасленица на носу!\r\nДевке расплетут косу!\r\n[Verse ]\r\nНа гулянке песни, пляски,\r\nХороводы у костра…\r\nА Снегурка шепчет тихо:\r\n– Мне к огню идти нельзя…\r\nНе послушала – и чудо:\r\nПрыгнула – и нет её.\r\nТолько лёгкий пар поднялся,\r\nУнеслась в небытиё.\r\n[Bridge]\r\nБабка с дедом слёзы лили:\r\n– Что ж сдержаться не смогла?\r\n[Final chorus – happy end]\r\nНо судьба, смеясь, решила:\r\nБабка двойню родила!\r\nИ свершилось это чудо,\r\nМораль сказки здесь ясна:\r\nКоли верить и трудиться\r\nЖизнь отвесит вам сполна!', 'loser', '2025-09-13 08:07:02');

-- --------------------------------------------------------

--
-- Структура таблицы `lyrics_votes`
--

CREATE TABLE `lyrics_votes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `lyric_id` int NOT NULL,
  `voted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `lyrics_votes`
--

INSERT INTO `lyrics_votes` (`id`, `user_id`, `lyric_id`, `voted_at`) VALUES
(1, 2, 2, '2025-08-30 21:36:15'),
(2, 2, 3, '2025-08-30 21:36:22'),
(3, 1, 3, '2025-08-30 21:36:38'),
(4, 1, 4, '2025-08-30 21:36:43'),
(5, 1, 6, '2025-08-30 22:17:33'),
(6, 2, 6, '2025-08-30 22:17:45'),
(7, 1, 5, '2025-08-30 22:18:21'),
(8, 6, 7, '2025-09-13 08:05:45'),
(9, 6, 8, '2025-09-13 08:05:46'),
(10, 1, 7, '2025-09-13 08:05:53'),
(11, 1, 9, '2025-09-13 08:07:06'),
(12, 4, 7, '2025-09-13 08:07:39'),
(13, 4, 9, '2025-09-13 08:07:42');

-- --------------------------------------------------------

--
-- Структура таблицы `lyric_contests`
--

CREATE TABLE `lyric_contests` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','submission_active','voting_active','results','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submission_start` datetime DEFAULT NULL,
  `submission_end` datetime DEFAULT NULL,
  `voting_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `lyric_contests`
--

INSERT INTO `lyric_contests` (`id`, `name`, `status`, `submission_start`, `submission_end`, `voting_end`) VALUES
(1, 'Поэтический баттл; 1', 'closed', '2025-08-30 23:25:00', '2025-08-30 23:30:00', '2025-08-31 00:40:00'),
(2, 'Поэтический баттл № 2', 'closed', '2025-08-31 01:16:00', '2025-08-31 01:26:00', '2025-08-31 01:35:00'),
(3, 'Поэтический баттл 1', 'closed', '2025-09-13 10:38:00', '2025-09-13 11:10:00', '2025-09-13 11:15:00');

-- --------------------------------------------------------

--
-- Структура таблицы `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'xa@bk.ru', 'a63bce34f71f4a598fdebff692656679635268794ed80a5d2d3ba6d4bcdf2c53', '2025-09-13 12:03:37', '2025-09-13 08:03:37');

-- --------------------------------------------------------

--
-- Структура таблицы `tracks`
--

CREATE TABLE `tracks` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) NOT NULL,
  `duration` int NOT NULL,
  `play_count` int NOT NULL DEFAULT '0',
  `download_count` int NOT NULL DEFAULT '0',
  `page_type` enum('general','contest') NOT NULL,
  `track_contest_id` int DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `tracks`
--

INSERT INTO `tracks` (`id`, `user_id`, `title`, `filename`, `duration`, `play_count`, `download_count`, `page_type`, `track_contest_id`, `upload_date`) VALUES
(1, 1, 'Два сердца в унисон (Riff 320)', '0920b097573921364ec453c1892f7037.mp3', 0, 9, 9, 'general', NULL, '2025-08-27 12:12:49'),
(3, 1, 'Два сердца в унисон (Suno)', '758a890b51624287bafb8922e1ec70e1.mp3', 0, 5, 8, 'general', NULL, '2025-08-27 13:12:59'),
(5, 1, 'Играй музыкант', '56c439bf720cc3c814ff1d35896ae5f5.mp3', 0, 12, 10, 'general', NULL, '2025-08-27 13:13:41'),
(6, 1, 'Грустный дождик', '53e679669e0378c223e63cab47febcf2.mp3', 0, 4, 9, 'general', NULL, '2025-08-27 13:16:18'),
(7, 1, 'Egyptian Desert', 'a45607b72d956025f800ac307bcebd0f.mp3', 0, 9, 4, 'general', NULL, '2025-09-13 06:20:33'),
(8, 1, '3I Atlas (Космический путешественник)', 'track_68c512a448442.mp3', 0, 5, 5, 'general', NULL, '2025-09-13 06:43:48'),
(9, 1, 'Новый мир', 'track_68c51a7a7c450.mp3', 0, 15, 5, 'general', NULL, '2025-09-13 07:17:14'),
(10, 1, 'Танцуй, красавица, танцуй!!!', 'track_68c533857bc36.mp3', 0, 21, 4, 'general', NULL, '2025-09-13 09:04:05'),
(11, 1, 'Прощай, МотоТаня!', 'track_68c5346c67d6d.mp3', 0, 3, 5, 'general', NULL, '2025-09-13 09:07:56'),
(12, 1, 'Я СКУЧАЮ ПО ТЕБЕ', 'track_68c534b6522d7.mp3', 0, 6, 3, 'general', NULL, '2025-09-13 09:09:10'),
(13, 1, 'Метель в чужом городе (Ремикс ЛМ)', 'track_68c53ae44c05c.mp3', 0, 7, 5, 'general', NULL, '2025-09-13 09:35:32'),
(14, 1, 'Атлас и взрыв Бетельгейзе', 'track_68c714acef79e.mp3', 0, 3, 7, 'general', NULL, '2025-09-14 19:17:00'),
(15, 1, 'Дядя Вова у шлагбаума', 'track_68c715469b071.mp3', 0, 3, 7, 'general', NULL, '2025-09-14 19:19:34'),
(16, 1, 'Пассажиры планета Земля', 'track_68c715c3a87c2.mp3', 0, 9, 5, 'general', NULL, '2025-09-14 19:21:39'),
(17, 1, 'Бомж (Modern Talking)', 'track_68c7170555847.mp3', 0, 5, 5, 'general', NULL, '2025-09-14 19:27:01'),
(18, 1, 'Изгибая пространство', 'track_68c717caac962.mp3', 0, 8, 3, 'general', NULL, '2025-09-14 19:30:18'),
(19, 1, 'На хирургическом столе', 'track_68c717fd443f6.mp3', 0, 5, 5, 'general', NULL, '2025-09-14 19:31:09'),
(20, 6, 'Алина Рин - адреналин', 'track_68c854e5f38fc.mp3', 0, 10, 6, 'general', NULL, '2025-09-15 18:03:18'),
(21, 1, 'Моя Львица Марина', 'track_68c90e67aa90b.mp3', 0, 3, 3, 'general', NULL, '2025-09-16 07:14:47'),
(22, 1, 'Мечта', 'track_68caa5404c2ab.mp3', 0, 6, 1, 'general', NULL, '2025-09-17 12:10:40');

-- --------------------------------------------------------

--
-- Структура таблицы `track_contests`
--

CREATE TABLE `track_contests` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','submission_active','voting_active','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submission_start` datetime DEFAULT NULL,
  `submission_end` datetime DEFAULT NULL,
  `voting_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `track_contests`
--

INSERT INTO `track_contests` (`id`, `name`, `status`, `submission_start`, `submission_end`, `voting_end`) VALUES
(1, 'Осенний марафон 1', 'closed', '2025-09-13 12:02:00', '2025-09-13 12:08:00', '2025-09-13 12:22:00');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar_filename` varchar(255) DEFAULT NULL,
  `access_level` enum('limited','full','admin') NOT NULL DEFAULT 'limited',
  `registration_source` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `email`, `avatar_filename`, `access_level`, `registration_source`, `created_at`) VALUES
(1, 'realist70', '$argon2id$v=19$m=65536,t=4,p=1$6pe0GGVmyuApemKh4UP55g$LsYA7ACI8CX085xV+DSNhssZAcDUogzRbcBWNPwSxNI', 'takebet.adm@gmail.com', 'user_1_1756303609.png', 'admin', NULL, '2025-08-27 11:38:54'),
(2, 'index', '$argon2id$v=19$m=65536,t=4,p=1$CK1FoAaMLncBs+gCnkJZxw$2tA+yuWCxG1fZHA+aDHk5yGmSs6GCSpoe53u+wx1cSw', 'xa@bk.ru', NULL, 'full', NULL, '2025-08-27 13:19:24'),
(3, 'deniszakup', '$argon2id$v=19$m=65536,t=4,p=1$ApazlnzuSk+7/MYju76cxw$qOv/w+ymZulD3q4DB+xF32SsyEPUedzW7TnewLdzvH8', 'deniszakup@gmail.com', NULL, 'limited', NULL, '2025-08-30 17:43:57'),
(4, 'xeenia', '$argon2id$v=19$m=65536,t=4,p=1$wIoI3YbSu8Skbl6/3OrqoQ$PsRf6wYf/sKQenx4I6EREOaAJzNIrmekc5JzMTiaoTk', 'xeeniaoffuttd@gmail.com', NULL, 'full', NULL, '2025-09-13 07:19:13'),
(6, 'arnold', '$argon2id$v=19$m=65536,t=4,p=1$YQAAYVJhQgzO70mT0IWX2A$hOcEf1Llwh/1ipCIOT+fxCQyWEUQ/5D1p8ut4hR2hjA', 'kovval.1970.24@gmail.com', NULL, 'full', NULL, '2025-09-13 08:04:24'),
(7, 'Mashandr', '$argon2id$v=19$m=65536,t=4,p=1$exQkxcHfrrjLxrNbzVCnCw$70fZ8EvG5yqCKkkT/CMmk7NDH7xcr3XE0euvUT5bBIE', 'avmashn@gmail.com', NULL, 'limited', NULL, '2025-09-17 13:00:20');

-- --------------------------------------------------------

--
-- Структура таблицы `votes`
--

CREATE TABLE `votes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `track_id` int NOT NULL,
  `voted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `track_id`, `voted_at`) VALUES
(1, 1, 1, '2025-08-27 12:13:08'),
(2, 1, 2, '2025-08-27 13:02:42'),
(3, 1, 5, '2025-08-27 13:18:35'),
(4, 1, 3, '2025-08-27 13:18:51'),
(5, 2, 5, '2025-08-27 13:20:12'),
(6, 2, 6, '2025-08-27 13:20:29'),
(7, 1, 7, '2025-09-13 06:21:12'),
(8, 1, 10, '2025-09-13 09:04:56'),
(9, 1, 11, '2025-09-13 09:08:34'),
(10, 1, 12, '2025-09-13 09:09:34'),
(11, 1, 9, '2025-09-13 09:09:48'),
(12, 1, 13, '2025-09-13 09:35:50'),
(13, 1, 14, '2025-09-14 19:17:42'),
(14, 1, 8, '2025-09-14 19:18:16'),
(15, 1, 15, '2025-09-14 19:20:18'),
(16, 1, 16, '2025-09-14 19:22:17'),
(17, 1, 17, '2025-09-14 19:28:52'),
(18, 1, 18, '2025-09-14 19:31:47'),
(19, 1, 19, '2025-09-14 19:33:26'),
(20, 6, 20, '2025-09-15 18:04:13'),
(21, 1, 20, '2025-09-15 18:06:03'),
(22, 1, 22, '2025-09-17 12:14:37'),
(23, 1, 21, '2025-09-17 12:14:55');

-- --------------------------------------------------------

--
-- Структура таблицы `winnings`
--

CREATE TABLE `winnings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `track_id` int NOT NULL,
  `track_contest_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `place` int NOT NULL,
  `win_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_donations_contest` (`track_contest_id`),
  ADD KEY `fk_donations_lyric_contest` (`lyric_contest_id`);

--
-- Индексы таблицы `lyrics`
--
ALTER TABLE `lyrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_lyrics_contest` (`lyric_contest_id`);

--
-- Индексы таблицы `lyrics_votes`
--
ALTER TABLE `lyrics_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_lyric_vote` (`user_id`,`lyric_id`),
  ADD KEY `lyric_id` (`lyric_id`);

--
-- Индексы таблицы `lyric_contests`
--
ALTER TABLE `lyric_contests`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Индексы таблицы `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_tracks_contest` (`track_contest_id`);

--
-- Индексы таблицы `track_contests`
--
ALTER TABLE `track_contests`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- Индексы таблицы `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_track_vote` (`user_id`,`track_id`),
  ADD KEY `track_id` (`track_id`);

--
-- Индексы таблицы `winnings`
--
ALTER TABLE `winnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `track_id` (`track_id`),
  ADD KEY `fk_winnings_contest` (`track_contest_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `lyrics`
--
ALTER TABLE `lyrics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `lyrics_votes`
--
ALTER TABLE `lyrics_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `lyric_contests`
--
ALTER TABLE `lyric_contests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `track_contests`
--
ALTER TABLE `track_contests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT для таблицы `winnings`
--
ALTER TABLE `winnings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_donations_contest` FOREIGN KEY (`track_contest_id`) REFERENCES `track_contests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_donations_lyric_contest` FOREIGN KEY (`lyric_contest_id`) REFERENCES `lyric_contests` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `lyrics`
--
ALTER TABLE `lyrics`
  ADD CONSTRAINT `fk_lyrics_contest` FOREIGN KEY (`lyric_contest_id`) REFERENCES `lyric_contests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lyrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `lyrics_votes`
--
ALTER TABLE `lyrics_votes`
  ADD CONSTRAINT `lyrics_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lyrics_votes_ibfk_2` FOREIGN KEY (`lyric_id`) REFERENCES `lyrics` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tracks`
--
ALTER TABLE `tracks`
  ADD CONSTRAINT `fk_tracks_contest` FOREIGN KEY (`track_contest_id`) REFERENCES `track_contests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `winnings`
--
ALTER TABLE `winnings`
  ADD CONSTRAINT `fk_winnings_contest` FOREIGN KEY (`track_contest_id`) REFERENCES `track_contests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `winnings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `winnings_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
