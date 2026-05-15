<?php
session_start();
require 'connect.php';

// Определяем начальные параметры пагинации
$perPage = 10; // Количество книг на одной странице
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'title';
$orderDirection = isset($_GET['order']) ? trim($_GET['order']) : 'ASC';

// Расчет начальной позиции выборки
$startFrom = ($currentPage - 1) * $perPage;

// Основной запрос с фильтрацией и сортировкой
$sql = "SELECT books.*, authors.name AS author_name, categories.name AS category_name 
        FROM books 
        LEFT JOIN authors ON books.author_id = authors.id 
        LEFT JOIN categories ON books.category_id = categories.id ";

// Добавляем условия фильтрации
if (!empty($searchQuery)) {
    $sql .= "WHERE books.title LIKE :search OR authors.name LIKE :search OR categories.name LIKE :search ";
}

// Сортировка
$sql .= "ORDER BY {$sortBy} {$orderDirection}";

// Ограничиваем количество записей
$sql .= " LIMIT :limit OFFSET :offset";

// Выполняем подготовленный запрос
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $startFrom, PDO::PARAM_INT);

if (!empty($searchQuery)) {
    $searchPattern = "%{$searchQuery}%";
    $stmt->bindParam(':search', $searchPattern, PDO::PARAM_STR);
}

$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем общее количество книг для расчета страниц
$countSql = "SELECT COUNT(*) FROM books ";
if (!empty($searchQuery)) {
    $countSql .= "WHERE title LIKE :search OR authors.name LIKE :search OR categories.name LIKE :search";
}

$countStmt = $pdo->prepare($countSql);
if (!empty($searchQuery)) {
    $countStmt->bindParam(':search', $searchPattern, PDO::PARAM_STR);
}
$countStmt->execute();
$totalBooks = $countStmt->fetchColumn();

// Количество страниц
$pagesCount = ceil($totalBooks / $perPage);

// Генерируем ссылки на страницы
$previousLink = $currentPage > 1 ? "?page=" . ($currentPage - 1) : '#';
$nextLink = $currentPage < $pagesCount ? "?page=" . ($currentPage + 1) : '#';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог книг | Библиотека</title>
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
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: var(--transition);
        }
        
        .pagination a.active {
            background-color: var(--accent-color);
        }
        
        .pagination a.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        
        select.filter-select {
            padding: 0.5rem;
            margin-right: 1rem;
            border-radius: 4px;
            border: 1px solid var(--secondary-color);
        }
        
        input.search-input {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid var(--secondary-color);
            width: 200px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 style="text-align:center;">Каталог книг</h1>

    <!-- Форма фильтров -->
    <form action="" method="get" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <select name="sort_by" class="filter-select">
            <option value="title" <?= ($sortBy == 'title') ? 'selected' : '' ?>>Название</option>
            <option value="author_name" <?= ($sortBy == 'author_name') ? 'selected' : '' ?>>Автор</option>
            <option value="publication_year" <?= ($sortBy == 'publication_year') ? 'selected' : '' ?>>Год публикации</option>
        </select>
        <select name="order" class="filter-select">
            <option value="ASC" <?= ($orderDirection == 'ASC') ? 'selected' : '' ?>>По возрастанию</option>
            <option value="DESC" <?= ($orderDirection == 'DESC') ? 'selected' : '' ?>>По убыванию</option>
        </select>
        <input type="text" name="search" placeholder="Поиск..." class="search-input" value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Применить</button>
    </form>

    <!-- Таблица книг -->
    <table>
        <thead>
            <tr>
                <th>Название</th>
                <th>Автор</th>
                <th>Категория</th>
                <th>Год публикации</th>
                <th>Количество экземпляров</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author_name']) ?></td>
                    <td><?= htmlspecialchars($book['category_name']) ?></td>
                    <td><?= $book['publication_year'] ?></td>
                    <td><?= $book['total_copies'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Блок пагинации -->
    <div class="pagination">
        <a href="<?= $previousLink ?>" class="<?= ($currentPage == 1) ? 'disabled' : '' ?>">Предыдущая</a>
        <?php for ($i = 1; $i <= $pagesCount; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= ($i == $currentPage) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="<?= $nextLink ?>" class="<?= ($currentPage >= $pagesCount) ? 'disabled' : '' ?>">Следующая</a>
    </div>
</div>
</body>
</html>