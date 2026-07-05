<?php
require_once 'config/database.php';

echo "Checking news_sources table structure...\n";

$result = mysqli_query($conn, 'DESCRIBE news_sources');
if (!$result) {
    echo "Error: news_sources table doesn't exist\n";
    echo "Creating news_sources table...\n";
    
    $create_query = "CREATE TABLE IF NOT EXISTS news_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        rss_url VARCHAR(500) NOT NULL,
        category VARCHAR(100) DEFAULT 'general',
        priority INT DEFAULT 1,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_query)) {
        echo "news_sources table created successfully\n";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "news_sources table exists with columns:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Check if is_active column exists
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM news_sources LIKE 'is_active'");
    if (mysqli_num_rows($check_column) == 0) {
        echo "\nAdding missing is_active column...\n";
        $alter_query = "ALTER TABLE news_sources ADD COLUMN is_active BOOLEAN DEFAULT 1";
        if (mysqli_query($conn, $alter_query)) {
            echo "is_active column added successfully\n";
        } else {
            echo "Error adding column: " . mysqli_error($conn) . "\n";
        }
    }
    
    // Check if priority column exists
    $check_priority = mysqli_query($conn, "SHOW COLUMNS FROM news_sources LIKE 'priority'");
    if (mysqli_num_rows($check_priority) == 0) {
        echo "\nAdding missing priority column...\n";
        $alter_query = "ALTER TABLE news_sources ADD COLUMN priority INT DEFAULT 1";
        if (mysqli_query($conn, $alter_query)) {
            echo "priority column added successfully\n";
        } else {
            echo "Error adding column: " . mysqli_error($conn) . "\n";
        }
    }
}

echo "\nDone.\n";
?>
