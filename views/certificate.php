<?php
session_start();
require_once __DIR__ . '/../classes/Certificate.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['deceased_id'])) {
    die("No record specified.");
}

$deceasedId = (int) $_GET['deceased_id'];
$certificate = new Certificate();

// Issue the certificate (or fetch existing one if already issued)
$result = $certificate->issue($deceasedId, $_SESSION['user_id']);

if (is_string($result)) {
    // An error message was returned instead of certificate data
    die("<p style='font-family:Arial;color:red;'>" . htmlspecialchars($result) . "</p><a href='list_deaths.php'>Back</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Death Certificate - <?= htmlspecialchars($result['full_name']) ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; max-width: 700px; margin: 40px auto; }
        .certificate { border: 6px double #2c3e50; padding: 40px; text-align: center; }
        h1 { font-size: 26px; margin-bottom: 5px; }
        .subtitle { color: #555; margin-bottom: 30px; }
        table { width: 100%; margin-top: 20px; text-align: left; }
        td { padding: 8px; font-size: 15px; }
        td.label { font-weight: bold; width: 40%; }
        .cert-number { margin-top: 30px; font-size: 13px; color: #777; }
        .no-print { text-align: center; margin-top: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Death Registration System</h1>
        <div class="subtitle">Official Certificate of Death</div>

        <table>
            <tr><td class="label">Full Name:</td><td><?= htmlspecialchars($result['full_name']) ?></td></tr>
            <tr><td class="label">Date of Birth:</td><td><?= htmlspecialchars($result['date_of_birth']) ?></td></tr>
            <tr><td class="label">Date of Death:</td><td><?= htmlspecialchars($result['date_of_death']) ?></td></tr>
            <tr><td class="label">Place of Death:</td><td><?= htmlspecialchars($result['place_of_death']) ?></td></tr>
            <tr><td class="label">Cause of Death:</td><td><?= htmlspecialchars($result['cause']) ?></td></tr>
            <tr><td class="label">Issued Date:</td><td><?= htmlspecialchars($result['issued_date']) ?></td></tr>
        </table>

        <div class="cert-number">Certificate No: <?= htmlspecialchars($result['certificate_number']) ?></div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print / Save as PDF</button>
        <a href="list_deaths.php">Back to Records</a>
    </div>
</body>
</html>
