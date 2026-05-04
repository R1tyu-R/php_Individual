
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="index.php">Главная</a>
        <a href="subMenus/library.php">Моя библиотека</a>
        <a href="subMenus/wishlist.php">Желаемое</a>
        <a href="subMenus/form.php">Добавить</a>
    </div>

    <div class="content">
        <h2>Добро пожаловать в каталог книг </h2>

        <?php
        require 'backend/backend.php';

        $db = getDB();

        echo "DB OK";
        ?>
    </div>

</div>

</body>
</html>