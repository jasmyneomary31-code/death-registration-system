<?php
// index.php - Test file to confirm Database.php connection works
require_once 'config/Database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Death Registration System</h2>";
echo "Connection successful! Database is connected.";

// Quick test: count how many users exist
$stmt = $db->query("SELECT COUNT(*) AS total FROM users");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<br>Users in database: " . $result['total'];
?>
