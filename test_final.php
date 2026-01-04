<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Testing gender-specific numbering...\n";

try {
    // Clean up any test candidates
    $db->exec("DELETE FROM candidates WHERE name LIKE 'Test%'");
    
    // Test male candidate #1
    echo "Testing male candidate #1...\n";
    $testQuery = "INSERT INTO candidates (pageant_id, candidate_number, name, age, gender, description) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $testStmt = $db->prepare($testQuery);
    $testStmt->execute([1, '1', 'Test Male Candidate', 25, 'Male', 'Test description']);
    echo "✓ Male candidate #1 added successfully!\n";
    
    // Test female candidate #1 (same number)
    echo "Testing female candidate #1...\n";
    $testStmt->execute([1, '1', 'Test Female Candidate', 23, 'Female', 'Test description']);
    echo "✓ Female candidate #1 added successfully!\n";
    
    // Show current candidates
    echo "\nCurrent candidates in pageant 1:\n";
    $candidates = $db->query("SELECT gender, candidate_number, name FROM candidates WHERE pageant_id = 1 ORDER BY gender, candidate_number")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($candidates as $candidate) {
        echo "- {$candidate['gender']} #{$candidate['candidate_number']}: {$candidate['name']}\n";
    }
    
    // Clean up test candidates
    $db->exec("DELETE FROM candidates WHERE name LIKE 'Test%'");
    
    echo "\n🎉 SUCCESS! The system now allows the same candidate number for each gender!\n";
    echo "You can now add:\n";
    echo "- Male Candidate #1\n";
    echo "- Female Candidate #1\n";
    echo "- Male Candidate #2\n";
    echo "- Female Candidate #2\n";
    echo "All within the same pageant!\n";
    
} catch(PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
