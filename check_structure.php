<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Show current indexes
    echo "Current indexes on candidates table:\n";
    $showIndexes = "SHOW INDEX FROM candidates";
    $stmt = $db->query($showIndexes);
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($indexes as $index) {
        echo "- Index: {$index['Key_name']}, Column: {$index['Column_name']}\n";
    }
    
    // Show current candidates
    echo "\nCurrent candidates:\n";
    $candidatesQuery = "SELECT * FROM candidates ORDER BY pageant_id, gender, candidate_number";
    $candidatesStmt = $db->query($candidatesQuery);
    $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($candidates as $candidate) {
        echo "- Pageant: {$candidate['pageant_id']}, Gender: {$candidate['gender']}, Number: {$candidate['candidate_number']}, Name: {$candidate['name']}\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
