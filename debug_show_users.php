<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->query("SELECT id, username, role, full_name, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>Users</h2>\n<table border=1 cellpadding=8 cellspacing=0>\n<tr><th>id</th><th>username</th><th>role</th><th>full_name</th><th>created_at</th></tr>\n";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($u['id']) . "</td>";
        echo "<td>" . htmlspecialchars($u['username']) . "</td>";
        echo "<td>" . htmlspecialchars($u['role']) . "</td>";
        echo "<td>" . htmlspecialchars($u['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($u['created_at']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} catch (PDOException $e) {
    echo "Error querying users: " . $e->getMessage();
}

?>