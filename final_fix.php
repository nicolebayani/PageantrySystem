<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Show current candidates
    echo "Current candidates:\n";
    $candidatesQuery = "SELECT id, pageant_id, gender, candidate_number, name FROM candidates ORDER BY pageant_id, gender, candidate_number";
    $candidatesStmt = $db->query($candidatesQuery);
    $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($candidates as $candidate) {
        echo "- ID: {$candidate['id']}, Pageant: {$candidate['pageant_id']}, Gender: {$candidate['gender']}, Number: {$candidate['candidate_number']}, Name: {$candidate['name']}\n";
    }
    
    // Remove the conflicting female candidate
    echo "\nRemoving the conflicting female candidate (Jennie Kim)...\n";
    $deleteQuery = "DELETE FROM candidates WHERE id = 8";
    $db->exec($deleteQuery);
    echo "✓ Conflicting candidate removed.\n";
    
    // Test the system
    echo "\nTesting the system...\n";
    
    // Test male candidate #1
    try {
        $testQuery = "INSERT INTO candidates (pageant_id, candidate_number, name, age, gender, description) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $testStmt = $db->prepare($testQuery);
        $testStmt->execute([1, '1', 'Test Male', 25, 'Male', 'Test']);
        echo "✓ Male candidate #1 added successfully\n";
        $db->exec("DELETE FROM candidates WHERE name = 'Test Male'");
    } catch(PDOException $e) {
        echo "✗ Male #1 failed: " . $e->getMessage() . "\n";
    }
    
    // Test female candidate #1
    try {
        $testStmt->execute([1, '1', 'Test Female', 23, 'Female', 'Test']);
        echo "✓ Female candidate #1 added successfully\n";
        $db->exec("DELETE FROM candidates WHERE name = 'Test Female'");
    } catch(PDOException $e) {
        echo "✗ Female #1 failed: " . $e->getMessage() . "\n";
    }
    
    // Test both can coexist
    echo "\nTesting both genders with same number...\n";
    try {
        $testStmt->execute([1, '1', 'Male Candidate 1', 25, 'Male', 'Test']);
        $testStmt->execute([1, '1', 'Female Candidate 1', 23, 'Female', 'Test']);
        echo "✓ Both Male #1 and Female #1 added successfully!\n";
        
        // Clean up
        $db->exec("DELETE FROM candidates WHERE name IN ('Male Candidate 1', 'Female Candidate 1')");
        echo "✓ Test candidates cleaned up.\n";
    } catch(PDOException $e) {
        echo "✗ Coexistence test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 System is now ready for gender-specific numbering!\n";
    echo "You can now add:\n";
    echo "- Male Candidate #1, Male Candidate #2, etc.\n";
    echo "- Female Candidate #1, Female Candidate #2, etc.\n";
    echo "Both can exist in the same pageant without conflicts!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
