<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Show current candidates and their numbers
    echo "Current candidates:\n";
    $candidatesQuery = "SELECT id, pageant_id, gender, candidate_number, name FROM candidates ORDER BY pageant_id, gender, candidate_number";
    $candidatesStmt = $db->query($candidatesQuery);
    $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($candidates as $candidate) {
        echo "- ID: {$candidate['id']}, Pageant: {$candidate['pageant_id']}, Gender: {$candidate['gender']}, Number: {$candidate['candidate_number']}, Name: {$candidate['name']}\n";
    }
    
    // Check for conflicts with the new constraint
    echo "\nChecking for conflicts with new constraint (pageant_id, gender, candidate_number)...\n";
    $conflictQuery = "SELECT pageant_id, gender, candidate_number, COUNT(*) as count 
                      FROM candidates 
                      GROUP BY pageant_id, gender, candidate_number 
                      HAVING COUNT(*) > 1";
    $conflictStmt = $db->query($conflictQuery);
    $conflicts = $conflictStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($conflicts)) {
        echo "Found conflicts:\n";
        foreach ($conflicts as $conflict) {
            echo "- Pageant {$conflict['pageant_id']}, {$conflict['gender']}, Number {$conflict['candidate_number']}: {$conflict['count']} duplicates\n";
        }
    } else {
        echo "No conflicts found with new constraint.\n";
    }
    
    // Check if the new index exists
    echo "\nChecking indexes:\n";
    $indexQuery = "SHOW INDEX FROM candidates WHERE Key_name = 'unique_candidate_per_pageant_gender'";
    $indexStmt = $db->query($indexQuery);
    $indexes = $indexStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($indexes)) {
        echo "New gender-specific index exists.\n";
    } else {
        echo "New gender-specific index NOT found.\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
