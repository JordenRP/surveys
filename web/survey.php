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

$survey_id = $_GET['id'] ?? null;

if (!$survey_id) {
    echo "Survey not found.";
    exit;
}

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение данных анкеты
    $stmt = $pdo->prepare("SELECT title FROM surveys WHERE id = :id");
    $stmt->execute([':id' => $survey_id]);
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$survey) {
        echo "Survey not found.";
        exit;
    }

    // Получение компонентов анкеты
    $stmt = $pdo->prepare("SELECT * FROM survey_components WHERE survey_id = :id");
    $stmt->execute([':id' => $survey_id]);
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title><?= htmlspecialchars($survey['title']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input[type="text"],
        input[type="radio"],
        input[type="checkbox"] {
            margin-top: 5px;
            margin-bottom: 15px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .component {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1><?= htmlspecialchars($survey['title']) ?></h1>
<form method="POST" action="/submit.php">
    <!-- Скрытое поле для передачи survey_id -->
    <input type="hidden" name="survey_id" value="<?= htmlspecialchars($survey_id) ?>">

    <?php foreach ($components as $component): ?>
        <div class="component">
            <label><?= htmlspecialchars($component['label']) ?></label>
            <?php if ($component['type'] === 'text'): ?>
                <input type="text" name="component_<?= $component['id'] ?>">
            <?php elseif ($component['type'] === 'radio'): ?>
                <?php foreach (json_decode($component['options'], true) as $option): ?>
                    <input type="radio" name="component_<?= $component['id'] ?>" value="<?= htmlspecialchars($option) ?>">
                    <?= htmlspecialchars($option) ?><br>
                <?php endforeach; ?>
            <?php elseif ($component['type'] === 'checkbox'): ?>
                <?php foreach (json_decode($component['options'], true) as $option): ?>
                    <input type="checkbox" name="component_<?= $component['id'] ?>[]" value="<?= htmlspecialchars($option) ?>">
                    <?= htmlspecialchars($option) ?><br>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <button type="submit">Submit</button>
</form>
</body>
</html>
