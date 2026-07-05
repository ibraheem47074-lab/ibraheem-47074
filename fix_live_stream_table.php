<?php
require_once 'config/database.php';

echo "<h2>Fix Live Stream Table</h2>";

// Check if live_stream table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_stream'");
if (mysqli_num_rows($table_check) > 0) {
    echo "<p>live_stream table exists. Checking columns...</p>";
    
    // Get existing columns
    $columns_query = mysqli_query($conn, "SHOW COLUMNS FROM live_stream");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($columns_query)) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<p>Current columns: " . implode(', ', $existing_columns) . "</p>";
    
    // Check for missing columns and add them
    $required_columns = [
        'stopped_at' => "ALTER TABLE live_stream ADD COLUMN stopped_at TIMESTAMP NULL",
        'started_at' => "ALTER TABLE live_stream ADD COLUMN started_at TIMESTAMP NULL",
        'updated_at' => "ALTER TABLE live_stream ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($required_columns as $column => $sql) {
        if (!in_array($column, $existing_columns)) {
            echo "<p>Adding column: $column</p>";
            if (mysqli_query($conn, $sql)) {
                echo "<p style='color: green;'>✓ Added $column column successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to add $column column: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>→ Column $column already exists</p>";
        }
    }
    
    // Show final table structure
    echo "<h3>Final Table Structure:</h3>";
    $final_columns = mysqli_query($conn, "SHOW COLUMNS FROM live_stream");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($final_columns)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>live_stream table does not exist. Creating it...</p>";
    
    // Create the table
    $create_sql = "CREATE TABLE live_stream (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        stream_url VARCHAR(500),
        stream_key VARCHAR(255),
        status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
        started_at TIMESTAMP NULL,
        stopped_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT
    )";
    
    if (mysqli_query($conn, $create_sql)) {
        echo "<p style='color: green;'>✓ live_stream table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create table: " . mysqli_error($conn) . "</p>";
    }
}

echo "<p><a href='admin/live-stream-control.php'>Go to Live Stream Control</a></p>";
?>
