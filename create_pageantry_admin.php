<?php
require_once 'config/database.php';

// Create or replace a specific admin account: pageantry_admin / 123123
$database = new Database();
$db = $database->getConnection();

$username = 'pageantry_admin';
$password = '123123';
$full_name = 'Pageantry Administrator';

try {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Remove any existing user with that username
    $del = $db->prepare("DELETE FROM users WHERE username = ?");
    $del->execute([$username]);

    // Insert new admin user
    $ins = $db->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
    $ins->execute([$username, $hashed, 'admin', $full_name]);

    echo "<h2>Admin Created</h2>\n";
    echo "<p>Username: <strong>" . htmlspecialchars($username) . "</strong></p>\n";
    echo "<p>Password: <strong>123123</strong></p>\n";
    echo "<p>Full name: <strong>" . htmlspecialchars($full_name) . "</strong></p>\n";
    echo "<p><a href='auth/login.php'>Go to Login</a></p>\n";
} catch (PDOException $e) {
    echo "<h2>Error</h2>\n";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>\n";
}

?>