<?php
// Simple database test that outputs to a file
error_reporting(E_ALL);
ini_set('display_errors', 1);

$output = "Database Test Results - " . date('Y-m-d H:i:s') . "\n";
$output .= "==========================================\n\n";

// Test 1: Basic connection with localhost
$output .= "Test 1: Basic connection (localhost, root, no password)\n";
$conn1 = mysqli_connect('localhost', 'root', '', 'pk_live_news');
if ($conn1) {
    $output .= "SUCCESS: Connected to pk_live_news database\n";
    mysqli_close($conn1);
} else {
    $output .= "FAILED: " . mysqli_connect_error() . "\n";
}

$output .= "\n";

// Test 2: Connection with .env credentials
$output .= "Test 2: Connection with .env credentials\n";
$conn2 = mysqli_connect('localhost', 'u129650532_ibraheem', 'Khan47074$', 'u129650532_ibraheem');
if ($conn2) {
    $output .= "SUCCESS: Connected to u129650532_ibraheem database\n";
    
    // Check tables
    $tables = mysqli_query($conn2, "SHOW TABLES");
    $output .= "Tables found: " . mysqli_num_rows($tables) . "\n";
    
    // Check users table
    $users_check = mysqli_query($conn2, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($users_check) > 0) {
        $output .= "SUCCESS: Users table exists\n";
        
        $count = mysqli_query($conn2, "SELECT COUNT(*) as count FROM users");
        $row = mysqli_fetch_assoc($count);
        $output .= "Total users: " . $row['count'] . "\n";
        
        // Check active users
        $active = mysqli_query($conn2, "SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $active_row = mysqli_fetch_assoc($active);
        $output .= "Active users: " . $active_row['count'] . "\n";
        
    } else {
        $output .= "FAILED: Users table does not exist\n";
    }
    
    mysqli_close($conn2);
} else {
    $output .= "FAILED: " . mysqli_connect_error() . "\n";
}

$output .= "\n";

// Test 3: PHP Extensions
$output .= "Test 3: PHP Extensions\n";
$output .= "mysqli: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "\n";
$output .= "session: " . (extension_loaded('session') ? 'YES' : 'NO') . "\n";

// Write results to file
file_put_contents('db_test_results.txt', $output);

echo "Database test completed. Check db_test_results.txt for results.";
?>
