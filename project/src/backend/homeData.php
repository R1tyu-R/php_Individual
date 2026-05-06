<?php

require_once __DIR__ . '/backend.php';
require_once __DIR__ . '/auth.php';

function getImagePaths(): array
{
    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    return array_values(array_map('toWebPath', $images));
}

function getSelectedImages(array $images): array
{
    $total = count($images);

    if ($total < 2) {
        return $images;
    }

    if (!isset($_SESSION['img_index'])) {
        $_SESSION['img_index'] = 0;
    }

    $i = $_SESSION['img_index'];

    $selected = [
        $images[$i % $total],
        $images[($i + 1) % $total]
    ];

    $_SESSION['img_index'] = ($i + 2) % $total;

    return $selected;
}

function toWebPath(string $path): string
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($path));
}

function getAverageRating(array $books): float
{
    $ratings = array_filter(
        array_column($books, 'rating'),
        static fn($rating) => $rating !== null && $rating !== ''
    );

    if (count($ratings) === 0) {
        return 0;
    }

    return round(array_sum($ratings) / count($ratings), 1);
}

function getRecentBook(array $books): ?array
{
    return $books[0] ?? null;
}

function getTopRatedBooks(array $books, int $limit = 3): array
{
    $filtered = array_values(array_filter(
        $books,
        static fn($book) => $book['rating'] !== null && $book['rating'] !== ''
    ));

    usort($filtered, static fn($a, $b) => (float) $b['rating'] <=> (float) $a['rating']);

    return array_slice($filtered, 0, $limit);
}

function getHomePageData(): array
{
    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $user = currentUser();
    $isAuthUser = isAuth();

    if ($isAuthUser) {
        $books = getBooksByUser((int) $user['id']);
        $statsTitle = 'Моя коллекция';
        $statsText = 'На главной показаны только ваши книги, ваши оценки и ваши последние добавления.';
        $actionPrimaryHref = '/subMenus/library.php';
        $actionPrimaryText = 'Открыть мою библиотеку';
        $actionSecondaryHref = '/subMenus/form.php';
        $actionSecondaryText = 'Добавить книгу';
        $topTitle = 'Мои лучшие книги';
    } else {
        $books = getBooks();
        $statsTitle = 'Общая коллекция';
        $statsText = 'Гость видит только общую статистику по всем книгам и может зарегистрироваться.';
        $actionPrimaryHref = '/login.php';
        $actionPrimaryText = 'Войти';
        $actionSecondaryHref = '/register.php';
        $actionSecondaryText = 'Регистрация';
        $topTitle = 'Лучшие книги коллекции';
    }

    return [
        'isAuth' => $isAuthUser,
        'user' => $user,
        'selectedImages' => getSelectedImages($images),
        'imagePaths' => getImagePaths(),
        'books' => $books,
        'bookCount' => count($books),
        'avgRating' => getAverageRating($books),
        'recentBook' => getRecentBook($books),
        'topRatedBooks' => getTopRatedBooks($books),
        'statsTitle' => $statsTitle,
        'statsText' => $statsText,
        'actionPrimaryHref' => $actionPrimaryHref,
        'actionPrimaryText' => $actionPrimaryText,
        'actionSecondaryHref' => $actionSecondaryHref,
        'actionSecondaryText' => $actionSecondaryText,
        'topTitle' => $topTitle
    ];
}
