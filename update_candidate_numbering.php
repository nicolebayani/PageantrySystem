<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Drop the unique constraint on (pageant_id, candidate_number)
    echo "Dropping existing unique constraint...\n";
    $alterQuery = "ALTER TABLE candidates DROP INDEX unique_candidate_per_pageant";
    $db->exec($alterQuery);
    echo "Unique constraint dropped.\n";
    
    // Add new unique constraint on (pageant_id, gender, candidate_number)
    echo "Adding new unique constraint for gender-specific numbering...\n";
    $addConstraintQuery = "ALTER TABLE candidates ADD UNIQUE KEY unique_candidate_per_pageant_gender (pageant_id, gender, candidate_number)";
    $db->exec($addConstraintQuery);
    echo "New unique constraint added.\n";
    
    echo "\nDatabase structure updated successfully!\n";
    echo "Now you can have:\n";
    echo "- Pageant 1, Male, Candidate 1\n";
    echo "- Pageant 1, Female, Candidate 1\n";
    echo "- Pageant 1, Male, Candidate 2\n";
    echo "- Pageant 1, Female, Candidate 2\n";
    echo "etc.\n";
    
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "Error: There are already candidates with conflicting numbers.\n";
        echo "Please resolve existing conflicts first.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
