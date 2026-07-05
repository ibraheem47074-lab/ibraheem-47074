<?php
require_once 'config/database.php';

// Check if live_stream table exists and show its structure
echo "<h2>Live Stream Table Check</h2>";

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_stream'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Live Stream table exists<br>";
    
    // Show table structure
    $result = mysqli_query($conn, "DESCRIBE live_stream");
    echo "<h3>Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'><tr style='background: #f0f0f0;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if stream_key column exists
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM live_stream LIKE 'stream_key'");
    if (mysqli_num_rows($column_check) == 0) {
        echo "<br><h3>❌ stream_key column does not exist. Adding it...</h3>";
        
        // Add stream_key column
        $alter_query = "ALTER TABLE live_stream ADD COLUMN stream_key VARCHAR(255) NULL AFTER stream_url";
        if (mysqli_query($conn, $alter_query)) {
            echo "✅ stream_key column added successfully!<br>";
        } else {
            echo "❌ Error adding stream_key column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "<br><h3>✅ stream_key column already exists</h3>";
    }
    
    // Show updated structure
    echo "<h3>Updated Table Structure:</h3>";
    $result = mysqli_query($conn, "DESCRIBE live_stream");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'><tr style='background: #f0f0f0;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "❌ Live Stream table does not exist. Creating it...";
    
    // Create the table with all required columns
    $create_query = "CREATE TABLE live_stream (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        stream_url VARCHAR(500) NULL,
        stream_key VARCHAR(255) NULL,
        status ENUM('active', 'inactive', 'live') DEFAULT 'inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    if (mysqli_query($conn, $create_query)) {
        echo "✅ Live Stream table created successfully!<br>";
    } else {
        echo "❌ Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br><h3>Test Live Stream Control</h3>";
echo "<a href='admin/live-stream-control.php'>Go to Live Stream Control</a>";
?>
