<?php
$images = glob($_SERVER['DOCUMENT_ROOT'] . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

$total = count($images);

if ($total < 2) {
    $selected = $images;
} else {
    session_start();

    if (!isset($_SESSION['img_index'])) {
        $_SESSION['img_index'] = 0;
    }

    $i = $_SESSION['img_index'];

    $selected = [
        $images[$i % $total],
        $images[($i + 1) % $total]
    ];

    $_SESSION['img_index'] = ($i + 2) % $total;
}

function toWebPath($path) {
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($path));
}

$imagePaths = array_values(array_map('toWebPath', $images));

require 'backend/backend.php';


$db = getDB();
$books = getBooks();
$bookCount = count($books);
$avgRating = 0;
$recentBook = null;
$topRatedBooks = [];

if ($bookCount > 0) {
    $ratings = array_filter(array_column($books, 'rating'), static fn($rating) => $rating !== null && $rating !== '');
    $avgRating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0;
    $recentBook = $books[0];

    $topRatedBooks = array_values(array_filter($books, static fn($book) => $book['rating'] !== null && $book['rating'] !== ''));
    usort($topRatedBooks, static fn($a, $b) => (float) $b['rating'] <=> (float) $a['rating']);
    $topRatedBooks = array_slice($topRatedBooks, 0, 3);
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
                
                <div class="image-container">
                    <?php foreach ($selected as $img): ?>
                        <img src="<?= toWebPath($img) ?>" class="main-image">
                    <?php endforeach; ?>
                </div>
                
                <div class="hero-actions">
                    <a class="button-link" href="subMenus/library.php">Открыть коллекцию</a>
                    <a class="button-link secondary" href="subMenus/form.php">Добавить книгу</a>
                </div>
            </article>

            <article class="stat-card top-books-card">
                <h3>Топ 3 книги</h3>
                <?php if (empty($topRatedBooks)): ?>
                    <p>Пока нет оценок для рейтинга.</p>
                <?php else: ?>
                    <div class="top-books-list">
                        <?php foreach ($topRatedBooks as $index => $book): ?>
                            <div class="top-book-item">
                                <span class="top-book-rank"><?= $index + 1 ?></span>
                                <div class="top-book-info">
                                    <strong><?= htmlspecialchars($book['title']) ?></strong>
                                    <span><?= htmlspecialchars($book['genre'] ?: '—') ?></span>
                                </div>
                                <span class="top-book-rating"><?= htmlspecialchars(number_format((float) $book['rating'], 1, '.', '')) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
            <article class="panel collection-panel">
                <h3>Состояние коллекции</h3>
                <strong class="collection-count"><?= $bookCount ?></strong>
                <p>Книг лежит в шкафу</p>
            </article>
        </section>
    </div>

</div>

</body>
</html>
