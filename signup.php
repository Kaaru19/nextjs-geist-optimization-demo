<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $message = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            if ($stmt->execute([$username, $hash, $role])) {
                header('Location: index.php');
                exit();
            } else {
                $message = 'Error creating account.';
            }
        }
    } else {
        $message = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign Up - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000;
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .bg-photo {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="bg-photo">
    <div class="max-w-md w-full bg-white bg-opacity-10 rounded-lg p-8 shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center">Sign Up</h1>
        <?php if ($message): ?>
            <div class="mb-4 text-red-400 text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required
                class="w-full p-3 rounded bg-black bg-opacity-50 text-white placeholder-gray-400 focus:outline-none" />
            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 rounded bg-black bg-opacity-50 text-white placeholder-gray-400 focus:outline-none" />
            <select name="role" class="w-full p-3 rounded bg-black bg-opacity-50 text-white focus:outline-none">
                <option value="user" selected>User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit"
                class="w-full bg-white text-black font-semibold py-3 rounded hover:bg-gray-200 transition">Sign Up</button>
        </form>
        <p class="mt-4 text-center text-gray-300">
            Already have an account? <a href="index.php" class="underline hover:text-white">Login</a>
        </p>
    </div>
</body>
</html>
