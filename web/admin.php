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

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение всех анкет
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
    <title>Admin Panel</title>
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
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        form label {
            font-weight: bold;
        }
        form input,
        form textarea {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Admin Panel</h1>

<h2>Existing Surveys</h2>
<?php if (count($surveys) > 0): ?>
    <ul>
        <?php foreach ($surveys as $survey): ?>
            <li>
                <strong><?= htmlspecialchars($survey['title']) ?></strong><br>
                <?= htmlspecialchars($survey['description']) ?><br>
                <a href="/edit_survey.php?id=<?= $survey['id'] ?>" class="button">Edit</a>
                <a href="/delete_survey.php?id=<?= $survey['id'] ?>" class="button" style="background-color: #dc3545;" onclick="return confirm('Are you sure?');">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No surveys available.</p>
<?php endif; ?>

<h2>Add New Survey</h2>
<form method="POST" action="/add_survey.php">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>
    <button type="submit">Add Survey</button>
</form>
</body>
</html>

