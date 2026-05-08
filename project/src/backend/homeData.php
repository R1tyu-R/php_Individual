<?php

require_once __DIR__ . '/backend.php';
require_once __DIR__ . '/auth.php';

/**
 * Собирает веб-пути к изображениям обложек из публичной папки img.
 *
 * @return array Список путей к изображениям, доступных для HTML.
 */
function getImagePaths(): array
{
    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    return array_values(array_map('toWebPath', $images));
}

/**
 * Выбирает два изображения для главной страницы и запоминает следующую позицию в сессии.
 *
 * @param array $images Доступные пути к изображениям.
 * @return array Выбранные пути к изображениям.
 */
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

/**
 * Преобразует серверный путь к файлу в путь для браузера.
 *
 * @param string $path Абсолютный путь внутри корневой папки сайта.
 * @return string Относительный веб-путь для атрибутов src или href.
 */
function toWebPath(string $path): string
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($path));
}

/**
 * Вычисляет среднюю оценку для книг, у которых она указана.
 *
 * @param array $books Строки книг из базы данных.
 * @return float Средняя оценка, округленная до одного знака, или 0, если оценок нет.
 */
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

/**
 * Возвращает самую новую книгу из уже отсортированного списка.
 *
 * @param array $books Строки книг, отсортированные от новых к старым.
 * @return array|null Первая строка книги или null для пустой коллекции.
 */
function getRecentBook(array $books): ?array
{
    return $books[0] ?? null;
}

/**
 * Формирует список книг с лучшими оценками.
 *
 * @param array $books Строки книг из текущей коллекции.
 * @param int $limit Максимальное количество книг в результате.
 * @return array Лучшие книги, отсортированные по оценке по убыванию.
 */
function getTopRatedBooks(array $books, int $limit = 3): array
{
    $filtered = array_values(array_filter(
        $books,
        static fn($book) => $book['rating'] !== null && $book['rating'] !== ''
    ));

    usort($filtered, static fn($a, $b) => (float) $b['rating'] <=> (float) $a['rating']);

    return array_slice($filtered, 0, $limit);
}

/**
 * Подготавливает все данные для главной страницы в гостевом или авторизованном режиме.
 *
 * @return array Тексты, изображения, статистика и списки книг для вывода index.php.
 */
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
