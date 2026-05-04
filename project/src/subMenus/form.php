<?php
session_start();

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['success']);
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
        <a href="library.php">Моя библиотека</a>
        <a href="wishlist.php">Желаемое</a>
        <a href="form.php" class="active">Добавить</a>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            <span class="eyebrow">Новая книга</span>
            <h2>Добавить запись в каталог</h2>
            <p>------</p>
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

        <form action="logic.php" method="POST" class="base-form">
            <input type="hidden" name="action" value="add">

            <input type="text" name="title" placeholder="Название" minlength="5" required>
            <input type="text" name="author" placeholder="Автор" minlength="5" required>
            <input type="date" name="year" required>
            <input type="text" name="genre" placeholder="Жанр" minlength="3">
            <input type="text" name="publisher" placeholder="Издательство" minlength="5">

            <select name="coverType">
                <option value="hard">Твёрдая обложка</option>
                <option value="soft">Мягкая обложка</option>
            </select>

            <input type="number" name="pages" placeholder="Количество страниц">
            <input type="number" name="isbn" placeholder="ISBN">
            <input type="number" name="rating" placeholder="Оценка">
            <textarea name="note" placeholder="Примечание"></textarea>

            <button type="submit">Добавить книгу</button>
        </form>
    </div>
</div>

</body>
</html>
