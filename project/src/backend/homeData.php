<?php

require_once __DIR__ . '/backend.php';

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

    session_start();

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
        static fn($r) => $r !== null && $r !== ''
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
        static fn($b) => $b['rating'] !== null && $b['rating'] !== ''
    ));

    usort($filtered, static fn($a, $b) =>
        (float)$b['rating'] <=> (float)$a['rating']
    );

    return array_slice($filtered, 0, $limit);
}

function getHomePageData(): array
{
    $books = getBooks();

    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    return [
        'images' => $images,
        'selectedImages' => getSelectedImages($images),
        'imagePaths' => getImagePaths(),
        'books' => $books,
        'bookCount' => count($books),
        'avgRating' => getAverageRating($books),
        'recentBook' => getRecentBook($books),
        'topRatedBooks' => getTopRatedBooks($books)
    ];
}