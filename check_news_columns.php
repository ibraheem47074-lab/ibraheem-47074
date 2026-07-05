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

// Check for any channel-related columns
echo "\nChannel-related columns:\n";
$channel_related = ['channel_id', 'source', 'source_url', 'channel', 'news_source'];
foreach ($channel_related as $col) {
    if (in_array($col, $news_columns)) {
        echo "✅ Found: $col\n";
    } else {
        echo "❌ Not found: $col\n";
    }
}

// Show sample news data
echo "\nSample news data:\n";
$sample = mysqli_query($conn, "SELECT id, title, status FROM news LIMIT 3");
while ($row = mysqli_fetch_assoc($sample)) {
    echo "ID: " . $row['id'] . " - " . substr($row['title'], 0, 50) . "...\n";
    echo "  Status: " . ($row['status'] ?? 'NULL') . "\n";
    echo "---\n";
}

// Check channels structure
echo "\nChannels table structure:\n";
$channels_columns = mysqli_query($conn, "DESCRIBE channels");
while ($col = mysqli_fetch_assoc($channels_columns)) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
