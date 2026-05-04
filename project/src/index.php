<?php
require 'backend/backend.php';

$db = getDB();
$books = getBooks();
$bookCount = count($books);
$avgRating = 0;
$recentBook = null;

if ($bookCount > 0) {
    $ratings = array_filter(array_column($books, 'rating'), static fn($rating) => $rating !== null && $rating !== '');
    $avgRating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0;
    $recentBook = $books[0];
}
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
        <a href="index.php" class="active">Главная</a>
        <a href="subMenus/library.php">Моя библиотека</a>
        <a href="subMenus/wishlist.php">Желаемое</a>
        <a href="subMenus/form.php">Добавить</a>
    </div>

    <div class="content">
        <section class="hero">
            <article class="hero-card">
                <span class="eyebrow">Книжная атмосфера</span>
                <h1>Тёплая библиотека с мягким древесным характером</h1>
                <p>
                    Каталог теперь можно ощущать как спокойное пространство: светлое дерево, хвойная глубина,
                    стеклянные панели и мягкие акценты, которые не спорят с книгами, а подчёркивают их.
                </p>
                <div class="hero-actions">
                    <a class="button-link" href="subMenus/library.php">Открыть коллекцию</a>
                    <a class="button-link secondary" href="subMenus/form.php">Добавить книгу</a>
                </div>
            </article>

            <article class="stat-card">
                <span>Состояние коллекции</span>
                <strong><?= $bookCount ?></strong>
                <span>книг уже создают настроение вашей домашней полки</span>
            </article>
        </section>

        <section class="feature-grid">
            <article class="panel">
                <h3>Средняя оценка</h3>
                <p><?= $avgRating > 0 ? $avgRating . ' / 10' : 'Пока нет оценок, можно начать с первой любимой книги.' ?></p>
            </article>
            <article class="panel">
                <h3>Последнее пополнение</h3>
                <p><?= $recentBook ? htmlspecialchars($recentBook['title']) . ' — ' . htmlspecialchars($recentBook['author']) : 'Коллекция пока пуста, но это хорошее место для красивого старта.' ?></p>
            </article>
            <article class="panel">
                <h3>Тон сайта</h3>
                <p>Сочетание тёплой древесины, стекла и оттенков соснового леса создаёт мягкое, спокойное послевкусие.</p>
            </article>
        </section>
    </div>

</div>

</body>
</html>
