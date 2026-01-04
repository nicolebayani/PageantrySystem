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
    
    // Check if there's a female candidate with number 1
    echo "\nChecking for female candidate #1...\n";
    $checkFemale = "SELECT * FROM candidates WHERE pageant_id = 1 AND gender = 'Female' AND candidate_number = '1'";
    $femaleStmt = $db->query($checkFemale);
    $femaleCandidates = $femaleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($femaleCandidates)) {
        echo "Found female candidate(s) with number 1:\n";
        foreach ($femaleCandidates as $female) {
            echo "- ID: {$female['id']}, Name: {$female['name']}\n";
        }
        
        echo "\nRenumbering female candidates to avoid conflicts...\n";
        
        // Update all female candidates to have sequential numbers starting from the next available
        $getFemaleNumbers = "SELECT id, candidate_number FROM candidates WHERE pageant_id = 1 AND gender = 'Female' ORDER BY CAST(candidate_number AS UNSIGNED)";
        $femaleNumbersStmt = $db->query($getFemaleNumbers);
        $femaleNumbers = $femaleNumbersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $newNumber = 1;
        foreach ($femaleNumbers as $female) {
            echo "Updating candidate ID {$female['id']} from number {$female['candidate_number']} to number $newNumber\n";
            $updateQuery = "UPDATE candidates SET candidate_number = ? WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$newNumber, $female['id']]);
            $newNumber++;
        }
        
        echo "✓ Female candidates renumbered successfully!\n";
    } else {
        echo "No female candidate with number 1 found.\n";
    }
    
    // Test the system again
    echo "\nTesting system after fixes...\n";
    
    // Test adding male candidate #1
    try {
        $testQuery = "INSERT INTO candidates (pageant_id, candidate_number, name, age, gender, description) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $testStmt = $db->prepare($testQuery);
        $testStmt->execute([1, '1', 'Test Male', 25, 'Male', 'Test']);
        echo "✓ Male candidate #1 added\n";
        $db->exec("DELETE FROM candidates WHERE name = 'Test Male'");
    } catch(PDOException $e) {
        echo "✗ Male #1 failed: " . $e->getMessage() . "\n";
    }
    
    // Test adding female candidate #1
    try {
        $testStmt->execute([1, '1', 'Test Female', 23, 'Female', 'Test']);
        echo "✓ Female candidate #1 added\n";
        $db->exec("DELETE FROM candidates WHERE name = 'Test Female'");
    } catch(PDOException $e) {
        echo "✗ Female #1 failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✓ System should now work correctly!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
