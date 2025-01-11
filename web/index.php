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

    // Получение списка анкет
    $stmt = $pdo->query("SELECT id, title, description FROM surveys");
    $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Available Surveys</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1 {
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
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Available Surveys</h1>
<?php if (count($surveys) > 0): ?>
    <ul>
        <?php foreach ($surveys as $survey): ?>
            <li>
                <strong><?= htmlspecialchars($survey['title']) ?></strong><br>
                <?= htmlspecialchars($survey['description']) ?><br>
                <a href="/survey.php?id=<?= $survey['id'] ?>" class="button">Take Survey</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No surveys available at the moment.</p>
<?php endif; ?>
</body>
</html>

