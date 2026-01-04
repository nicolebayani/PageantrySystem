<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Drop the existing composite index
    echo "Dropping existing composite index...\n";
    $alterQuery = "ALTER TABLE candidates DROP INDEX pageant_id";
    $db->exec($alterQuery);
    echo "Existing index dropped.\n";
    
    // Add new composite index for gender-specific numbering
    echo "Adding new composite index for gender-specific numbering...\n";
    $addIndexQuery = "ALTER TABLE candidates ADD UNIQUE KEY unique_candidate_per_pageant_gender (pageant_id, gender, candidate_number)";
    $db->exec($addIndexQuery);
    echo "New unique index added successfully!\n";
    
    echo "\nDatabase structure updated successfully!\n";
    echo "Now you can have separate numbering for male and female candidates:\n";
    echo "- Pageant 1, Male, Candidate 1\n";
    echo "- Pageant 1, Female, Candidate 1\n";
    echo "- Pageant 1, Male, Candidate 2\n";
    echo "- Pageant 1, Female, Candidate 2\n";
    echo "etc.\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
