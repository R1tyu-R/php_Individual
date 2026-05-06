<?php
require_once __DIR__ . '/../backend/backend.php';
require_once __DIR__ . '/../backend/auth.php';

requireAuth();

$sort = $_POST['sort'] ?? null;
$user = currentUser();
$books = getBooksByUser((int) $user['id'], $sort);
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['success']);
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
        <a href="form.php">Добавить книгу</a>
        <a href="../authLogic.php?action=logout">Выйти</a>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            <h2>Моя библиотека</h2>
            <p>Здесь находятся только книги пользователя <?= htmlspecialchars($user['username']) ?>.</p>
        </section>

        <?php if (!empty($errors)) { ?>
            <div class="errorBox">
                <?php foreach ($errors as $fieldErrors) { ?>
                    <?php foreach ($fieldErrors as $error) { ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($success)) { ?>
            <div class="success-box">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php } ?>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="sort-form">
            <button type="submit" name="sort" value="title">По названию</button>
            <button type="submit" name="sort" value="rating">По оценке</button>
            <button type="submit" name="sort" value="year">По году</button>
        </form>

        <?php if (empty($books)) { ?>
            <div class="empty-state">У вас пока нет книг. Добавьте первую запись через страницу добавления.</div>
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
                        <?php if ($editId === (int) $book['id']) { ?>
                            <form method="POST" action="logic.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">

                                <td><input name="title" value="<?= htmlspecialchars($book['title']) ?>" required></td>
                                <td><input name="author" value="<?= htmlspecialchars($book['author']) ?>" required></td>
                                <td><input type="date" name="year" value="<?= htmlspecialchars($book['year']) ?>" required></td>
                                <td><input name="genre" value="<?= htmlspecialchars($book['genre']) ?>"></td>
                                <td><input type="number" name="rating" value="<?= htmlspecialchars($book['rating']) ?>" min="1" max="10" step="0.1"></td>

                                <input type="hidden" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>">
                                <input type="hidden" name="pages" value="<?= htmlspecialchars($book['pages']) ?>">
                                <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                                <input type="hidden" name="note" value="<?= htmlspecialchars($book['description']) ?>">

                                <td><button type="submit">Сохранить</button></td>
                                <td><a href="library.php">Отмена</a></td>
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
