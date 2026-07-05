<?php
require_once 'config/database.php';

echo "Checking table collations...\n";

// Check news_sources table collation
echo "\n=== news_sources table ===";
$result = mysqli_query($conn, "SHOW CREATE TABLE news_sources");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "\n" . $row['Create Table'] . "\n";
}

// Check categories table collation
echo "\n=== categories table ===";
$result = mysqli_query($conn, "SHOW CREATE TABLE categories");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "\n" . $row['Create Table'] . "\n";
}

// Check news table collation for source_url column
echo "\n=== news table (source_url column) ===";
$result = mysqli_query($conn, "SHOW CREATE TABLE news");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "\n" . $row['Create Table'] . "\n";
}

// Check specific column collations
echo "\n=== Column collations ===";
$columns_to_check = [
    'news_sources.rss_url',
    'news.source_url',
    'categories.name'
];

foreach ($columns_to_check as $column) {
    $parts = explode('.', $column);
    $table = $parts[0];
    $col = $parts[1];
    
    $result = mysqli_query($conn, "SELECT COLLATION_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$col'");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo "\n$column: " . $row['COLLATION_NAME'];
    }
}

echo "\n\nCollation check complete.\n";
?>
