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

    // Обновление анкеты
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $components = $_POST['components'] ?? [];
        $new_components = $_POST['new_components'] ?? [];

        if (empty($title) || empty($description)) {
            echo "Title and description are required.";
            exit;
        }

        // Обновляем данные анкеты
        $stmt = $pdo->prepare("UPDATE surveys SET title = :title, description = :description WHERE id = :id");
        $stmt->execute([':title' => $title, ':description' => $description, ':id' => $survey_id]);

        // Обновляем существующие компоненты
        foreach ($components as $component_id => $component_data) {
            $stmt = $pdo->prepare("UPDATE survey_components SET label = :label, type = :type, options = :options WHERE id = :id AND survey_id = :survey_id");
            $stmt->execute([
                ':label' => $component_data['label'],
                ':type' => $component_data['type'],
                ':options' => json_encode($component_data['options'] ?? []),
                ':id' => $component_id,
                ':survey_id' => $survey_id
            ]);
        }

        // Добавляем новые компоненты
        foreach ($new_components as $new_component) {
            if (!empty($new_component['label'])) {
                $stmt = $pdo->prepare("INSERT INTO survey_components (survey_id, type, label, options) VALUES (:survey_id, :type, :label, :options)");
                $stmt->execute([
                    ':survey_id' => $survey_id,
                    ':type' => $new_component['type'],
                    ':label' => $new_component['label'],
                    ':options' => json_encode(explode(',', $new_component['options'] ?? '')),
                ]);
            }
        }

        header("Location: /admin.php");
        exit;
    }

    // Получение данных анкеты
    $stmt = $pdo->prepare("SELECT title, description FROM surveys WHERE id = :id");
    $stmt->execute([':id' => $survey_id]);
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$survey) {
        echo "Survey not found.";
        exit;
    }

    // Получение компонентов анкеты
    $stmt = $pdo->prepare("SELECT id, type, label, options FROM survey_components WHERE survey_id = :survey_id");
    $stmt->execute([':survey_id' => $survey_id]);
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
    <title>Edit Survey</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        form {
            max-width: 800px;
            margin: auto;
        }
        .component {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .component label {
            font-weight: bold;
        }
        .component input,
        .component select {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .add-button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-button:hover {
            background-color: #0056b3;
        }
        .update-button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .update-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Edit Survey</h1>
<form id="survey-form" method="POST" action="">
    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($survey['title']) ?>" required><br>
    <label for="description">Description:</label><br>
    <textarea id="description" name="description" required style="width: 100%; padding: 10px;"><?= htmlspecialchars($survey['description']) ?></textarea><br>

    <h2>Components</h2>
    <?php foreach ($components as $component): ?>
        <div class="component">
            <label>Label:</label>
            <input type="text" name="components[<?= $component['id'] ?>][label]" value="<?= htmlspecialchars($component['label']) ?>" required>
            <label>Type:</label>
            <select name="components[<?= $component['id'] ?>][type]" onchange="toggleOptions(this)">
                <option value="text" <?= $component['type'] === 'text' ? 'selected' : '' ?>>Text</option>
                <option value="radio" <?= $component['type'] === 'radio' ? 'selected' : '' ?>>Radio</option>
                <option value="checkbox" <?= $component['type'] === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
            </select>
            <label>Options (comma-separated):</label>
            <?php if ($component['type'] === 'text'): ?>
                <input type="text" name="components[<?= $component['id'] ?>][options]"
                       value="" disabled>
            <?php else: ?>
                <input type="text" name="components[<?= $component['id'] ?>][options]"
                       value="<?= htmlspecialchars(implode(',', json_decode($component['options'], true))) ?>">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <h2>Add New Components</h2>
    <div id="new-components">
        <div class="component">
            <label>Label:</label>
            <input type="text" name="new_components[0][label]" required>
            <label>Type:</label>
            <select name="new_components[0][type]" onchange="toggleOptions(this)">
                <option value="text">Text</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
            </select>
            <label>Options (comma-separated):</label>
            <input type="text" name="new_components[0][options]" disabled>
        </div>
    </div>
    <button type="button" class="add-button" onclick="addNewComponent()">Add Another Component</button><br><br>
    <button type="submit" class="update-button" onclick="return validateForm()">Update Survey</button>
</form>

<script>
    // Управление опциями в зависимости от типа компонента
    function toggleOptions(selectElement) {
        const optionsInput = selectElement.closest('.component').querySelector('input[name$="[options]"]');
        if (selectElement.value === 'text') {
            optionsInput.value = '';
            optionsInput.setAttribute('disabled', 'disabled');
        } else {
            optionsInput.removeAttribute('disabled');
        }
    }

    // Добавление нового компонента
    function addNewComponent() {
        const container = document.getElementById('new-components');
        const index = container.children.length;
        const newComponent = `
        <div class="component">
            <label>Label:</label>
            <input type="text" name="new_components[${index}][label]" required>
            <label>Type:</label>
            <select name="new_components[${index}][type]" onchange="toggleOptions(this)">
                <option value="text">Text</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
            </select>
            <label>Options (comma-separated):</label>
            <input type="text" name="new_components[${index}][options]" disabled>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', newComponent);
    }

    // Валидация формы перед отправкой
    function validateForm() {
        const components = document.querySelectorAll('.component');
        for (const component of components) {
            const labelInput = component.querySelector('input[name$="[label]"]');
            if (!labelInput || labelInput.value.trim() === '') {
                alert('All components must have a label.');
                return false;
            }
        }
        return true;
    }
</script>
</body>
</html>

