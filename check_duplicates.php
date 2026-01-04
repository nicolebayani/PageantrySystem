<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Check for duplicate candidate numbers within the same pageant
    $checkDuplicates = "SELECT pageant_id, candidate_number, COUNT(*) as count 
                        FROM candidates 
                        GROUP BY pageant_id, candidate_number 
                        HAVING COUNT(*) > 1";
    $stmt = $db->query($checkDuplicates);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($duplicates)) {
        echo "Found duplicate candidate numbers:\n";
        foreach ($duplicates as $duplicate) {
            echo "- Pageant ID {$duplicate['pageant_id']}, Candidate Number '{$duplicate['candidate_number']}' appears {$duplicate['count']} times\n";
        }
        
        // Get details of duplicates
        echo "\nDetailed information:\n";
        foreach ($duplicates as $duplicate) {
            $detailsQuery = "SELECT * FROM candidates WHERE pageant_id = ? AND candidate_number = ?";
            $detailsStmt = $db->prepare($detailsQuery);
            $detailsStmt->execute([$duplicate['pageant_id'], $duplicate['candidate_number']]);
            $candidates = $detailsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($candidates as $candidate) {
                echo "  - ID: {$candidate['id']}, Name: {$candidate['name']}, Gender: {$candidate['gender']}\n";
            }
        }
    } else {
        echo "No duplicate candidate numbers found.\n";
    }
    
    // Show all current candidates
    echo "\nAll current candidates:\n";
    $allQuery = "SELECT id, pageant_id, candidate_number, name, gender FROM candidates ORDER BY pageant_id, candidate_number";
    $allStmt = $db->query($allQuery);
    $allCandidates = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allCandidates as $candidate) {
        echo "- ID: {$candidate['id']}, Pageant: {$candidate['pageant_id']}, Number: '{$candidate['candidate_number']}', Name: {$candidate['name']}, Gender: {$candidate['gender']}\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
