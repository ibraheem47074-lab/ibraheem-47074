<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "=== COLLATION CHECK AND FIX VERIFICATION ===\n\n";

// Check specific column collations
echo "1. COLUMN COLLATIONS:\n";
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
        echo "   $column: " . $row['COLLATION_NAME'] . "\n";
    } else {
        echo "   $column: NOT FOUND\n";
    }
}

echo "\n2. TESTING ORIGINAL QUERY (should fail):\n";
$original_query = "SELECT ns.*, c.name as category_name,
                   (SELECT COUNT(*) FROM news WHERE source_url = ns.rss_url) as imported_count,
                   (SELECT MAX(created_at) FROM news WHERE source_url = ns.rss_url) as last_import_date
                   FROM news_sources ns 
                   LEFT JOIN categories c ON ns.category_id = c.id 
                   WHERE ns.type = 'rss' OR ns.type IS NULL
                   ORDER BY ns.created_at DESC LIMIT 1";

$result = mysqli_query($conn, $original_query);
if ($result) {
    echo "   SUCCESS: Original query executed\n";
} else {
    echo "   FAILED: " . mysqli_error($conn) . "\n";
}

echo "\n3. TESTING FIXED QUERY (should work):\n";
$fixed_query = "SELECT ns.*, c.name as category_name,
                (SELECT COUNT(*) FROM news WHERE source_url COLLATE utf8mb4_unicode_ci = ns.rss_url COLLATE utf8mb4_unicode_ci) as imported_count,
                (SELECT MAX(created_at) FROM news WHERE source_url COLLATE utf8mb4_unicode_ci = ns.rss_url COLLATE utf8mb4_unicode_ci) as last_import_date
                FROM news_sources ns 
                LEFT JOIN categories c ON ns.category_id = c.id 
                WHERE ns.type = 'rss' OR ns.type IS NULL
                ORDER BY ns.created_at DESC LIMIT 1";

$result = mysqli_query($conn, $fixed_query);
if ($result) {
    echo "   SUCCESS: Fixed query executed\n";
    if ($row = mysqli_fetch_assoc($result)) {
        echo "   Sample data: " . $row['rss_url'] . " - " . $row['category_name'] . "\n";
    }
} else {
    echo "   FAILED: " . mysqli_error($conn) . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
?>
