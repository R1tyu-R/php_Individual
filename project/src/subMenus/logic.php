<?php 
session_start();
require_once __DIR__ . '/../backend/backend.php';

function validateFormData(array $data): array
{
    $errors = [];

    if ($data['title'] === '') 
    {
        $errors['title'][] = "Не указано название";
    }

    if ($data['author'] === '') 
    {
        $errors['author'][] = "Не указан автор";
    }

    $date = DateTime::createFromFormat('Y-m-d', $data['year']);

    if (!$date || $date->format('Y-m-d') !== $data['year']) 
    {
        $errors['year'][] = "Дата указана неверно";
    }

    if ($data['pages'] !== '' && (!is_numeric($data['pages']) || $data['pages'] <= 0)) 
    {
        $errors['pages'][] = "Количество страниц должно быть положительным";
    }

    if (
        $data['rating'] !== '' &&
        (
            !preg_match('/^(10(\.0)?|[1-9](\.[0-9])?)$/', $data['rating']) ||
            (float) $data['rating'] < 1 ||
            (float) $data['rating'] > 10
        )
    ) 
    {
        $errors['rating'][] = "Оценка должна быть от 1 до 10 с шагом 0.1";
    }

    if ($data['isbn'] !== '' && !preg_match('/^[0-9\-]+$/', $data['isbn'])) 
    {
        $errors['isbn'][] = "ISBN не подходит";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $action = $_POST['action'] ?? '';


    if ($action === 'delete') 
    {
        $id = $_POST['id'] ?? null;

        if ($id) 
        {
            deleteBook($id);
        }

        header("Location: library.php");
        exit;
    }

 
    if ($action === 'update') 
    {
        $data = [
            'id' => $_POST['id'],
            'title' => trim($_POST['title'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'year' => $_POST['year'] ?? '',
            'genre' => trim($_POST['genre'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'pages' => $_POST['pages'] ?? '',
            'isbn' => $_POST['isbn'] ?? '',
            'rating' => $_POST['rating'] ?? '',
            'note' => trim($_POST['note'] ?? '')
        ];

        $errors = validateFormData($data);

        if (!empty($errors)) 
        {
            $_SESSION['errors'] = $errors;
            header("Location: library.php?edit=" . $data['id']);
            exit;
        }

        updateBook($data);

        header("Location: library.php");
        exit;
    }

    if ($action === 'add') 
    {
        $data = [
            'title' => trim($_POST["title"] ?? ''),
            'author' => trim($_POST["author"] ?? ''),
            'year'=> $_POST['year'] ?? '',
            'genre'=> trim($_POST['genre'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'pages'=> $_POST['pages'] ?? '',
            'isbn' => trim($_POST['isbn'] ?? ''),
            'rating' => $_POST['rating'] ?? '',
            'note'=> trim($_POST['note'] ?? '')
        ];

        $errors = validateFormData($data);

        if (empty($errors)) 
        {
            try 
            {
                insertBook($data);
                $_SESSION['success'] = "Книга успешно добавлена!";
            } 
            catch (Exception $e) 
            {
                $_SESSION['errors']['db'][] = $e->getMessage();
            }
        } 
        else 
        {
            $_SESSION['errors'] = $errors;
        }

        header('Location: form.php');
        exit;
    }
}
