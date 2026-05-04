<?php
require_once __DIR__ . '/../backend/backend.php';

$sort = $_POST['sort'] ?? null;
$books = getBooksSorted($sort);
$editId = $_GET['edit'] ?? null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моя библиотека</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="../index.php">Главная</a>
        <a href="library.php" class="active">Моя библиотека</a>
        <a href="wishlist.php">Желаемое</a>
        <a href="form.php">Добавить</a>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            
            <h2>Моя библиотека</h2>
            <p>Указаны только книги из основного шкафа</p>
        </section>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="sort-form">
            <button type="submit" name="sort" value="title">По названию</button>
            <button type="submit" name="sort" value="rating">По оценке</button>
            <button type="submit" name="sort" value="year">По году</button>
        </form>

        <?php if (empty($books)) { ?>
            <div class="empty-state">Пока здесь нет книг. Можно начать с добавления первой записи в каталог.</div>
        <?php } else { ?>
        <table>
            <tr>
                <th>Название</th>
                <th>Автор</th>
                <th>Год</th>
                <th>Жанр</th>
                <th>Оценка</th>
                <th>Удалить</th>
                <th>Редактировать</th>
            </tr>

            <?php foreach ($books as $book) { ?>
            <tr>

            <?php if ($editId == $book['id']) { ?>

                <form method="POST" action="logic.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">

                    <td><input name="title" value="<?= htmlspecialchars($book['title']) ?>"></td>
                    <td><input name="author" value="<?= htmlspecialchars($book['author']) ?>"></td>
                    <td><input type="date" name="year" value="<?= htmlspecialchars($book['year']) ?>"></td>
                    <td><input name="genre" value="<?= htmlspecialchars($book['genre']) ?>"></td>
                    <td><input type="number" name="rating" value="<?= htmlspecialchars($book['rating']) ?>" min="1" max="10" step="0.1"></td>

                    <td>
                        <button type="submit">Сохранить</button>
                    </td>

                    <td>
                        <a href="library.php">Отмена</a>
                    </td>
                </form>

            <?php } else { ?>

                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= htmlspecialchars($book['year']) ?></td>
                <td><?= htmlspecialchars($book['genre']) ?></td>
                <td><?= $book['rating'] !== null && $book['rating'] !== '' ? htmlspecialchars(number_format((float) $book['rating'], 1, '.', '')) : '' ?></td>

                <td>
                    <form method="POST" action="logic.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                        <button type="submit">Удалить</button>
                    </form>
                </td>

                <td>
                    <a href="library.php?edit=<?= (int) $book['id'] ?>">Редактировать</a>
                </td>

            <?php } ?>

            </tr>
            <?php } ?>

        </table>
        <?php } ?>
    </div>

</div>

</body>
</html>
