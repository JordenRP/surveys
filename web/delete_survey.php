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

$survey_id = $_GET['id'] ?? null;

if (!$survey_id) {
    echo "Survey not found.";
    exit;
}

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Удаление анкеты
    $stmt = $pdo->prepare("DELETE FROM surveys WHERE id = :id");
    $stmt->execute([':id' => $survey_id]);

    header("Location: /admin.php");
    exit;
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>
