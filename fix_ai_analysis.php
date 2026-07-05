<?php
require_once 'config/database.php';

echo "=== Fixing AI Analysis Issues ===\n\n";

// Check news table structure
echo "1. Checking news table structure...\n";
$result = mysqli_query($conn, "DESCRIBE news");
if ($result) {
    echo "News table columns:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n";

// Check if source_name column exists in news table
echo "2. Checking for source_name column in news table...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_name'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding source_name column to news table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE news ADD COLUMN source_name VARCHAR(255) NULL");
    if ($add_column) {
        echo "✓ source_name column added to news table\n";
    } else {
        echo "✗ Error adding source_name column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ source_name column already exists in news table\n";
}

echo "\n";

// Check if source_name column exists in articles table
echo "3. Checking for source_name column in articles table...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE 'source_name'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding source_name column to articles table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE articles ADD COLUMN source_name VARCHAR(255) NULL AFTER source");
    if ($add_column) {
        echo "✓ source_name column added to articles table\n";
    } else {
        echo "✗ Error adding source_name column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ source_name column already exists in articles table\n";
}

echo "\n";

// Update existing records with default source_name values
echo "4. Updating existing records with source_name values...\n";

// Update news table
$update_news = mysqli_query($conn, "UPDATE news SET source_name = 'PK Live News' WHERE source_name IS NULL OR source_name = ''");
if ($update_news) {
    $affected = mysqli_affected_rows($conn);
    echo "✓ Updated $affected news records with source_name\n";
} else {
    echo "✗ Error updating news source_name: " . mysqli_error($conn) . "\n";
}

// Update articles table
$update_articles = mysqli_query($conn, "UPDATE articles SET source_name = 'PK Live News' WHERE source_name IS NULL OR source_name = ''");
if ($update_articles) {
    $affected = mysqli_affected_rows($conn);
    echo "✓ Updated $articles article records with source_name\n";
} else {
    echo "✗ Error updating articles source_name: " . mysqli_error($conn) . "\n";
}

echo "\n=== AI Analysis Fixes Complete ===\n";
?>
