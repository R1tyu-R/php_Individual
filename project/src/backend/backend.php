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

function getRedis() {

    $redis = new Redis();
    $redis->connect('redis', 6379);
    return $redis;
}

function insertBook($data) 
{
    $db = getDB();

    $stmt = $db->prepare("
        insert into books 
        (title, author, year, genre, publisher, pages, isbn, rating, description)
        values 
        (:title, :author, :year, :genre, :publisher, :pages, :isbn, :rating, :description)
    ");

    $stmt->execute([
        'title' => $data['title'],
        'author' => $data['author'],
        'year' => $data['year'],
        'genre' => $data['genre'],
        'publisher' => $data['publisher'],
        'pages' => $data['pages'],
        'isbn' => $data['isbn'],
        'rating' => $data['rating'],
        'description' => $data['note']
    ]);

    $redis = getRedis();
    $redis->flushAll();
}


function getBooks() 
{
    $db = getDB();

    $stmt = $db->query("select * from books order by id desc");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBooksSorted($sort = null) 
{
    $redis = getRedis();

    $key = "books_" . ($sort ?? 'default');

    if ($redis->exists($key)) 
    {
        return json_decode($redis->get($key), true);
    }

    $db = getDB();

    $allowed = [
        'title' => 'title asc',
        'rating' => 'rating desc',
        'year' => 'year desc'
    ];

    $order = $allowed[$sort] ?? 'id desc';
    echo 'redis';
    $stmt = $db->query("select * from books order by $order");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $redis->set($key, json_encode($books));
    $redis->expire($key, 60);

    return $books;
}

function getBookById($id) 
{
    $db = getDB();

    $stmt = $db->prepare("select * from books where id = :id");
    $stmt->execute(['id' => $id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function deleteBook($id)
 {
    $db = getDB();

    $stmt = $db->prepare("delete from books where id = :id");
    $stmt->execute(['id' => $id]);
    $redis = getRedis();
    $redis->flushAll();
}


function updateBook($data) 
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
        where id = :id
    ");

    $stmt->execute([
        'id' => $data['id'],
        'title' => $data['title'],
        'author' => $data['author'],
        'year' => $data['year'],
        'genre' => $data['genre'],
        'publisher' => $data['publisher'],
        'pages' => $data['pages'] ?: null,
        'isbn' => $data['isbn'] ?: null,
        'rating' => $data['rating'] ?: null,
        'description' => $data['note']
    ]);
    $redis = getRedis();
    $redis->flushAll();
}
