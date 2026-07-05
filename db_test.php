<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Use live server credentials first
$host = 'localhost';
$user = 'u129650532_ibraheem';
$pass = 'Khan47074$';
$dbname = 'u129650532_ibraheem';

echo "Testing connection with: $host, $user, $dbname<br>";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    echo "<span style='color: red;'>Connection failed: " . mysqli_connect_error() . "</span><br>";
    
    // Try with local development credentials
    echo "<br>Trying with local development credentials:<br>";
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'pk_live_news';
    
    echo "Testing connection with: $host, $user, $dbname<br>";
    
    $conn2 = mysqli_connect($host, $user, $pass, $dbname);
    if (!$conn2) {
        echo "<span style='color: red;'>Connection failed: " . mysqli_connect_error() . "</span><br>";
    } else {
        echo "<span style='color: green;'>Connection successful with local development credentials!</span><br>";
        
        // Check if users table exists
        $table_check = mysqli_query($conn2, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($table_check) > 0) {
            echo "<span style='color: green;'>Users table exists</span><br>";
            
            // Count users
            $count_result = mysqli_query($conn2, "SELECT COUNT(*) as count FROM users");
            $count_row = mysqli_fetch_assoc($count_result);
            echo "Total users: " . $count_row['count'] . "<br>";
            
            // Show sample user data (without passwords)
            $user_query = mysqli_query($conn2, "SELECT id, name, email, role, status FROM users LIMIT 5");
            echo "Sample users:<br>";
            while ($user = mysqli_fetch_assoc($user_query)) {
                echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}<br>";
            }
        } else {
            echo "<span style='color: red;'>Users table does not exist!</span><br>";
            
            // Show all tables
            $tables = mysqli_query($conn2, "SHOW TABLES");
            echo "Available tables:<br>";
            while ($table = mysqli_fetch_row($tables)) {
                echo "- {$table[0]}<br>";
            }
        }
        
        mysqli_close($conn2);
    }
} else {
    echo "<span style='color: green;'>Connection successful!</span><br>";
    
    // Check if users table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($table_check) > 0) {
        echo "<span style='color: green;'>Users table exists</span><br>";
        
        // Count users
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
        $count_row = mysqli_fetch_assoc($count_result);
        echo "Total users: " . $count_row['count'] . "<br>";
    } else {
        echo "<span style='color: red;'>Users table does not exist!</span><br>";
    }
    
    mysqli_close($conn);
}

echo "<br><h2>PHP Info</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? 'Available' : 'Not Available') . "<br>";
?>
