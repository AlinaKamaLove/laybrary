<?php
session_start();
require 'connect.php';

// Проверяем, является ли пользователь библиотекарем
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit();
}

// Получаем ID книги из URL
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Получаем информацию о книге
$stmt = $pdo->prepare("SELECT * FROM books WHERE id=:id");
$stmt->execute(['id' => $id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Обработка формы редактирования книги
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $authorId = intval($_POST['author_id']);
    $categoryId = intval($_POST['category_id']);
    $isbn = trim($_POST['isbn']);
    $year = intval($_POST['publication_year']);
    $copies = intval($_POST['total_copies']);

    try {
        $stmt = $pdo->prepare("UPDATE books SET title=:title, author_id=:author_id, category_id=:category_id, isbn=:isbn, publication_year=:publication_year, total_copies=:total_copies WHERE id=:id");
        $stmt->execute([
            'title' => $title,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'isbn' => $isbn,
            'publication_year' => $year,
            'total_copies' => $copies,
            'id' => $id
        ]);

        $_SESSION['message'] = "Книга успешно обновлена!";
        header("Location: admin_books.php");
        exit();
    } catch (\PDOException $e) {
        echo "<small class='error-message'>Ошибка обновления книги: {$e->getMessage()}</small>";
    }
}

// Получаем список авторов и категорий
$authors = $pdo->query("SELECT * FROM authors")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование книги | Библиотека</title>
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
        
        form {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 4rem auto;
        }
        
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--secondary-color);
            border-radius: 4px;
            outline: none;
            transition: var(--transition);
        }
        
        select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--secondary-color);
            border-radius: 4px;
            outline: none;
            transition: var(--transition);
        }
        
        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 0.8rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        input[type="submit"]:hover {
            background-color: var(--accent-color);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        small.error-message {
            color: red;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 style="text-align:center;">Редактирование книги</h1>
    
    <form method="post">
        <div class="form-group">
            <label for="title">Название книги:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required autofocus />
        </div>
        <div class="form-group">
            <label for="author_id">Автор:</label>
            <select name="author_id" required>
                <option value="">Выберите автора</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?= $author['id'] ?>" <?= ($author['id'] == $book['author_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($author['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Категория:</label>
            <select name="category_id" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($category['id'] == $book['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="isbn">ISBN:</label>
            <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" required />
        </div>
        <div class="form-group">
            <label for="publication_year">Год издания:</label>
            <input type="number" name="publication_year" value="<?= $book['publication_year'] ?>" required />
        </div>
        <div class="form-group">
            <label for="total_copies">Кол-во экземпляров:</label>
            <input type="number" name="total_copies" value="<?= $book['total_copies'] ?>" required />
        </div>
        <input type="submit" value="Сохранить изменения"/>
    </form>
</div>
</body>
</html>