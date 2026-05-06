<?php
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/backend.php';

requireAuth();

function validateFormData(array $data): array
{
    $errors = [];

    if ($data['title'] === '') {
        $errors['title'][] = 'Не указано название';
    }

    if ($data['author'] === '') {
        $errors['author'][] = 'Не указан автор';
    }

    $date = DateTime::createFromFormat('Y-m-d', $data['year']);

    if (!$date || $date->format('Y-m-d') !== $data['year']) {
        $errors['year'][] = 'Дата указана неверно';
    }

    if ($data['pages'] !== '' && (!is_numeric($data['pages']) || (int) $data['pages'] <= 0)) {
        $errors['pages'][] = 'Количество страниц должно быть положительным';
    }

    if (
        $data['rating'] !== '' &&
        (
            !preg_match('/^(10(\.0)?|[1-9](\.[0-9])?)$/', $data['rating']) ||
            (float) $data['rating'] < 1 ||
            (float) $data['rating'] > 10
        )
    ) {
        $errors['rating'][] = 'Оценка должна быть от 1 до 10 с шагом 0.1';
    }

    if ($data['isbn'] !== '' && !preg_match('/^[0-9\-]+$/', $data['isbn'])) {
        $errors['isbn'][] = 'ISBN должен содержать только цифры и дефис';
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$user = currentUser();
$userId = (int) $user['id'];

if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($id > 0) {
        $book = getBookByIdForUser($id, $userId);

        if ($book) {
            deleteBookForUser($id, $userId);
            $_SESSION['success'] = 'Книга удалена.';
        } else {
            $_SESSION['errors']['book'][] = 'Эту книгу нельзя удалить.';
        }
    }

    header('Location: library.php');
    exit;
}

if ($action === 'update') {
    $data = [
        'id' => isset($_POST['id']) ? (int) $_POST['id'] : 0,
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'year' => $_POST['year'] ?? '',
        'genre' => trim($_POST['genre'] ?? ''),
        'publisher' => trim($_POST['publisher'] ?? ''),
        'pages' => $_POST['pages'] ?? '',
        'isbn' => trim($_POST['isbn'] ?? ''),
        'rating' => $_POST['rating'] ?? '',
        'note' => trim($_POST['note'] ?? '')
    ];

    $book = getBookByIdForUser($data['id'], $userId);

    if (!$book) {
        $_SESSION['errors']['book'][] = 'Эту книгу нельзя редактировать.';
        header('Location: library.php');
        exit;
    }

    $errors = validateFormData($data);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: library.php?edit=' . $data['id']);
        exit;
    }

    updateBook($data, $userId);
    $_SESSION['success'] = 'Книга обновлена.';

    header('Location: library.php');
    exit;
}

if ($action === 'add') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'year' => $_POST['year'] ?? '',
        'genre' => trim($_POST['genre'] ?? ''),
        'publisher' => trim($_POST['publisher'] ?? ''),
        'pages' => $_POST['pages'] ?? '',
        'isbn' => trim($_POST['isbn'] ?? ''),
        'rating' => $_POST['rating'] ?? '',
        'note' => trim($_POST['note'] ?? '')
    ];

    $errors = validateFormData($data);

    if (empty($errors)) {
        try {
            insertBook($data, $userId);
            $_SESSION['success'] = 'Книга успешно добавлена.';
        } catch (Exception $e) {
            $_SESSION['errors']['db'][] = 'Не удалось сохранить книгу.';
        }
    } else {
        $_SESSION['errors'] = $errors;
    }

    header('Location: form.php');
    exit;
}

header('Location: ../index.php');
exit;
