<?php
session_start();
require 'connect.php';

// Обрабатываем форму регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $role = trim($_POST['role']); // Роль пользователя (user/librarian)

    try {
        // Проверяем существование пользователя по имени или почте
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username=:username OR email=:email");
        $check_stmt->execute(['username' => $username, 'email' => $email]);
        $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            throw new Exception("Пользователь с такими именем или почтой уже существует.");
        }

        // Регистрируем нового пользователя
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password, 'role' => $role]);

        $_SESSION['message'] = "Регистрация прошла успешно! Теперь можете войти.";
        header("Location: login.php");
        exit();
    } catch (\PDOException $e) {
        echo "<small class='error-message'>Ошибка регистрации: {$e->getMessage()}</small>";
    } catch (\Exception $e) {
        echo "<small class='error-message'>Ошибка: {$e->getMessage()}</small>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Библиотека</title>
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
    <h1 style="text-align:center;">Регистрация</h1>
    
    <form method="post">
        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" name="username" required autofocus />
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" required />
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" required />
        </div>
        <div class="form-group">
            <label for="role">Роль:</label>
            <select name="role" required>
                <option value="user">Читатель</option>
                <option value="librarian">Библиотекарь</option>
            </select>
        </div>
        <input type="submit" value="Зарегистрироваться"/>
    </form>
    <p style="margin-top: 1rem; text-align:center;">Уже зарегистрированы? <a href="login.php">Войдите здесь!</a></p>
</div>
</body>
</html>