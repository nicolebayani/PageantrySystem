<?php
session_start();
require_once 'config/database.php';
require_once 'config/settings.php';
require_once 'config/pageant_config.php';

// Test script to verify settings changes propagate system-wide
echo "<h2>Settings Propagation Test</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    $settings = new Settings($db);
    
    // Test 1: Get initial settings
    echo "<h3>Test 1: Initial Settings</h3>";
    $initialSettings = getPageantSettings();
    echo "Pageant Name: " . htmlspecialchars($initialSettings['pageant_name']) . "<br>";
    echo "Primary Color: " . htmlspecialchars($initialSettings['primary_color']) . "<br>";
    echo "Cache timestamp: " . time() . "<br><br>";
    
    // Test 2: Update a setting
    echo "<h3>Test 2: Updating Settings</h3>";
    $testName = "Test Pageant " . date('H:i:s');
    $testColor = "#" . substr(md5(rand()), 0, 6);
    
    $result = $settings->updateMultiple([
        'pageant_name' => $testName,
        'primary_color' => $testColor
    ]);
    
    echo "Update result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
    
    // Test 3: Verify cache invalidation worked
    echo "<h3>Test 3: Verifying Cache Invalidation</h3>";
    $updatedSettings = getPageantSettings();
    
    echo "New Pageant Name: " . htmlspecialchars($updatedSettings['pageant_name']) . "<br>";
    echo "New Primary Color: " . htmlspecialchars($updatedSettings['primary_color']) . "<br>";
    
    $nameUpdated = ($updatedSettings['pageant_name'] === $testName);
    $colorUpdated = ($updatedSettings['primary_color'] === $testColor);
    
    echo "Name propagated: " . ($nameUpdated ? "✅ YES" : "❌ NO") . "<br>";
    echo "Color propagated: " . ($colorUpdated ? "✅ YES" : "❌ NO") . "<br>";
    
    // Test 4: Test CSS generation with new settings
    echo "<h3>Test 4: CSS Generation</h3>";
    $css = generatePageantCSS();
    $cssContainsNewColor = strpos($css, $testColor) !== false;
    echo "CSS contains new color: " . ($cssContainsNewColor ? "✅ YES" : "❌ NO") . "<br>";
    
    // Test 5: Force refresh test
    echo "<h3>Test 5: Force Refresh Test</h3>";
    $forcedSettings = getPageantSettings(true);
    $forceRefreshWorks = ($forcedSettings['pageant_name'] === $testName);
    echo "Force refresh works: " . ($forceRefreshWorks ? "✅ YES" : "❌ NO") . "<br>";
    
    // Overall result
    echo "<h3>Overall Test Result</h3>";
    $allTestsPassed = $nameUpdated && $colorUpdated && $cssContainsNewColor && $forceRefreshWorks;
    echo "<strong>" . ($allTestsPassed ? "🎉 ALL TESTS PASSED - Settings propagate correctly!" : "⚠️ SOME TESTS FAILED - Check implementation") . "</strong><br>";
    
    // Restore original settings
    echo "<h3>Cleanup</h3>";
    $settings->updateMultiple([
        'pageant_name' => $initialSettings['pageant_name'],
        'primary_color' => $initialSettings['primary_color']
    ]);
    echo "Original settings restored.<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
