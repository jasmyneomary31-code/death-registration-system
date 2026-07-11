<?php
session_start();

// Protect this page - if not logged in, send back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Session timeout after 15 minutes of inactivity
$timeout = 900; // 15 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time(); // reset the timer on activity
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 80px auto; }
        .card { padding: 20px; background: #f4f4f4; border-radius: 6px; }
        a { color: #2c3e50; }
        .menu { display: flex; flex-direction: column; gap: 10px; margin: 20px 0; }
        .menu a { padding: 8px 12px; background: white; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
        <p>Role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

        <nav class="menu">
            <a href="register_death.php">+ Register a Death</a>
            <a href="list_deaths.php">View Death Records</a>
            <a href="statistics.php">View Statistics</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="register.php">+ Add New User</a>
            <?php endif; ?>
        </nav>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
