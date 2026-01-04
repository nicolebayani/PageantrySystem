<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Check current structure
    $checkQuery = "DESCRIBE candidates";
    $stmt = $db->query($checkQuery);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current candidates table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check if gender column exists
    $genderExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'gender') {
            $genderExists = true;
            break;
        }
    }
    
    if (!$genderExists) {
        echo "\nAdding gender column...\n";
        $alterQuery = "ALTER TABLE candidates ADD COLUMN gender ENUM('Male', 'Female') AFTER age";
        $db->exec($alterQuery);
        echo "Gender column added successfully.\n";
    } else {
        echo "\nGender column already exists.\n";
    }
    
    // Check for any existing candidates without gender
    $checkNullQuery = "SELECT COUNT(*) as count FROM candidates WHERE gender IS NULL";
    $stmt = $db->query($checkNullQuery);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "\nFound {$result['count']} candidates without gender. Updating them to 'Male' as default...\n";
        $updateQuery = "UPDATE candidates SET gender = 'Male' WHERE gender IS NULL";
        $db->exec($updateQuery);
        echo "Updated candidates without gender.\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
