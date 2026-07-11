<?php
session_start();
require_once __DIR__ . '/../classes/Deceased.php';
require_once __DIR__ . '/../config/Database.php';

// Protect page - must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch causes of death for the dropdown (from our normalized lookup table)
$db = Database::getInstance()->getConnection();
$causes = $db->query("SELECT cause_id, description FROM causes_of_death ORDER BY description")
             ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        $fullName     = trim($_POST['full_name']);
        $gender       = $_POST['gender'];
        $dob          = $_POST['date_of_birth'];
        $dod          = $_POST['date_of_death'];
        $place        = trim($_POST['place_of_death']);
        $nationalId   = trim($_POST['national_id']);
        $causeId      = $_POST['cause_id'];

        if (empty($fullName) || empty($dob) || empty($dod) || empty($place) || empty($nationalId)) {
            $message = "All fields are required.";
        } else {
            $deceased = new Deceased();
            $result = $deceased->register(
                $fullName, $gender, $dob, $dod, $place, $nationalId, $causeId, $_SESSION['user_id']
            );

            $message = ($result === true)
                ? "Death record registered successfully! Awaiting Registrar approval."
                : $result;
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Death - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 450px; margin: 40px auto; }
        input, select { width: 100%; padding: 8px; margin: 6px 0 14px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; background: #eee; margin-bottom: 15px; }
        .row { display: flex; gap: 10px; }
        .row > div { flex: 1; }
    </style>
</head>
<body>
    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    <h2>Register a Death</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Full Name of Deceased</label>
        <input type="text" name="full_name" required>

        <div class="row">
            <div>
                <label>Gender</label>
                <select name="gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div>
                <label>National ID</label>
                <input type="text" name="national_id" required>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" required>
            </div>
            <div>
                <label>Date of Death</label>
                <input type="date" name="date_of_death" required>
            </div>
        </div>

        <label>Place of Death</label>
        <input type="text" name="place_of_death" required>

        <label>Cause of Death</label>
        <select name="cause_id" required>
            <?php foreach ($causes as $cause): ?>
                <option value="<?= $cause['cause_id'] ?>"><?= htmlspecialchars($cause['description']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Register Death</button>
    </form>
</body>
</html>
