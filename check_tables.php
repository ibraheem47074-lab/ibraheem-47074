<?php
require_once 'config/database.php';

// Check which tables exist
$tables_to_check = ['alert_categories', 'news_sources', 'category_analytics'];
foreach ($tables_to_check as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($result) > 0;
    echo $table . ': ' . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}

mysqli_close($conn);
?>
