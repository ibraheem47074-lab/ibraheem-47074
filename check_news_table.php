<?php
require_once 'config/database.php';

echo "Checking news table structure...\n";
echo "================================\n\n";

$result = mysqli_query($conn, 'DESCRIBE news');
if ($result) {
    echo "Current columns in news table:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n\nChecking if news_type column exists...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($check_column) > 0) {
    echo "✓ news_type column exists\n";
} else {
    echo "✗ news_type column is MISSING\n";
    echo "Adding news_type column...\n";
    
    $add_column = "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual' AFTER status";
    if (mysqli_query($conn, $add_column)) {
        echo "✓ news_type column added successfully\n";
    } else {
        echo "✗ Error adding news_type column: " . mysqli_error($conn) . "\n";
    }
}

echo "\n\nChecking news_sources table...\n";
$sources_check = mysqli_query($conn, 'SELECT COUNT(*) as count FROM news_sources WHERE status = "active"');
if ($sources_check) {
    $row = mysqli_fetch_assoc($sources_check);
    echo "Active RSS sources: " . $row['count'] . "\n";
    
    $sources_list = mysqli_query($conn, 'SELECT name, url, rss_url FROM news_sources WHERE status = "active" LIMIT 5');
    echo "\nSample RSS sources:\n";
    while ($source = mysqli_fetch_assoc($sources_list)) {
        echo "- " . $source['name'] . ": " . $source['rss_url'] . "\n";
    }
}

echo "\nDone!\n";
?>
