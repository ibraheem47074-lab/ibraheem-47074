<?php
/**
 * Fix news_analytics table structure
 */

require_once 'config/database.php';

echo "<h2>Fixing news_analytics table...</h2>";

// Check existing columns
$result = mysqli_query($conn, "SHOW COLUMNS FROM news_analytics");
if (!$result) {
    echo "<p style='color: red;'>Table doesn't exist!</p>";
    exit;
}

$existing_columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $existing_columns[] = $row['Field'];
    echo "<p>Found column: {$row['Field']}</p>";
}

// Add missing columns
$columns_to_add = [
    'unique_views' => "ALTER TABLE news_analytics ADD COLUMN unique_views int(11) DEFAULT 0",
    'avg_time_on_page' => "ALTER TABLE news_analytics ADD COLUMN avg_time_on_page int(11) DEFAULT 0",
    'shares' => "ALTER TABLE news_analytics ADD COLUMN shares int(11) DEFAULT 0",
    'comments' => "ALTER TABLE news_analytics ADD COLUMN comments int(11) DEFAULT 0"
];

foreach ($columns_to_add as $col => $sql) {
    if (!in_array($col, $existing_columns)) {
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color: green;'>✓ Added column: $col</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding $col: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>→ Column $col already exists</p>";
    }
}

echo "<hr><h3>Done!</h3>";
echo "<p><a href='admin/website-performance.php'>Go to Website Performance</a></p>";
echo "<p><strong>Delete this file after use.</strong></p>";
