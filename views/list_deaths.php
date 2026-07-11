<?php
session_start();
require_once __DIR__ . '/../classes/Deceased.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$deceased = new Deceased();
$message = "";

// Handle approve/reject actions (only Registrar or Admin allowed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request.";
    } elseif (!in_array($_SESSION['role'], ['registrar', 'admin'])) {
        // Role-based authorization check - business rule from Phase 1
        $message = "You are not authorized to approve or reject records.";
    } else {
        $id = (int) $_POST['deceased_id'];

        if ($_POST['action'] === 'approve') {
            $deceased->approve($id, $_SESSION['user_id']);
            $message = "Record approved successfully.";
        } elseif ($_POST['action'] === 'reject') {
            $deceased->reject($id, $_SESSION['user_id']);
            $message = "Record rejected.";
        }
    }
}

// Optional filter via ?status=pending, ?status=approved, ?status=rejected
$statusFilter = $_GET['status'] ?? null;
$records = $deceased->getAll($statusFilter);

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Death Records - Death Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #2c3e50; color: white; }
        .status-pending { color: #b8860b; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        .message { padding: 10px; background: #eee; margin-bottom: 15px; }
        .filters a { margin-right: 12px; }
        form.inline { display: inline; }
        button { cursor: pointer; padding: 4px 10px; }
    </style>
</head>
<body>
    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    <h2>Death Records</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="filters">
        <a href="list_deaths.php">All</a>
        <a href="list_deaths.php?status=pending">Pending</a>
        <a href="list_deaths.php?status=approved">Approved</a>
        <a href="list_deaths.php?status=rejected">Rejected</a>
    </div>

    <table>
        <tr>
            <th>Name</th>
            <th>Gender</th>
            <th>Date of Death</th>
            <th>Place</th>
            <th>Cause</th>
            <th>Registered By</th>
            <th>Status</th>
            <?php if (in_array($_SESSION['role'], ['registrar', 'admin'])): ?>
                <th>Action</th>
            <?php endif; ?>
        </tr>
        <?php foreach ($records as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['gender']) ?></td>
            <td><?= htmlspecialchars($r['date_of_death']) ?></td>
            <td><?= htmlspecialchars($r['place_of_death']) ?></td>
            <td><?= htmlspecialchars($r['cause']) ?></td>
            <td><?= htmlspecialchars($r['registered_by_name']) ?></td>
            <td class="status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></td>

            <?php if (in_array($_SESSION['role'], ['registrar', 'admin'])): ?>
            <td>
                <?php if ($r['status'] === 'pending'): ?>
                    <form class="inline" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="deceased_id" value="<?= $r['deceased_id'] ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                    </form>
                    <form class="inline" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="deceased_id" value="<?= $r['deceased_id'] ?>">
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                <?php elseif ($r['status'] === 'approved'): ?>
                    <a href="certificate.php?deceased_id=<?= $r['deceased_id'] ?>" target="_blank">Generate Certificate</a>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
