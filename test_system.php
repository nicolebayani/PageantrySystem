<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    echo "Testing candidate insertion...\n";
    
    // Test adding a male candidate with number 1
    echo "Testing: Male candidate #1 for pageant 1\n";
    $testQuery = "INSERT INTO candidates (pageant_id, candidate_number, name, age, gender, description) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $testStmt = $db->prepare($testQuery);
    
    try {
        $testStmt->execute([1, '1', 'Test Male Candidate', 25, 'Male', 'Test description']);
        echo "✓ Male candidate #1 added successfully!\n";
        
        // Remove the test candidate
        $deleteQuery = "DELETE FROM candidates WHERE name = 'Test Male Candidate'";
        $db->exec($deleteQuery);
        echo "✓ Test candidate removed.\n";
    } catch(PDOException $e) {
        echo "✗ Error adding male candidate #1: " . $e->getMessage() . "\n";
    }
    
    // Test adding a female candidate with number 1
    echo "\nTesting: Female candidate #1 for pageant 1\n";
    try {
        $testStmt->execute([1, '1', 'Test Female Candidate', 23, 'Female', 'Test description']);
        echo "✓ Female candidate #1 added successfully!\n";
        
        // Remove the test candidate
        $deleteQuery = "DELETE FROM candidates WHERE name = 'Test Female Candidate'";
        $db->exec($deleteQuery);
        echo "✓ Test candidate removed.\n";
    } catch(PDOException $e) {
        echo "✗ Error adding female candidate #1: " . $e->getMessage() . "\n";
    }
    
    // Show final index status
    echo "\nFinal index status:\n";
    $indexesQuery = "SHOW INDEX FROM candidates";
    $indexesStmt = $db->query($indexesQuery);
    $indexes = $indexesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($indexes as $index) {
        $unique = $index['Non_unique'] == 0 ? 'UNIQUE' : 'NON-UNIQUE';
        echo "- Index: {$index['Key_name']} ($unique), Column: {$index['Column_name']}\n";
    }
    
    echo "\n✓ System is ready for gender-specific numbering!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
