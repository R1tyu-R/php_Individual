<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Проверяет, сохранен ли пользователь в текущей сессии.
 *
 * @return bool True, если пользователь авторизован.
 */
function isAuth(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Возвращает данные авторизованного пользователя из сессии.
 *
 * @return array|null Данные пользователя с id и username или null для гостя.
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Разрешает доступ только авторизованным пользователям.
 *
 * @return void Перенаправляет гостей на страницу входа.
 */
function requireAuth(): void
{
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Уводит уже авторизованного пользователя со страниц входа и регистрации.
 *
 * @return void Перенаправляет авторизованных пользователей на главную страницу.
 */
function redirectIfAuth(): void
{
    if (isAuth()) {
        header('Location: /index.php');
        exit;
    }
}
