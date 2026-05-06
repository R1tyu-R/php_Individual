<?php

require_once __DIR__ . '/backend/homeData.php';

$data = getHomePageData();

$selected = $data['selectedImages'];
$books = $data['books'];
$bookCount = $data['bookCount'];
$avgRating = $data['avgRating'];
$recentBook = $data['recentBook'];
$topRatedBooks = $data['topRatedBooks'];
$isAuth = $data['isAuth'];
$user = $data['user'];
$statsTitle = $data['statsTitle'];
$statsText = $data['statsText'];
$actionPrimaryHref = $data['actionPrimaryHref'];
$actionPrimaryText = $data['actionPrimaryText'];
$actionSecondaryHref = $data['actionSecondaryHref'];
$actionSecondaryText = $data['actionSecondaryText'];
$topTitle = $data['topTitle'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="/index.php" class="active">Главная</a>

        <?php if ($isAuth) { ?>
            <a href="/subMenus/library.php">Моя библиотека</a>
            <a href="/subMenus/form.php">Добавить книгу</a>
            <a href="/authLogic.php?action=logout">Выйти</a>
        <?php } else { ?>
            <a href="/login.php">Войти</a>
            <a href="/register.php">Регистрация</a>
        <?php } ?>
    </div>

    <div class="content">
        <section class="hero">
            <article class="hero-card">
                <span class="eyebrow"><?= $isAuth ? 'Личный кабинет' : 'Гостевой режим' ?></span>
                <h1><?= $isAuth ? 'Здравствуйте, ' . htmlspecialchars($user['username']) . '!' : 'Добро пожаловать в коллекцию книг' ?></h1>
                <p><?= htmlspecialchars($statsText) ?></p>

                <div class="image-container">
                    <?php foreach ($selected as $img) { ?>
                        <img src="<?= toWebPath($img) ?>" class="main-image" alt="Книга">
                    <?php } ?>
                </div>

                <div class="hero-actions">
                    <a class="button-link" href="<?= htmlspecialchars($actionPrimaryHref) ?>"><?= htmlspecialchars($actionPrimaryText) ?></a>
                    <a class="button-link secondary" href="<?= htmlspecialchars($actionSecondaryHref) ?>"><?= htmlspecialchars($actionSecondaryText) ?></a>
                </div>
            </article>

            <article class="stat-card top-books-card">
                <h3><?= htmlspecialchars($topTitle) ?></h3>
                <?php if (empty($topRatedBooks)) { ?>
                    <p>Пока нет оценённых книг.</p>
                <?php } else { ?>
                    <div class="top-books-list">
                        <?php foreach ($topRatedBooks as $index => $book) { ?>
                            <div class="top-book-item">
                                <span class="top-book-rank"><?= $index + 1 ?></span>
                                <div class="top-book-info">
                                    <strong><?= htmlspecialchars($book['title']) ?></strong>
                                    <span><?= htmlspecialchars($book['genre'] ?: '-') ?></span>
                                </div>
                                <span class="top-book-rating"><?= htmlspecialchars(number_format((float) $book['rating'], 1, '.', '')) ?></span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>

        <section class="feature-grid">
            <article class="panel">
                <h3>Средняя оценка</h3>
                <p>
                    <?php if ($avgRating > 0) { ?>
                        <?= htmlspecialchars(number_format($avgRating, 1, '.', '')) ?> / 10
                    <?php } else { ?>
                        Пока нет оценок.
                    <?php } ?>
                </p>
            </article>

            <article class="panel">
                <h3>Последнее пополнение</h3>
                <p>
                    <?php if ($recentBook) { ?>
                        <?= htmlspecialchars($recentBook['title']) ?> - <?= htmlspecialchars($recentBook['author']) ?>
                    <?php } else { ?>
                        Пока в коллекции нет книг.
                    <?php } ?>
                </p>
            </article>

            <article class="panel collection-panel">
                <h3><?= htmlspecialchars($statsTitle) ?></h3>
                <strong class="collection-count"><?= $bookCount ?></strong>
                <p><?= $isAuth ? 'Книг добавлено именно вами' : 'Книг добавлено всеми пользователями' ?></p>
            </article>
        </section>
    </div>

</div>

</body>
</html>
