<?php
require_once 'config/database.php';

$conn = getDatabaseConnection();

// Check if news_analytics table exists
echo "Checking news_analytics table structure...\n";
$result = mysqli_query($conn, 'DESCRIBE news_analytics');

if ($result) {
    echo "Table news_analytics exists with columns:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . ' (' . $row['Type'] . ")\n";
    }
} else {
    echo "Table news_analytics does not exist or error: " . mysqli_error($conn) . "\n";
    
    // Check if there are any similar tables
    echo "\nChecking for similar tables...\n";
    $tables_result = mysqli_query($conn, "SHOW TABLES LIKE '%analytics%'");
    if ($tables_result) {
        while ($row = mysqli_fetch_row($tables_result)) {
            echo "- " . $row[0] . "\n";
        }
    }
    
    $tables_result = mysqli_query($conn, "SHOW TABLES LIKE '%news%'");
    if ($tables_result) {
        echo "\nTables with 'news' in name:\n";
        while ($row = mysqli_fetch_row($tables_result)) {
            echo "- " . $row[0] . "\n";
        }
    }
}

mysqli_close($conn);
?>
