<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

$message = "";

// If already logged in, no need to log in again
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $message = "Please enter both username and password.";
        } else {
            $user = new User();
            $result = $user->login($username, $password);

            if ($result) {
                // Prevent session fixation - regenerate session ID after login
                session_regenerate_id(true);

                $_SESSION['user_id']   = $result['user_id'];
                $_SESSION['full_name'] = $result['full_name'];
                $_SESSION['role']      = $result['role'];
                $_SESSION['last_activity'] = time(); // for session timeout tracking

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid username or password.";
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; }
        input { width: 100%; padding: 8px; margin: 6px 0 14px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; width: 100%; }
        .message { padding: 10px; background: #f8d7da; color: #842029; margin-bottom: 15px; }
        .link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <h2>Login</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="link"><a href="register.php">Don't have an account? Register</a></div>
</body>
</html>
