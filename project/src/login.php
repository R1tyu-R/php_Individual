<?php
require_once __DIR__ . '/backend/auth.php';

redirectIfAuth();

$errors = $_SESSION['auth_errors'] ?? [];
$success = $_SESSION['auth_success'] ?? '';
$old = $_SESSION['auth_old'] ?? [];

unset($_SESSION['auth_errors'], $_SESSION['auth_success'], $_SESSION['auth_old']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="container">

    <div class="nav">
        <a href="/index.php">Главная</a>
        <a href="/login.php" class="active">Вход</a>
        <a href="/register.php">Регистрация</a>
    </div>

    <div class="content">
        <section class="panel" style="margin-bottom: 24px;">
            <h2>Вход в аккаунт</h2>
            <p>Введите логин и пароль, чтобы работать со своей коллекцией книг.</p>
        </section>

        <?php if (!empty($errors)) { ?>
            <div class="errorBox">
                <?php foreach ($errors as $error) { ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($success)) { ?>
            <div class="success-box">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php } ?>

        <form action="/authLogic.php" method="POST" class="base-form">
            <input type="hidden" name="action" value="login">

            <input
                type="text"
                name="username"
                placeholder="Имя пользователя"
                value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                required
            >

            <input type="password" name="password" placeholder="Пароль" required>

            <button type="submit">Войти</button>
        </form>
    </div>
</div>

</body>
</html>
