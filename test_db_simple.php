<?php
// Simple database test
try {
    require_once 'config/database.php';
    echo "Database connection: SUCCESS\n";
    
    // Check tables
    $tables = ['news', 'channels', 'categories', 'news_sources'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        $exists = mysqli_num_rows($result) > 0;
        echo "Table '$table': " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
        
        if ($exists) {
            $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
            $row = mysqli_fetch_assoc($count);
            echo "  Records: " . $row['count'] . "\n";
        }
    }
    
    // Check news table structure
    echo "\nNews table columns:\n";
    $columns = mysqli_query($conn, "DESCRIBE news");
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "- " . $col['Field'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
