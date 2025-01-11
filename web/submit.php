<?php
session_start();
header("Content-Type: application/json");

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header("Location: /login.php");
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

// Получаем данные из POST-запроса
$survey_id = $_POST['survey_id'] ?? null;
if (!$survey_id) {
    http_response_code(400);
    echo json_encode(["message" => "Survey ID is required"]);
    exit;
}

// Собираем ответы на компоненты
$responses = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'component_') === 0) {
        $responses[$key] = $value;
    }
}

// Подключение к базе данных
$host = 'db';
$dbname = 'survey_db';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Сохранение данных в базе
    $stmt = $pdo->prepare("
        INSERT INTO survey_responses (user_id, survey_id, responses) 
        VALUES (:user_id, :survey_id, :responses)
    ");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':survey_id' => $survey_id,
        ':responses' => json_encode($responses),
    ]);

    header("Location: /index.php");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
