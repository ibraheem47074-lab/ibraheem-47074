<?php
// Database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Load environment configuration
require_once 'config/env.php';

echo "<h3>Environment Variables:</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "SITE_URL: " . SITE_URL . "<br>";

// Test database connection
echo "<h3>Database Connection:</h3>";
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    echo "<span style='color: red;'>Connection failed: " . mysqli_connect_error() . "</span><br>";
    echo "Error code: " . mysqli_connect_errno() . "<br>";
} else {
    echo "<span style='color: green;'>Connection successful!</span><br>";
    
    // Test database selection
    if (mysqli_select_db($conn, DB_NAME)) {
        echo "<span style='color: green;'>Database selected successfully!</span><br>";
    } else {
        echo "<span style='color: red;'>Database selection failed: " . mysqli_error($conn) . "</span><br>";
    }
    
    // Test query
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        echo "<span style='color: green;'>Query executed successfully!</span><br>";
        echo "Tables in database: " . mysqli_num_rows($result) . "<br>";
        
        // Check if users table exists
        $users_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($users_check) > 0) {
            echo "<span style='color: green;'>Users table exists!</span><br>";
            
            // Count users
            $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
            $count_row = mysqli_fetch_assoc($count_result);
            echo "Total users: " . $count_row['count'] . "<br>";
        } else {
            echo "<span style='color: red;'>Users table does not exist!</span><br>";
        }
    } else {
        echo "<span style='color: red;'>Query failed: " . mysqli_error($conn) . "</span><br>";
    }
    
    mysqli_close($conn);
}

echo "<h3>PHP Info:</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? 'Available' : 'Not Available') . "<br>";

echo "<h3>File Permissions:</h3>";
echo "Config directory readable: " . (is_readable('config/') ? 'Yes' : 'No') . "<br>";
echo "Database.php readable: " . (is_readable('config/database.php') ? 'Yes' : 'No') . "<br>";
echo ".env readable: " . (is_readable('.env') ? 'Yes' : 'No') . "<br>";
?>
