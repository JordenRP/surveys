<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Проверяем, что пользователь — администратор
if ($_SESSION['is_admin'] !== true) {
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

    // Данные для графика регистрации пользователей
    $stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
    $user_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Данные для графика пройденных анкет
    $stmt = $pdo->query("SELECT DATE(completed_at) as date, COUNT(*) as count FROM survey_responses GROUP BY DATE(completed_at) ORDER BY DATE(completed_at)");
    $survey_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Данные для популярности анкет
    $stmt = $pdo->query("SELECT s.title, COUNT(r.id) as count FROM surveys s JOIN survey_responses r ON s.id = r.survey_id GROUP BY s.title ORDER BY count DESC");
    $popularity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Survey Statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .chart-container {
            width: 80%;
            margin: 30px auto;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Survey Statistics</h1>

<div class="chart-container">
    <canvas id="userChart"></canvas>
</div>
<div class="chart-container">
    <canvas id="surveyChart"></canvas>
</div>
<div class="chart-container">
    <canvas id="popularityChart"></canvas>
</div>

<script>
    // Данные для графика пользователей
    const userLabels = <?= json_encode(array_column($user_data, 'date')) ?>;
    const userCounts = <?= json_encode(array_column($user_data, 'count')) ?>;

    const userChartCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userChartCtx, {
        type: 'line',
        data: {
            labels: userLabels,
            datasets: [{
                label: 'User Registrations',
                data: userCounts,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                }
            }
        }
    });

    // Данные для графика пройденных анкет
    const surveyLabels = <?= json_encode(array_column($survey_data, 'date')) ?>;
    const surveyCounts = <?= json_encode(array_column($survey_data, 'count')) ?>;

    const surveyChartCtx = document.getElementById('surveyChart').getContext('2d');
    new Chart(surveyChartCtx, {
        type: 'line',
        data: {
            labels: surveyLabels,
            datasets: [{
                label: 'Surveys Completed',
                data: surveyCounts,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                }
            }
        }
    });

    // Данные для популярности анкет
    const popularityLabels = <?= json_encode(array_column($popularity_data, 'title')) ?>;
    const popularityCounts = <?= json_encode(array_column($popularity_data, 'count')) ?>;

    const popularityChartCtx = document.getElementById('popularityChart').getContext('2d');
    new Chart(popularityChartCtx, {
        type: 'bar',
        data: {
            labels: popularityLabels,
            datasets: [{
                label: 'Survey Popularity',
                data: popularityCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                }
            }
        }
    });
</script>
</body>
</html>
