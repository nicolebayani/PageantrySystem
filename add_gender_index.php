<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Add new unique index for gender-specific numbering
    echo "Adding new composite index for gender-specific numbering...\n";
    $addIndexQuery = "ALTER TABLE candidates ADD UNIQUE KEY unique_candidate_per_pageant_gender (pageant_id, gender, candidate_number)";
    $db->exec($addIndexQuery);
    echo "New unique index added successfully!\n";
    
    echo "\nDatabase structure updated successfully!\n";
    echo "Now you can have separate numbering for male and female candidates.\n";
    
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "Error: There are already candidates with conflicting numbers across genders.\n";
        echo "Current conflicts need to be resolved first.\n";
        
        // Show conflicting candidates
        echo "\nCurrent candidates:\n";
        $candidatesQuery = "SELECT * FROM candidates ORDER BY pageant_id, gender, candidate_number";
        $candidatesStmt = $db->query($candidatesQuery);
        $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($candidates as $candidate) {
            echo "- Pageant: {$candidate['pageant_id']}, Gender: {$candidate['gender']}, Number: {$candidate['candidate_number']}, Name: {$candidate['name']}\n";
        }
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
