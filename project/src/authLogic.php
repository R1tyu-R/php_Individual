<?php
require_once __DIR__ . '/backend/backend.php';
require_once __DIR__ . '/backend/auth.php';

function saveAuthOld(array $data): void
{
    $_SESSION['auth_old'] = $data;
}

function saveAuthErrors(array $errors): void
{
    $_SESSION['auth_errors'] = $errors;
}

function validateRegisterData(string $username, string $password, string $repeatPassword): array
{
    $errors = [];

    if ($username === '') {
        $errors[] = 'Введите имя пользователя.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Имя пользователя должно содержать минимум 3 символа.';
    }

    if ($password === '') {
        $errors[] = 'Введите пароль.';
    } elseif (strlen($password) < 4) {
        $errors[] = 'Пароль должен содержать минимум 4 символа.';
    }

    if ($password !== $repeatPassword) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (getUserByUsername($username)) {
        $errors[] = 'Пользователь с таким именем уже существует.';
    }

    return $errors;
}

function validateLoginData(string $username, string $password): array
{
    $errors = [];

    if ($username === '') {
        $errors[] = 'Введите имя пользователя.';
    }

    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }

    return $errors;
}

function loginUser(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username']
    ];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: /index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

if ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeat_password'] ?? '';

    saveAuthOld(['username' => $username]);

    $errors = validateRegisterData($username, $password, $repeatPassword);

    if (!empty($errors)) {
        saveAuthErrors($errors);
        header('Location: /register.php');
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        createUser($username, $passwordHash);
    } catch (Exception $e) {
        saveAuthErrors(['Не удалось создать пользователя. Попробуйте другое имя.']);
        header('Location: /register.php');
        exit;
    }

    $_SESSION['auth_success'] = 'Аккаунт создан. Теперь можно войти.';
    unset($_SESSION['auth_old']);

    header('Location: /login.php');
    exit;
}

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    saveAuthOld(['username' => $username]);

    $errors = validateLoginData($username, $password);

    if (!empty($errors)) {
        saveAuthErrors($errors);
        header('Location: /login.php');
        exit;
    }

    $user = getUserByUsername($username);

    if (!$user || !password_verify($password, $user['password'])) {
        saveAuthErrors(['Неверное имя пользователя или пароль.']);
        header('Location: /login.php');
        exit;
    }

    loginUser($user);
    unset($_SESSION['auth_old']);

    header('Location: /index.php');
    exit;
}

header('Location: /index.php');
exit;
