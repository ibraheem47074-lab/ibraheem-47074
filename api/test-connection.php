<?php
// Simple database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    require_once '../config/database.php';
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test basic query
    $test_query = "SELECT 1 as test";
    $result = mysqli_query($conn, $test_query);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Basic query successful</p>";
        
        // Test news table
        $news_check = "SHOW TABLES LIKE 'news'";
        $news_result = mysqli_query($conn, $news_check);
        
        if (mysqli_num_rows($news_result) > 0) {
            echo "<p style='color: green;'>✓ News table exists</p>";
            
            // Test breaking news query
            $breaking_query = "SELECT COUNT(*) as count FROM news WHERE status = 'published' AND is_breaking = 1";
            $breaking_result = mysqli_query($conn, $breaking_query);
            $count = mysqli_fetch_assoc($breaking_result);
            
            echo "<p style='color: green;'>✓ Breaking news query successful - Found " . $count['count'] . " breaking news items</p>";
        } else {
            echo "<p style='color: orange;'>⚠ News table not found</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Basic query failed: " . mysqli_error($conn) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test environment variables
echo "<h2>Environment Variables</h2>";
echo "<p>DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</p>";
echo "<p>DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "</p>";
echo "<p>DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</p>";

?>
