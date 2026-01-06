<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h1>Database Schema Update</h1>";

try {
    // Check if 'gender' column exists in 'candidates' table
    $checkColumn = $db->query("SHOW COLUMNS FROM `candidates` LIKE 'gender'");
    if ($checkColumn->rowCount() > 0) {
        echo "<p style='color: green;'>'gender' column already exists in 'candidates' table. No action needed.</p>";
    } else {
        // Add 'gender' column to 'candidates' table
        $db->exec("ALTER TABLE `candidates` ADD `gender` VARCHAR(10) NOT NULL AFTER `age`");
        echo "<p style='color: green;'>Successfully added 'gender' column to 'candidates' table.</p>";
    }

    // Check if 'gender' column exists in 'pageants' table
    $checkPageantColumn = $db->query("SHOW COLUMNS FROM `pageants` LIKE 'gender'");
    if ($checkPageantColumn->rowCount() > 0) {
        echo "<p style='color: green;'>'gender' column already exists in 'pageants' table. No action needed.</p>";
    } else {
        // Add 'gender' column to 'pageants' table
        $db->exec("ALTER TABLE `pageants` ADD `gender` VARCHAR(10) NOT NULL DEFAULT 'Both' AFTER `name`");
        echo "<p style='color: green;'>Successfully added 'gender' column to 'pageants' table.</p>";
    }

    echo "<p><b>Schema update complete. You can now delete this file.</b></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>An error occurred: " . $e->getMessage() . "</p>";
}
?>
