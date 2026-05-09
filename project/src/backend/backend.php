<?php

/**
 * Создает PDO-подключение к базе данных PostgreSQL.
 *
 * @return PDO Активное подключение к базе данных.
 */
function getDB()
{
    return new PDO(
        "pgsql:host=postgres;dbname=books_db",
        "user",
        "password",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

/**
 * Создает подключение к Redis, который используется для кеширования книг.
 *
 * @return Redis Активное подключение к Redis.
 */
function getRedis()
{
    $redis = new Redis();
    $redis->connect('redis', 6379);

    return $redis;
}

/**
 * Очищает кеш списков книг после изменения коллекции.
 *
 * @return void
 */
function clearBooksCache(): void
{
    $redis = getRedis();
    $redis->flushAll();
}

/**
 * Находит пользователя по имени.
 *
 * @param string $username Имя пользователя из формы входа или регистрации.
 * @return array|null Строка пользователя или null, если пользователь не найден.
 */
function getUserByUsername(string $username): ?array
{
    $db = getDB();

    $stmt = $db->prepare("select * from users where username = :username");
    $stmt->execute(['username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

/**
 * Сохраняет нового пользователя в базе данных.
 *
 * @param string $username Имя нового пользователя.
 * @param string $passwordHash Хеш пароля, созданный функцией password_hash().
 * @return void
 */
function createUser(string $username, string $passwordHash): void
{
    $db = getDB();

    $stmt = $db->prepare("
        insert into users (username, password)
        values (:username, :password)
    ");

    $stmt->execute([
        'username' => $username,
        'password' => $passwordHash
    ]);
}

/**
 * Добавляет новую книгу в коллекцию авторизованного пользователя.
 *
 * @param array $data Поля книги из формы добавления.
 * @param int $userId Идентификатор пользователя, который добавляет книгу.
 * @return void
 */
function insertBook(array $data, int $userId): void
{
    $db = getDB();

    $stmt = $db->prepare("
        insert into books
        (title, author, year, genre, publisher, pages, isbn, rating, description, created_by)
        values
        (:title, :author, :year, :genre, :publisher, :pages, :isbn, :rating, :description, :created_by)
    ");

    $stmt->execute([
        'title' => $data['title'],
        'author' => $data['author'],
        'year' => $data['year'],
        'genre' => $data['genre'],
        'publisher' => $data['publisher'],
        'pages' => $data['pages'] !== '' ? $data['pages'] : null,
        'isbn' => $data['isbn'] !== '' ? $data['isbn'] : null,
        'rating' => $data['rating'] !== '' ? $data['rating'] : null,
        'description' => $data['note'],
        'created_by' => $userId
    ]);

    clearBooksCache();
}

/**
 * Возвращает все книги из базы данных.
 *
 * @return array Список всех строк книг, отсортированный от новых к старым.
 */
function getBooks(): array
{
    $db = getDB();

    $stmt = $db->query("select * from books order by id desc");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Возвращает все книги с необязательной сортировкой и кратким кешированием в Redis.
 *
 * @param string|null $sort Ключ сортировки: title, rating, year или null.
 * @return array Отсортированный список всех книг.
 */
function getBooksSorted(?string $sort = null): array
{
    $redis = getRedis();
    $key = "books_all_" . ($sort ?? 'default');

    if ($redis->exists($key)) {
        return json_decode($redis->get($key), true);
    }

    $db = getDB();
    $allowed = [
        'title' => 'title asc',
        'rating' => 'rating desc nulls last',
        'year' => 'year desc nulls last'
    ];

    $order = $allowed[$sort] ?? 'id desc';
    echo 'redis';
    $stmt = $db->query("select * from books order by $order");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $redis->set($key, json_encode($books));
    $redis->expire($key, 60);

    return $books;
}

/**
 * Возвращает только книги одного пользователя с необязательной сортировкой.
 *
 * @param int $userId Идентификатор владельца коллекции.
 * @param string|null $sort Ключ сортировки: title, rating, year или null.
 * @return array Отсортированный список книг пользователя.
 */
function getBooksByUser(int $userId, ?string $sort = null): array
{
    $redis = getRedis();
    $key = "books_user_{$userId}_" . ($sort ?? 'default');

    if ($redis->exists($key)) {
        return json_decode($redis->get($key), true);
    }

    $db = getDB();
    $allowed = [
        'title' => 'title asc',
        'rating' => 'rating desc nulls last',
        'year' => 'year desc nulls last'
    ];

    $order = $allowed[$sort] ?? 'id desc';
    $stmt = $db->prepare("select * from books where created_by = :user_id order by $order");
    $stmt->execute(['user_id' => $userId]);

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $redis->set($key, json_encode($books));
    $redis->expire($key, 60);

    return $books;
}

/**
 * Находит одну книгу по ее идентификатору.
 *
 * @param int $id Идентификатор книги.
 * @return array|null Строка книги или null, если она не существует.
 */
function getBookById(int $id): ?array
{
    $db = getDB();

    $stmt = $db->prepare("select * from books where id = :id");
    $stmt->execute(['id' => $id]);

    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    return $book ?: null;
}

/**
 * Находит книгу только в том случае, если она принадлежит выбранному пользователю.
 *
 * @param int $bookId Идентификатор книги.
 * @param int $userId Идентификатор пользователя.
 * @return array|null Строка книги или null, если доступ запрещен.
 */
function getBookByIdForUser(int $bookId, int $userId): ?array
{
    $db = getDB();

    $stmt = $db->prepare("
        select * from books
        where id = :book_id and created_by = :user_id
    ");
    $stmt->execute([
        'book_id' => $bookId,
        'user_id' => $userId
    ]);

    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    return $book ?: null;
}

/**
 * Удаляет собственную книгу пользователя из коллекции.
 *
 * @param int $bookId Идентификатор книги.
 * @param int $userId Идентификатор пользователя.
 * @return void
 */
function deleteBookForUser(int $bookId, int $userId): void
{
    $db = getDB();

    $stmt = $db->prepare("
        delete from books
        where id = :book_id and created_by = :user_id
    ");
    $stmt->execute([
        'book_id' => $bookId,
        'user_id' => $userId
    ]);

    clearBooksCache();
}

/**
 * Обновляет книгу, которая принадлежит авторизованному пользователю.
 *
 * @param array $data Измененные поля книги из таблицы библиотеки.
 * @param int $userId Идентификатор текущего пользователя.
 * @return void
 */
function updateBook(array $data, int $userId): void
{
    $db = getDB();

    $stmt = $db->prepare("
        update books set
            title = :title,
            author = :author,
            year = :year,
            genre = :genre,
            publisher = :publisher,
            pages = :pages,
            isbn = :isbn,
            rating = :rating,
            description = :description
        where id = :id and created_by = :user_id
    ");

    $stmt->execute([
        'id' => $data['id'],
        'user_id' => $userId,
        'title' => $data['title'],
        'author' => $data['author'],
        'year' => $data['year'],
        'genre' => $data['genre'],
        'publisher' => $data['publisher'],
        'pages' => $data['pages'] !== '' ? $data['pages'] : null,
        'isbn' => $data['isbn'] !== '' ? $data['isbn'] : null,
        'rating' => $data['rating'] !== '' ? $data['rating'] : null,
        'description' => $data['note']
    ]);

    clearBooksCache();
}
