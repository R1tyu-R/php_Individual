<?php
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/backend.php';

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$user = currentUser();
$books = [];

unset($_SESSION['errors'], $_SESSION['success']);

if ($user) {
    $books = getBooksByUser((int) $user['id']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить книгу</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="../index.php">Главная</a>

        <?php if ($user) { ?>
            <a href="library.php">Моя библиотека</a>
            <a href="form.php" class="active">Добавить книгу</a>
            <a href="../authLogic.php?action=logout">Выйти</a>
        <?php } else { ?>
            <a href="../login.php">Войти</a>
            <a href="../register.php">Регистрация</a>
        <?php } ?>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            <h2>Добавление книги</h2>
            <p>Здесь можно добавить книгу в свою личную коллекцию.</p>
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

        <?php if (!$user) { ?>
            <div class="empty-state" style="margin-bottom: 24px;">
                Добавлять книги может только авторизованный пользователь. Войдите в аккаунт или создайте новый.
            </div>

            <div class="hero-actions">
                <a class="button-link" href="../login.php">Войти</a>
                <a class="button-link secondary" href="../register.php">Регистрация</a>
            </div>
        <?php } else { ?>
            <form action="logic.php" method="POST" class="base-form">
                <input type="hidden" name="action" value="add">

                <input type="text" name="title" placeholder="Название" minlength="2" required>
                <input type="text" name="author" placeholder="Автор" minlength="2" required>
                <input type="date" name="year" required>
                <input type="text" name="genre" placeholder="Жанр">
                <input type="text" name="publisher" placeholder="Издательство">

                <input type="number" name="pages" placeholder="Количество страниц" min="1">
                <input type="text" name="isbn" placeholder="ISBN">
                <input type="number" name="rating" placeholder="Оценка" min="1" max="10" step="0.1">
                <textarea name="note" placeholder="Примечание"></textarea>

                <button type="submit">Добавить книгу</button>
            </form>

            <section class="panel" style="margin-top: 28px;">
                <h2>Мои добавленные книги</h2>
                <p>На этой странице показаны только ваши книги.</p>
            </section>

            <?php if (empty($books)) { ?>
                <div class="empty-state" style="margin-top: 18px;">
                    Вы пока ещё не добавили ни одной книги.
                </div>
            <?php } else { ?>
                <table style="margin-top: 18px;">
                    <tr>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Год</th>
                        <th>Жанр</th>
                        <th>Оценка</th>
                    </tr>

                    <?php foreach ($books as $book) { ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['year']) ?></td>
                            <td><?= htmlspecialchars($book['genre']) ?></td>
                            <td><?= $book['rating'] !== null && $book['rating'] !== '' ? htmlspecialchars(number_format((float) $book['rating'], 1, '.', '')) : '' ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        <?php } ?>
    </div>
</div>

</body>
</html>
