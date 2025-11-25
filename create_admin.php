<?php
// Simple script to create admin account with correct password hash
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Create database if it doesn't exist
$database->createDatabase();

// Create tables if they don't exist
$tables = [
    "users" => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'judge') NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        
    "candidates" => "
        CREATE TABLE IF NOT EXISTS candidates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            age INT,
            description TEXT,
            photo VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        
    "criteria" => "
        CREATE TABLE IF NOT EXISTS criteria (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            percentage DECIMAL(5,2) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        
    "scores" => "
        CREATE TABLE IF NOT EXISTS scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judge_id INT NOT NULL,
            candidate_id INT NOT NULL,
            criteria_id INT NOT NULL,
            score DECIMAL(5,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (judge_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
            FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE,
            UNIQUE KEY unique_score (judge_id, candidate_id, criteria_id)
        ) ENGINE=InnoDB"
];

// Execute table creation
foreach ($tables as $tableName => $sql) {
    try {
        $db->exec($sql);
        echo "✅ $tableName table verified/created successfully<br>";
    } catch (PDOException $e) {
        echo "⚠️ Error with $tableName table: " . $e->getMessage() . "<br>";
    }
}

try {
    // Create admin user with correct password hash
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $judgePassword = password_hash('judge123', PASSWORD_DEFAULT);
    
    // Delete existing users first
    $db->exec("DELETE FROM users WHERE username IN ('admin', 'judge1')");
    
    // Insert admin and judge
    $stmt = $db->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'admin', 'System Administrator']);
    $stmt->execute(['judge1', $judgePassword, 'judge', 'Sample Judge']);
    
    echo "Admin and Judge accounts created successfully!<br>";
    
    // Insert sample criteria
    $db->exec("DELETE FROM criteria");
    $criteriaStmt = $db->prepare("INSERT INTO criteria (name, percentage, description) VALUES (?, ?, ?)");
    $criteriaStmt->execute(['Beauty', 40.00, 'Physical appearance and overall beauty']);
    $criteriaStmt->execute(['Intelligence', 30.00, 'Question and answer portion']);
    $criteriaStmt->execute(['Talent', 20.00, 'Special talent presentation']);
    $criteriaStmt->execute(['Personality', 10.00, 'Overall personality and charisma']);
    
    echo "Sample criteria added successfully!<br>";
    echo "<br><strong>Login Credentials:</strong><br>";
    echo "Admin - Username: admin, Password: admin123<br>";
    echo "Judge - Username: judge1, Password: judge123<br>";
    echo "<br><a href='index.php'>Go to Main Page</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
