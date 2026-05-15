<?php
session_start();
require 'connect.php';

// Первым делом выполняйте логику входа
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username=:username OR email=:username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // ПЕРЕНАПРАВЛЕНИЕ ДОЛЖНО ПРОИСХОДИТЬ РАНЬШЕ ВСЕГО!
            header("Location: index.php");
            exit();
        } else {
            $error_message = "<small class='error-message'>Неверное имя пользователя или пароль</small>";
        }
    } catch (\PDOException $e) {
        $error_message = "<small class='error-message'>Ошибка входа: {$e->getMessage()}</small>";
    }
}

// Только после попытки входа можем вывести HTML
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Заголовок и стили -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Библиотека</title>
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
    <h1 style="text-align:center;">Вход в аккаунт</h1>
    
    <?php if(isset($error_message)): ?>
        <?= $error_message ?>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="username">Имя пользователя или Email:</label>
            <input type="text" name="username" required autofocus />
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" required />
        </div>
        <input type="submit" value="Войти"/>
    </form>
    <p style="margin-top: 1rem; text-align:center;">Нет аккаунта? <a href="register.php">Зарегистрируйтесь здесь!</a></p>
</div>
</body>
</html>