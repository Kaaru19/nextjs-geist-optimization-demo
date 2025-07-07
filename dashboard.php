<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';

// Handle appointment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_appointment'])) {
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $time = $_POST['time'];

    if ($title && $date && $time) {
        $stmt = $pdo->prepare('INSERT INTO appointments (user_id, title, date, time) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$user_id, $title, $date, $time])) {
            $message = 'Appointment created successfully.';
        } else {
            $message = 'Failed to create appointment.';
        }
    } else {
        $message = 'Please fill all fields.';
    }
}

// Handle appointment deletion
if (isset($_GET['delete'])) {
    $appointment_id = (int)$_GET['delete'];
    // Only allow deletion if user owns the appointment or is admin
    if ($role === 'admin') {
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$appointment_id]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ? AND user_id = ?');
        $stmt->execute([$appointment_id, $user_id]);
    }
    header('Location: dashboard.php');
    exit();
}

// Fetch appointments
if ($role === 'admin') {
    $stmt = $pdo->query('SELECT a.id, a.title, a.date, a.time, u.username FROM appointments a JOIN users u ON a.user_id = u.id ORDER BY a.date, a.time');
    $appointments = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT id, title, date, time FROM appointments WHERE user_id = ? ORDER BY date, time');
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000;
            color: #fff;
            min-height: 100vh;
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
    <div class="max-w-4xl mx-auto bg-white bg-opacity-10 rounded-lg p-6 shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Appointment Tracker</h1>
            <div>
                <span class="mr-4">Hello, <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($role) ?>)</span>
                <a href="logout.php" class="underline hover:text-gray-300">Logout</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="mb-4 text-green-400"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" class="mb-6 space-y-4 bg-black bg-opacity-30 p-4 rounded">
            <h2 class="text-xl font-semibold">Create Appointment</h2>
            <input type="text" name="title" placeholder="Title" required
                class="w-full p-2 rounded bg-black bg-opacity-50 text-white placeholder-gray-400 focus:outline-none" />
            <input type="date" name="date" required
                class="w-full p-2 rounded bg-black bg-opacity-50 text-white placeholder-gray-400 focus:outline-none" />
            <input type="time" name="time" required
                class="w-full p-2 rounded bg-black bg-opacity-50 text-white placeholder-gray-400 focus:outline-none" />
            <button type="submit" name="create_appointment"
                class="bg-white text-black font-semibold py-2 px-4 rounded hover:bg-gray-200 transition">Add Appointment</button>
        </form>
        <h2 class="text-xl font-semibold mb-4">Your Appointments</h2>
        <table class="w-full table-auto border-collapse border border-gray-600">
            <thead>
                <tr class="bg-black bg-opacity-50">
                    <th class="border border-gray-600 px-4 py-2 text-left">Title</th>
                    <th class="border border-gray-600 px-4 py-2 text-left">Date</th>
                    <th class="border border-gray-600 px-4 py-2 text-left">Time</th>
                    <?php if ($role === 'admin'): ?>
                    <th class="border border-gray-600 px-4 py-2 text-left">User</th>
                    <?php endif; ?>
                    <th class="border border-gray-600 px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr class="hover:bg-white hover:bg-opacity-10">
                            <td class="border border-gray-600 px-4 py-2"><?= htmlspecialchars($appt['title']) ?></td>
                            <td class="border border-gray-600 px-4 py-2"><?= htmlspecialchars($appt['date']) ?></td>
                            <td class="border border-gray-600 px-4 py-2"><?= htmlspecialchars($appt['time']) ?></td>
                            <?php if ($role === 'admin'): ?>
                            <td class="border border-gray-600 px-4 py-2"><?= htmlspecialchars($appt['username']) ?></td>
                            <?php endif; ?>
                            <td class="border border-gray-600 px-4 py-2">
                                <a href="dashboard.php?delete=<?= $appt['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this appointment?');"
                                   class="text-red-400 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $role === 'admin' ? 5 : 4 ?>" class="text-center py-4">No appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
