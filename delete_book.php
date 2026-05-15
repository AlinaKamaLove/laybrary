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

// Удаляем книгу
try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id=:id");
    $stmt->execute(['id' => $id]);

    $_SESSION['message'] = "Книга удалена успешно!";
    header("Location: admin_books.php");
    exit();
} catch (\PDOException $e) {
    echo "<small class='error-message'>Ошибка удаления книги: {$e->getMessage()}</small>";
}