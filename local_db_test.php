<?php
// Test local database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Local Database Test</h2>";

// Test with local MySQL (XAMPP default)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

echo "Testing: $host, $user, $dbname<br>";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    echo "<span style='color: red;'>Connection failed: " . mysqli_connect_error() . "</span><br>";
    
    // Try to create the database
    echo "<br>Trying to create database...<br>";
    $create_conn = mysqli_connect($host, $user, $pass);
    if ($create_conn) {
        $create_query = "CREATE DATABASE IF NOT EXISTS $dbname";
        if (mysqli_query($create_conn, $create_query)) {
            echo "<span style='color: green;'>Database created successfully!</span><br>";
            
            // Now try to connect again
            $conn = mysqli_connect($host, $user, $pass, $dbname);
            if ($conn) {
                echo "<span style='color: green;'>Connected to new database!</span><br>";
            }
        }
        mysqli_close($create_conn);
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
        
        // Show sample users
        $user_query = mysqli_query($conn, "SELECT id, name, email, role, status FROM users LIMIT 3");
        echo "Sample users:<br>";
        while ($user = mysqli_fetch_assoc($user_query)) {
            echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}<br>";
        }
    } else {
        echo "<span style='color: red;'>Users table does not exist!</span><br>";
        
        // Show all tables
        $tables = mysqli_query($conn, "SHOW TABLES");
        echo "Available tables: " . mysqli_num_rows($tables) . "<br>";
        while ($table = mysqli_fetch_row($tables)) {
            echo "- {$table[0]}<br>";
        }
        
        // Create users table
        echo "<br>Creating users table...<br>";
        $create_table = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'editor', 'reporter') DEFAULT 'reporter',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (mysqli_query($conn, $create_table)) {
            echo "<span style='color: green;'>Users table created successfully!</span><br>";
            
            // Insert a default admin user
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $insert_admin = "INSERT INTO users (name, email, password, role, status) 
                           VALUES ('Admin User', 'admin@pklivenews.com', '$admin_password', 'admin', 'active')";
            
            if (mysqli_query($conn, $insert_admin)) {
                echo "<span style='color: green;'>Default admin user created!</span><br>";
                echo "Email: admin@pklivenews.com<br>";
                echo "Password: admin123<br>";
            }
        }
    }
    
    mysqli_close($conn);
}
?>
