<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

$message = "";

// Only process when the form is submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Basic CSRF protection check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        $fullName = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];
        $role     = $_POST['role'];

        if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
            $message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } elseif (strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";
        } else {
            $user = new User();
            $result = $user->register($fullName, $username, $email, $password, $role);

            $message = ($result === true) ? "Registration successful! You can now log in." : $result;
        }
    }
}

// Generate a fresh CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 60px auto; }
        input, select { width: 100%; padding: 8px; margin: 6px 0 14px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; background: #eee; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Register New User</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role">
            <option value="hospital_staff">Hospital Staff</option>
            <option value="registrar">Registrar</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Register</button>
    </form>
</body>
</html>
