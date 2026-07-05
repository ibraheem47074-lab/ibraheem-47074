<?php
require_once 'config/database.php';

echo "Checking news table structure...\n";

// Get news table structure
$columns = mysqli_query($conn, "DESCRIBE news");
echo "News table columns:\n";
$news_columns = [];
while ($col = mysqli_fetch_assoc($columns)) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    $news_columns[] = $col['Field'];
}

// Check for engagement-related columns
$engagement_columns = ['views', 'likes', 'shares', 'comments', 'likes_count', 'share_count', 'comment_count', 'engagement_score'];
echo "\nEngagement-related columns found:\n";
foreach ($engagement_columns as $col) {
    if (in_array($col, $news_columns)) {
        echo "✅ $col\n";
    } else {
        echo "❌ $col\n";
    }
}

// Show sample data
echo "\nSample news data:\n";
$sample = mysqli_query($conn, "SELECT id, title, views FROM news LIMIT 3");
while ($row = mysqli_fetch_assoc($sample)) {
    echo "ID: " . $row['id'] . " - Title: " . substr($row['title'], 0, 50) . "...\n";
    echo "  Views: " . ($row['views'] ?? 'NULL') . "\n";
    echo "---\n";
}

// Check comments table
echo "\nChecking comments table...\n";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ Comments table exists\n";
    $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments");
    $row = mysqli_fetch_assoc($count);
    echo "Total comments: " . $row['count'] . "\n";
} else {
    echo "❌ Comments table does not exist\n";
}
?>
