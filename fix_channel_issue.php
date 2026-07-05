<?php
require_once 'config/database.php';

echo "=== Fixing Channel ID Issues ===\n\n";

// Check if channel_id column exists in news table
echo "1. Checking for channel_id column in news table...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'channel_id'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding channel_id column to news table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE news ADD COLUMN channel_id INT NULL AFTER category_id");
    if ($add_column) {
        echo "✓ channel_id column added to news table\n";
    } else {
        echo "✗ Error adding channel_id column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ channel_id column already exists in news table\n";
}

echo "\n";

// Add foreign key constraint if channels table exists
echo "2. Adding foreign key constraint...\n";
$check_channels = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if (mysqli_num_rows($check_channels) > 0) {
    // Check if foreign key already exists
    $check_fk = mysqli_query($conn, "
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = 'pk_live_news' 
        AND TABLE_NAME = 'news' 
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    
    if (mysqli_num_rows($check_fk) == 0) {
        $add_fk = mysqli_query($conn, "
            ALTER TABLE news 
            ADD CONSTRAINT fk_news_channel 
            FOREIGN KEY (channel_id) REFERENCES channels(id) 
            ON DELETE SET NULL
        ");
        if ($add_fk) {
            echo "✓ Foreign key constraint added\n";
        } else {
            echo "⚠ Foreign key constraint not added (may already exist or channels table structure different)\n";
        }
    } else {
        echo "✓ Foreign key constraint already exists\n";
    }
} else {
    echo "⚠ Channels table not found, skipping foreign key\n";
}

echo "\n";

// Test the channel query
echo "3. Testing channel query...\n";
try {
    $test_query = "
        SELECT ch.name, COUNT(n.id) as news_count 
        FROM channels ch 
        LEFT JOIN news n ON ch.id = n.channel_id 
        GROUP BY ch.id, ch.name 
        ORDER BY news_count DESC 
        LIMIT 5
    ";
    $result = mysqli_query($conn, $test_query);
    if ($result) {
        echo "✓ Channel query executed successfully\n";
        if (mysqli_num_rows($result) > 0) {
            echo "Sample results:\n";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "- " . $row['name'] . ": " . $row['news_count'] . " news items\n";
            }
        } else {
            echo "No results found (normal if no data)\n";
        }
    } else {
        echo "✗ Channel query failed: " . mysqli_error($conn) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Channel query exception: " . $e->getMessage() . "\n";
}

echo "\n=== Channel ID Fixes Complete ===\n";
?>
