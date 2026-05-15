<?php
session_start();
require 'connect.php';

// Получаем ID книги из URL
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Проверяем наличие ID
if (!$id) {
    echo '<p>Книга не найдена.</p>';
    exit();
}

// Запрашиваем информацию о книге
try {
    $stmt = $pdo->prepare("
        SELECT books.*, authors.name AS author_name, categories.name AS category_name
        FROM books
        LEFT JOIN authors ON books.author_id = authors.id
        LEFT JOIN categories ON books.category_id = categories.id
        WHERE books.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo '<p>Книга не найдена.</p>';
        exit();
    }
} catch (\PDOException $e) {
    echo "<small class='error-message'>Ошибка загрузки книги: {$e->getMessage()}</small>";
    exit();
}

// Подготовка стилей и разметки
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Книга | Библиотека</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5A8F7B;
            --secondary-color: #D4EDE1;
            --dark-color: #1a1a1a;
            --light-color: #F8FBF9;
            --accent-color: #7FB685;
            --text-color: #333;
            --text-light: #777;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--text-color);
            line-height: 1.7;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        section.book-info {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 4rem auto;
        }
        
        img.book-cover {
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        h1.book-title {
            font-size: 2.2rem;
            font-family: 'Playfair Display', serif;
            margin-bottom: 1rem;
        }
        
        .book-detail {
            margin-bottom: 1.5rem;
        }
        
        .book-detail strong {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .book-detail span {
            display: block;
            margin-top: 0.5rem;
        }
        
        .btn-back {
            display: block;
            padding: 0.8rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            width: fit-content;
            margin: 0 auto;
        }
        
        .btn-back:hover {
            background-color: var(--accent-color);
        }
    </style>
</head>
<body>
<div class="container">
    <section class="book-info">
        <img src="https://via.placeholder.com/300x400.png?text=Book+Covers" alt="Cover of <?= htmlspecialchars($book['title']) ?>" class="book-cover">
        <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
        
        <div class="book-detail">
            <strong>Автор:</strong>
            <span><?= htmlspecialchars($book['author_name']) ?></span>
        </div>
        
        <div class="book-detail">
            <strong>Категория:</strong>
            <span><?= htmlspecialchars($book['category_name']) ?></span>
        </div>
        
        <div class="book-detail">
            <strong>ISBN:</strong>
            <span><?= htmlspecialchars($book['isbn']) ?></span>
        </div>
        
        <div class="book-detail">
            <strong>Год издания:</strong>
            <span><?= $book['publication_year'] ?></span>
        </div>
        
        <div class="book-detail">
            <strong>Экземпляры:</strong>
            <span><?= $book['total_copies'] ?> (доступно: <?= $book['available_copies'] ?>)</span>
        </div>
        
        <a href="books.php" class="btn-back">Вернуться к списку книг</a>
    </section>
</div>
</body>
</html>