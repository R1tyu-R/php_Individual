<?php

function getDB()
{
    return new PDO(
        "pgsql:host=postgres;dbname=books_db",
        "user",
        "password",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

function getRedis()
{
    $redis = new Redis();
    $redis->connect('redis', 6379);

    return $redis;
}

function clearBooksCache(): void
{
    $redis = getRedis();
    $redis->flushAll();
}

function getUserByUsername(string $username): ?array
{
    $db = getDB();

    $stmt = $db->prepare("select * from users where username = :username");
    $stmt->execute(['username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

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

function getBooks(): array
{
    $db = getDB();

    $stmt = $db->query("select * from books order by id desc");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

function getBookById(int $id): ?array
{
    $db = getDB();

    $stmt = $db->prepare("select * from books where id = :id");
    $stmt->execute(['id' => $id]);

    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    return $book ?: null;
}

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
