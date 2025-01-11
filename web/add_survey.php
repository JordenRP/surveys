<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Проверяем, что пользователь — администратор
if (isset($_SESSION['is_admin']) and $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// Подключение к базе данных
$host = 'db';
$dbname = 'survey_db';
$user = 'user';
$password = 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($title) || empty($description)) {
        echo "Title and description are required.";
        exit;
    }

    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Добавление анкеты
        $stmt = $pdo->prepare("INSERT INTO surveys (title, description) VALUES (:title, :description)");
        $stmt->execute([':title' => $title, ':description' => $description]);

        header("Location: /admin.php");
        exit;
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
}

