<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAuth(): bool
{
    return isset($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireAuth(): void
{
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }
}

function redirectIfAuth(): void
{
    if (isAuth()) {
        header('Location: /index.php');
        exit;
    }
}
