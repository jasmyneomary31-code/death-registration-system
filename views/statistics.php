<?php
session_start();
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = Database::getInstance()->getConnection();

// 1. Totals by status (pending/approved/rejected)
$statusStats = $db->query(
    "SELECT status, COUNT(*) AS total FROM deceased GROUP BY status"
)->fetchAll(PDO::FETCH_ASSOC);

// 2. Totals by cause of death
$causeStats = $db->query(
    "SELECT c.description AS cause, COUNT(d.deceased_id) AS total
     FROM causes_of_death c
     LEFT JOIN deceased d ON c.cause_id = d.cause_id
     GROUP BY c.cause_id
     ORDER BY total DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// 3. Totals by gender
$genderStats = $db->query(
    "SELECT gender, COUNT(*) AS total FROM deceased GROUP BY gender"
)->fetchAll(PDO::FETCH_ASSOC);

// 4. Monthly trend (last 6 months, based on date_of_death)
$monthlyStats = $db->query(
    "SELECT DATE_FORMAT(date_of_death, '%Y-%m') AS month, COUNT(*) AS total
     FROM deceased
     GROUP BY month
     ORDER BY month DESC
     LIMIT 6"
)->fetchAll(PDO::FETCH_ASSOC);

// 5. Grand total + certificates issued (quick summary cards)
$totalDeaths = $db->query("SELECT COUNT(*) AS c FROM deceased")->fetch(PDO::FETCH_ASSOC)['c'];
$totalCerts  = $db->query("SELECT COUNT(*) AS c FROM certificates")->fetch(PDO::FETCH_ASSOC)['c'];

// Helper to find the max value in a stats array (for scaling simple CSS bar widths)
function maxTotal($stats) {
    $max = 1;
    foreach ($stats as $row) {
        if ($row['total'] > $max) $max = $row['total'];
    }
    return $max;
}
$causeMax = maxTotal($causeStats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statistics - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; }
        .cards { display: flex; gap: 15px; margin-bottom: 25px; }
        .card { flex: 1; background: #2c3e50; color: white; padding: 15px; border-radius: 6px; text-align: center; }
        .card .num { font-size: 28px; font-weight: bold; }
        h3 { margin-top: 30px; border-bottom: 2px solid #eee; padding-bottom: 6px; }
        .bar-row { display: flex; align-items: center; margin: 6px 0; font-size: 14px; }
        .bar-label { width: 160px; }
        .bar-track { flex: 1; background: #eee; border-radius: 4px; overflow: hidden; margin: 0 10px; }
        .bar-fill { background: #2c3e50; height: 18px; }
        .bar-total { width: 30px; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 14px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    <h2>Statistics</h2>

    <div class="cards">
        <div class="card">
            <div class="num"><?= $totalDeaths ?></div>
            <div>Total Deaths Registered</div>
        </div>
        <div class="card">
            <div class="num"><?= $totalCerts ?></div>
            <div>Certificates Issued</div>
        </div>
    </div>

    <h3>By Status</h3>
    <table>
        <tr><th>Status</th><th>Total</th></tr>
        <?php foreach ($statusStats as $row): ?>
        <tr>
            <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>By Cause of Death</h3>
    <?php foreach ($causeStats as $row): ?>
        <div class="bar-row">
            <div class="bar-label"><?= htmlspecialchars($row['cause']) ?></div>
            <div class="bar-track">
                <div class="bar-fill" style="width: <?= ($row['total'] / $causeMax) * 100 ?>%"></div>
            </div>
            <div class="bar-total"><?= $row['total'] ?></div>
        </div>
    <?php endforeach; ?>

    <h3>By Gender</h3>
    <table>
        <tr><th>Gender</th><th>Total</th></tr>
        <?php foreach ($genderStats as $row): ?>
        <tr>
            <td><?= ucfirst(htmlspecialchars($row['gender'])) ?></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Monthly Trend (Last 6 Months)</h3>
    <table>
        <tr><th>Month</th><th>Total Deaths</th></tr>
        <?php foreach ($monthlyStats as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['month']) ?></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
