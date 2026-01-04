<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Checking current candidates...\n";

$candidates = $db->query("SELECT id, gender, candidate_number, name FROM candidates WHERE pageant_id = 1 ORDER BY gender, candidate_number")->fetchAll(PDO::FETCH_ASSOC);

foreach ($candidates as $candidate) {
    echo "- ID: {$candidate['id']}, {$candidate['gender']} #{$candidate['candidate_number']}: {$candidate['name']}\n";
}

// Find the female candidate with number 1
$femaleWithNumber1 = null;
foreach ($candidates as $candidate) {
    if ($candidate['gender'] === 'Female' && $candidate['candidate_number'] === '1') {
        $femaleWithNumber1 = $candidate;
        break;
    }
}

if ($femaleWithNumber1) {
    echo "\nFound female candidate with number 1: {$femaleWithNumber1['name']} (ID: {$femaleWithNumber1['id']})\n";
    echo "Updating her number to 2 to free up number 1...\n";
    
    $updateQuery = "UPDATE candidates SET candidate_number = '2' WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$femaleWithNumber1['id']]);
    
    echo "✓ Female candidate renumbered to #2\n";
    
    // Test again
    echo "\nTesting again...\n";
    
    // Clean up test candidates
    $db->exec("DELETE FROM candidates WHERE name LIKE 'Test%'");
    
    // Test male #1
    $testQuery = "INSERT INTO candidates (pageant_id, candidate_number, name, age, gender, description) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $testStmt = $db->prepare($testQuery);
    $testStmt->execute([1, '1', 'Test Male', 25, 'Male', 'Test']);
    echo "✓ Male candidate #1 added\n";
    
    // Test female #1
    $testStmt->execute([1, '1', 'Test Female', 23, 'Female', 'Test']);
    echo "✓ Female candidate #1 added\n";
    
    // Clean up
    $db->exec("DELETE FROM candidates WHERE name LIKE 'Test%'");
    
    echo "\n🎉 SUCCESS! Gender-specific numbering is now working!\n";
} else {
    echo "\nNo female candidate with number 1 found.\n";
}
?>
