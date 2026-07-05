<?php
require_once __DIR__ . '/config/database.php';

echo "Testing database connection and tables...\n<br>";

try {
    // Test connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    echo "Database connection: OK\n<br>";

    // Check if role_applications table exists
    $result = $conn->query("SHOW TABLES LIKE 'role_applications'");
    if ($result->num_rows > 0) {
        echo "role_applications table: EXISTS\n<br>";
        
        // Show table structure
        $result = $conn->query("DESCRIBE role_applications");
        echo "<br>role_applications structure:\n<br>";
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n<br>";
        }
    } else {
        echo "role_applications table: MISSING\n<br>";
    }

    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<br>users table: EXISTS\n<br>";
    } else {
        echo "<br>users table: MISSING\n<br>";
    }

    // Test the problematic query step by step
    echo "<br>Testing query components:\n<br>";
    
    // Test basic query
    $query1 = "SELECT * FROM role_applications LIMIT 1";
    $result = $conn->query($query1);
    if ($result) {
        echo "Basic role_applications query: OK\n<br>";
    } else {
        echo "Basic role_applications query: FAILED - " . $conn->error . "\n<br>";
    }

    // Test join with users
    $query2 = "SELECT ra.*, u.name FROM role_applications ra LEFT JOIN users u ON ra.user_id = u.id LIMIT 1";
    $result = $conn->query($query2);
    if ($result) {
        echo "Join with users: OK\n<br>";
    } else {
        echo "Join with users: FAILED - " . $conn->error . "\n<br>";
    }

    // Test the full query
    $query3 = "SELECT ra.*, u.name, u.email, u.role as current_role, reviewer.name as reviewer_name 
              FROM role_applications ra 
              LEFT JOIN users u ON ra.user_id = u.id 
              LEFT JOIN users reviewer ON ra.reviewed_by = reviewer.id 
              ORDER BY ra.created_at DESC 
              LIMIT 1";
    $result = $conn->query($query3);
    if ($result) {
        echo "Full query: OK\n<br>";
    } else {
        echo "Full query: FAILED - " . $conn->error . "\n<br>";
        echo "Query was: " . $query3 . "\n<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n<br>";
}
?>
