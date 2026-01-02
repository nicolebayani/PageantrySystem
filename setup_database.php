<?php
// Comprehensive database setup script to fix login issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Pageantry System Database Setup</h2>";

try {
    // Step 1: Create database connection without specifying database
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'pageantry_system';
    
    echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px;'><strong>Debugging Info:</strong><br>Host: $host<br>DB Name: $dbname<br>User: $username</div>";
    echo "<h3>Step 1: Creating Database</h3>";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and Create database
    $conn->exec("DROP DATABASE IF EXISTS $dbname");
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Database '$dbname' created/verified<br>";
    
    // Step 2: Connect to the specific database
    echo "<h3>Step 2: Connecting to Database</h3>";
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("set names utf8");
    echo "✅ Connected to database successfully<br>";
    
    // Step 3: Create tables
    echo "<h3>Step 3: Creating Tables</h3>";

    // Drop tables in reverse order of creation to avoid foreign key issues
    $db->exec("DROP TABLE IF EXISTS scores");
    $db->exec("DROP TABLE IF EXISTS candidates");
    $db->exec("DROP TABLE IF EXISTS criteria");
    $db->exec("DROP TABLE IF EXISTS segments");
    $db->exec("DROP TABLE IF EXISTS pageants");
    $db->exec("DROP TABLE IF EXISTS judge_assignments");
    $db->exec("DROP TABLE IF EXISTS settings");
    $db->exec("DROP TABLE IF EXISTS users");
    echo "✅ Existing tables dropped for a clean setup<br>";
    
    // Users table
    $userTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'judge') NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $db->exec($userTable);
    echo "✅ Users table created<br>";

    // Pageants table
    $pageantsTable = "
    CREATE TABLE IF NOT EXISTS pageants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        primary_color VARCHAR(7) DEFAULT '#667eea',
        secondary_color VARCHAR(7) DEFAULT '#764ba2',
        accent_color VARCHAR(7) DEFAULT '#ffd700',
        logo_image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $db->exec($pageantsTable);
    echo "✅ Pageants table created<br>";

    // Judge Assignments table
    $judgeAssignmentsTable = "
    CREATE TABLE IF NOT EXISTS judge_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judge_id INT NOT NULL,
        pageant_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (judge_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (pageant_id) REFERENCES pageants(id) ON DELETE CASCADE,
        UNIQUE KEY unique_assignment (judge_id, pageant_id)
    ) ENGINE=InnoDB";
    $db->exec($judgeAssignmentsTable);
    echo "✅ Judge Assignments table created<br>";
    
    // Candidates table
    $candidatesTable = "
    CREATE TABLE IF NOT EXISTS candidates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pageant_id INT NOT NULL,
        candidate_number VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        age INT,
        description TEXT,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pageant_id) REFERENCES pageants(id) ON DELETE CASCADE,
        UNIQUE KEY (pageant_id, candidate_number)
    ) ENGINE=InnoDB";
    $db->exec($candidatesTable);
    echo "✅ Candidates table created<br>";

    // Segments table
    $segmentsTable = "
    CREATE TABLE IF NOT EXISTS segments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pageant_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (pageant_id) REFERENCES pageants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    $db->exec($segmentsTable);
    echo "✅ Segments table created<br>";
    
    // Criteria table
    $criteriaTable = "
    CREATE TABLE IF NOT EXISTS criteria (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pageant_id INT NOT NULL,
        round_id INT,
        name VARCHAR(100) NOT NULL,
        percentage DECIMAL(5,2) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pageant_id) REFERENCES pageants(id) ON DELETE CASCADE,
        FOREIGN KEY (round_id) REFERENCES segments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    $db->exec($criteriaTable);
    echo "✅ Criteria table created<br>";
    
    // Scores table
    $scoresTable = "
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
    ) ENGINE=InnoDB";
    $db->exec($scoresTable);
    echo "✅ Scores table created<br>";
    
    // Settings table
    $settingsTable = "
    CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $db->exec($settingsTable);
    echo "✅ Settings table created<br>";

    // Step 3.5: Verify table creation
    echo "<h3>Step 3.5: Verifying Table Creation</h3>";
    $tablesStmt = $db->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "❌ No tables found in the database.<br>";
    } else {
        echo "Tables found in '{$dbname}':<br><ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }

    
    // Step 4: Create user accounts
    echo "<h3>Step 4: Creating User Accounts</h3>";
    
    // Step 4.5: Insert a default pageant
    echo "<h3>Step 4.5: Inserting Default Pageant</h3>";
    $db->exec("INSERT INTO pageants (name) VALUES ('Default Pageant')");
    $defaultPageantId = $db->lastInsertId();
    echo "✅ Default pageant created with ID: $defaultPageantId<br>";
    
    // Check if admin exists
    $checkAdmin = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $checkAdmin->execute();
    
    if ($checkAdmin->fetchColumn() == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $db->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
        $insertAdmin->execute(['admin', $adminPassword, 'admin', 'System Administrator']);
        echo "✅ Admin account created (username: admin, password: admin123)<br>";
    } else {
        echo "ℹ️ Admin account already exists<br>";
    }
    
    // Check if judge exists
    $checkJudge = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'judge1'");
    $checkJudge->execute();
    
    if ($checkJudge->fetchColumn() == 0) {
        $judgePassword = password_hash('judge123', PASSWORD_DEFAULT);
        $insertJudge = $db->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
        $insertJudge->execute(['judge1', $judgePassword, 'judge', 'Sample Judge']);
        echo "✅ Judge account created (username: judge1, password: judge123)<br>";
    } else {
        echo "ℹ️ Judge account already exists<br>";
    }

    // Check if pageantry_admin exists
    $checkPageantryAdmin = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'pageantry_admin'");
    $checkPageantryAdmin->execute();

    if ($checkPageantryAdmin->fetchColumn() == 0) {
        $pageantryAdminPassword = password_hash('123123', PASSWORD_DEFAULT);
        $insertPageantryAdmin = $db->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
        $insertPageantryAdmin->execute(['pageantry_admin', $pageantryAdminPassword, 'admin', 'Pageantry Admin']);
        echo "✅ Pageantry Admin account created (username: pageantry_admin, password: 123123)<br>";
    } else {
        echo "ℹ️ Pageantry Admin account already exists<br>";
    }
    
    // Step 5: Insert default criteria
    echo "<h3>Step 5: Setting Up Default Criteria</h3>";
    
    $checkCriteria = $db->prepare("SELECT COUNT(*) FROM criteria");
    $checkCriteria->execute();
    
    if ($checkCriteria->fetchColumn() == 0) {
        $criteriaData = [
            [$defaultPageantId, 'Beauty & Poise', 30.00, 'Physical appearance, posture, and overall presentation'],
            [$defaultPageantId, 'Talent Performance', 25.00, 'Special talent or skill demonstration'],
            [$defaultPageantId, 'Evening Gown', 20.00, 'Elegance and grace in formal wear'],
            [$defaultPageantId, 'Interview Skills', 15.00, 'Communication skills and personality'],
            [$defaultPageantId, 'Swimwear', 10.00, 'Physical fitness and confidence']
        ];
        
        $insertCriteria = $db->prepare("INSERT INTO criteria (pageant_id, name, percentage, description) VALUES (?, ?, ?, ?)");
        foreach ($criteriaData as $criteria) {
            $insertCriteria->execute($criteria);
        }
        echo "✅ Default criteria added (5 categories totaling 100%)<br>";
    } else {
        echo "ℹ️ Criteria already exist<br>";
    }
    
    // Step 6: Insert default settings
    echo "<h3>Step 6: Setting Up Default Settings</h3>";
    
    $defaultSettings = [
        'pageant_name' => 'Pageantry Competition',
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'accent_color' => '#ffd700',
        'logo_text' => '👑',
        'logo_image' => '',
        'logo_type' => 'emoji',
        'theme_style' => 'gradient',
        'background_style' => 'gradient',
        'card_style' => 'glassmorphism'
    ];
    
    $insertSetting = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $settingsAdded = 0;
    foreach ($defaultSettings as $key => $value) {
        $result = $insertSetting->execute([$key, $value]);
        if ($result) $settingsAdded++;
    }
    echo "✅ Default settings configured ($settingsAdded new settings added)<br>";
    
    // Step 7: Test login functionality
    echo "<h3>Step 7: Testing Login System</h3>";
    
    $testQuery = "SELECT id, username, role, full_name FROM users WHERE username = ?";
    $testStmt = $db->prepare($testQuery);
    $testStmt->execute(['admin']);
    $testUser = $testStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        echo "✅ Admin user found: " . htmlspecialchars($testUser['full_name']) . " (Role: " . $testUser['role'] . ")<br>";
    } else {
        echo "❌ Admin user not found - there may be an issue<br>";
    }
    
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>Login Credentials:</strong><br>";
    echo "🔑 Admin: <code>admin</code> / <code>admin123</code><br>";
    echo "👨‍⚖️ Judge: <code>judge1</code> / <code>judge123</code><br>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚀 Go to Login</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Home</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>❌ Database Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Possible solutions:</strong><br>";
    echo "• Make sure XAMPP MySQL is running<br>";
    echo "• Check database connection settings in config/database.php<br>";
    echo "• Ensure MySQL user 'root' has proper permissions<br>";
    echo "</div>";
} catch(Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>❌ General Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
