<?php
require_once __DIR__ . '/backend/auth.php';

redirectIfAuth();

$errors = $_SESSION['auth_errors'] ?? [];
$old = $_SESSION['auth_old'] ?? [];

unset($_SESSION['auth_errors'], $_SESSION['auth_old']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="/index.php">Главная</a>
        <a href="/login.php">Вход</a>
        <a href="/register.php" class="active">Регистрация</a>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            <h2>Регистрация</h2>
            <p>Создайте аккаунт, чтобы добавлять книги и видеть только свою коллекцию.</p>
        </section>

        <?php if (!empty($errors)) { ?>
            <div class="errorBox">
                <?php foreach ($errors as $error) { ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form action="/authLogic.php" method="POST" class="base-form">
            <input type="hidden" name="action" value="register">

            <input
                type="text"
                name="username"
                placeholder="Имя пользователя"
                value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                required
            >

            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="repeat_password" placeholder="Повторите пароль" required>

            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>

</body>
</html>
