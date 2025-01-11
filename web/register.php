<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        // Подключение к базе данных
        $host = 'db';
        $dbname = 'survey_db';
        $user = 'user';
        $password_db = 'password';

        try {
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password_db);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Хеширование пароля
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Сохранение пользователя в базе
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => $passwordHash
            ]);

            $success = "Registration successful. You can now log in.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) { // Unique constraint violation
                $error = "Username already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            max-width: 400px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        form label {
            font-weight: bold;
        }
        form input {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            width: 100%;
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
        p {
            text-align: center;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Register</h1>
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if (isset($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<form method="POST" action="">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Register</button>
</form>
<p><a href="/login.php">Already have an account? Log in</a></p>
</body>
</html>

