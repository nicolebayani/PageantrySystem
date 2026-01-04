<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Show all constraints
    echo "All constraints on candidates table:\n";
    $constraintsQuery = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                         FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                         WHERE TABLE_NAME = 'candidates' AND TABLE_SCHEMA = DATABASE()";
    $constraintsStmt = $db->query($constraintsQuery);
    $constraints = $constraintsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($constraints as $constraint) {
        echo "- Constraint: {$constraint['CONSTRAINT_NAME']}, Column: {$constraint['COLUMN_NAME']}\n";
        if ($constraint['REFERENCED_TABLE_NAME']) {
            echo "  References: {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}\n";
        }
    }
    
    // Show all indexes
    echo "\nAll indexes on candidates table:\n";
    $indexesQuery = "SHOW INDEX FROM candidates";
    $indexesStmt = $db->query($indexesQuery);
    $indexes = $indexesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($indexes as $index) {
        $unique = $index['Non_unique'] == 0 ? 'UNIQUE' : 'NON-UNIQUE';
        echo "- Index: {$index['Key_name']} ($unique), Column: {$index['Column_name']}\n";
    }
    
    // Try to identify the problematic constraint
    echo "\nTrying to identify the issue...\n";
    
    // Check if there's still an old unique constraint
    $oldIndexQuery = "SHOW INDEX FROM candidates WHERE Key_name = 'pageant_id' AND Non_unique = 0";
    $oldIndexStmt = $db->query($oldIndexQuery);
    $oldIndexes = $oldIndexStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($oldIndexes)) {
        echo "Found old unique index on pageant_id that needs to be removed.\n";
        echo "Attempting to remove it...\n";
        
        try {
            $dropQuery = "ALTER TABLE candidates DROP INDEX pageant_id";
            $db->exec($dropQuery);
            echo "Old index removed successfully!\n";
        } catch(PDOException $e) {
            echo "Error removing old index: " . $e->getMessage() . "\n";
            echo "This might be a foreign key constraint issue.\n";
        }
    } else {
        echo "No old unique index found on pageant_id.\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
