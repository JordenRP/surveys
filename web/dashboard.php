<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Подключение к базе данных
$host = 'db';
$dbname = 'survey_db';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение информации о пользователе
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }

    // Получение анкет, которые прошёл пользователь
    $stmt = $pdo->prepare("
        SELECT s.id, s.title, s.description
        FROM surveys s
        INNER JOIN survey_responses sr ON s.id = sr.survey_id
        WHERE sr.user_id = :user_id
        GROUP BY s.id, s.title, s.description
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $completed_surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        li strong {
            font-size: 1.2em;
        }
        p {
            color: #555;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>

<h2>Completed Surveys</h2>
<?php if (count($completed_surveys) > 0): ?>
    <ul>
        <?php foreach ($completed_surveys as $survey): ?>
            <li>
                <strong><?= htmlspecialchars($survey['title']) ?></strong><br>
                <p><?= htmlspecialchars($survey['description']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>You haven't completed any surveys yet.</p>
<?php endif; ?>
</body>
</html>
