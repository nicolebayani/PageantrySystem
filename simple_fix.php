<?php
require_once 'config/database.php';

echo "Starting fix...\n";

$database = new Database();
$db = $database->getConnection();

echo "Database connected.\n";

try {
    // Show current indexes
    echo "Current indexes:\n";
    $indexes = $db->query("SHOW INDEX FROM candidates")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $index) {
        echo "- {$index['Key_name']}: {$index['Column_name']}\n";
    }
    
    // Remove old constraint
    echo "\nRemoving old constraint...\n";
    $db->exec("ALTER TABLE candidates DROP INDEX pageant_id");
    echo "Old constraint removed.\n";
    
    // Add gender-specific constraint
    echo "\nAdding gender-specific constraint...\n";
    $db->exec("ALTER TABLE candidates ADD UNIQUE KEY unique_candidate_per_pageant_gender (pageant_id, gender, candidate_number)");
    echo "Gender-specific constraint added.\n";
    
    echo "\nFix completed!\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
