<?php
session_start();
require 'connect.php';

// Проверяем наличие сессии
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем информацию о пользователе
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Информация о заказанных книгах (для читателей)
if ($_SESSION['role'] === 'user') {
    $stmtOrders = $pdo->prepare("SELECT books.*, book_issues.* 
                                 FROM book_issues 
                                 INNER JOIN books ON book_issues.book_id = books.id 
                                 WHERE book_issues.user_id=:user_id");
    $stmtOrders->execute(['user_id' => $userId]);
    $orderedBooks = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
}

// Список всех книг (для библиотекарей)
if ($_SESSION['role'] === 'librarian') {
    $stmtBooks = $pdo->query("SELECT books.*, authors.name AS author_name, categories.name AS category_name 
                              FROM books 
                              LEFT JOIN authors ON books.author_id = authors.id 
                              LEFT JOIN categories ON books.category_id = categories.id");
    $allBooks = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль | Библиотека</title>
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
        
        .profile-section {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 4rem auto;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--secondary-color);
            border-radius: 4px;
            outline: none;
            transition: var(--transition);
        }
        
        button {
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
        
        button:hover {
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        thead th {
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
        }
        
        tbody tr:nth-child(even) {
            background-color: var(--secondary-color);
        }
        
        .action-buttons a {
            color: var(--primary-color);
            text-decoration: underline;
            margin-right: 1rem;
        }
        
        .action-buttons a.delete-link {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 style="text-align:center;">Ваш профиль</h1>
    
    <section class="profile-section">
        <h2>Личная информация</h2>
        <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Электронная почта:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Роль:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
    </section>

    <?php if ($_SESSION['role'] === 'user'): ?>
        <section class="profile-section">
            <h2>Заказанные вами книги</h2>
            <?php if(empty($orderedBooks)): ?>
                <p>У вас нет заказанных книг.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Автор</th>
                            <th>Дата выдачи</th>
                            <th>Срок сдачи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderedBooks as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book['title']) ?></td>
                                <td><?= htmlspecialchars($book['author_name']) ?></td>
                                <td><?= $book['issue_date'] ?></td>
                                <td><?= $book['due_date'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php elseif ($_SESSION['role'] === 'librarian'): ?>
        <section class="profile-section">
            <h2>Управление книгами</h2>
            <a href="add_book.php" class="add-new-btn">Добавить новую книгу</a>
            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Категория</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBooks as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author_name']) ?></td>
                            <td><?= htmlspecialchars($book['category_name']) ?></td>
                            <td class="action-buttons">
                                <a href="edit_book.php?id=<?= $book['id'] ?>">Редактировать</a>
                                <a href="delete_book.php?id=<?= $book['id'] ?>" onclick="return confirm('Удалить книгу?');" class="delete-link">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</div>
</body>
</html>