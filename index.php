<?php
session_start();
require 'connect.php'; // Подключаем файл с подключением к БД

// Получаем 5 последних книг с авторами
try {
    $query = "
       SELECT b.id, b.title, 
       GROUP_CONCAT(a.name SEPARATOR ', ') AS authors,
       b.available_copies
        FROM books b
        JOIN book_authors ba ON b.id = ba.book_id
        JOIN authors a ON ba.author_id = a.id
        GROUP BY b.id
        ORDER BY b.id DESC 
        LIMIT 5
    ";
    $books = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении книг: " . $e->getMessage());
}

// Проверяем авторизацию и роль
$isLoggedIn = isset($_SESSION['user_id']);
$isLibrarian = $isLoggedIn && $_SESSION['role'] === 'librarian';
$isUser = $isLoggedIn && $_SESSION['role'] === 'user';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотечная система</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Все ваши стили остаются без изменений */
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
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: 1px;
            font-family: 'Playfair Display', serif;
            transition: var(--transition);
        }
        
        .logo:hover {
            color: var(--primary-color);
        }
        
        .logo i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        header {
            background-color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        nav ul li {
            margin-left: 1.8rem;
        }
        
        nav ul li a {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        nav ul li a:hover {
            color: var(--primary-color);
        }
        
        nav ul li a i {
            margin-right: 8px;
        }
        
        .orders-count {
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .hero {
            height: 70vh;
            min-height: 500px;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                        url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            margin-bottom: 4rem;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
            margin-left: 1rem;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--dark-color);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .book-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .book-cover {
            height: 350px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--text-light);
        }
        
        .book-details {
            padding: 1.5rem;
        }
        
        .book-details h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }
        
        .book-author {
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .book-publisher {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .footer-column h3 {
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 0.8rem;
        }
        
        .footer-column ul li a {
            color: #ccc;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-column ul li a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #999;
            font-size: 0.9rem;
        }
        
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 1.5rem;
            }
            
            nav ul li {
                margin-left: 1rem;
                margin-right: 1rem;
            }
            
            .hero {
                height: 60vh;
                min-height: 400px;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 1rem;
            }
            
            .btn-outline {
                margin-left: 0;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 0 1.5rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    Библиотека
                </a>
                <nav>
                    <ul>
                        <?php if($isLoggedIn): ?>
                            <li><a href="profile.php"><i class="fas fa-user"></i> Профиль</a></li>
                            <li><a href="books.php"><i class="fas fa-book"></i> Книги</a></li>
                            
                            <?php if($isLibrarian): ?>
                                <li><a href="issues.php"><i class="fas fa-exchange-alt"></i> Выдачи</a></li>
                            <?php else: ?>
                                <li><a href="orders.php"><i class="fas fa-list"></i> Мои заказы</a></li>
                            <?php endif; ?>
                            
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
                        <?php else: ?>
                            <li><a href="books.php"><i class="fas fa-book"></i> Каталог</a></li>
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Вход</a></li>
                            <li><a href="register.php"><i class="fas fa-user-plus"></i> Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Добро пожаловать в нашу библиотеку</h1>
            <p>Откройте для себя мир знаний с нашей коллекцией книг</p>
            <div>
                <a href="books.php" class="btn">Перейти к каталогу</a>
                <?php if(!$isLoggedIn): ?>
                    <a href="register.php" class="btn btn-outline">Зарегистрироваться</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="container">
        <section class="new-books">
            <div class="section-title">
                <h2>Новые поступления</h2>
                <p>Свежие книги, недавно добавленные в нашу коллекцию</p>
            </div>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="book-details">
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="book-author"><?= htmlspecialchars($book['authors']) ?></p>
                            <p>Доступно: <?= $book['available_copies'] ?> экз.</p>
                            <a href="book.php?id=<?= $book['id'] ?>" class="btn">Подробнее</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Каталог</h3>
                    <ul>
                        <li><a href="books.php">Все книги</a></li>
                        <li><a href="#">Новинки</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Контакты</h3>
                    <p><i class="fas fa-map-marker-alt"></i> г. Москва, ул. Книжная, 15</p>
                    <p><i class="fas fa-phone"></i> +7 (495) 123-45-67</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> Библиотечная система</p>
            </div>
        </div>
    </footer>
</body>
</html>