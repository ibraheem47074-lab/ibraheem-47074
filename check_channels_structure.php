<?php
require_once 'config/database.php';

echo "Checking channels table structure...\n";

// Check if channels table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ Channels table exists\n";
    
    // Get table structure
    $columns = mysqli_query($conn, "DESCRIBE channels");
    echo "Channels table columns:\n";
    $channel_columns = [];
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        $channel_columns[] = $col['Field'];
    }
    
    // Show sample data
    echo "\nSample channels data:\n";
    $sample = mysqli_query($conn, "SELECT * FROM channels LIMIT 3");
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "Channel ID: " . $row['id'] . "\n";
        foreach ($row as $key => $value) {
            if ($key !== 'id') {
                echo "  $key: $value\n";
            }
        }
        echo "---\n";
    }
    
    // Check if we have news data linked to channels
    echo "\nNews data linked to channels:\n";
    $news_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE channel_id IS NOT NULL");
    $news_row = mysqli_fetch_assoc($news_count);
    echo "News with channel_id: " . $news_row['count'] . "\n";
    
    // Show channel statistics
    echo "\nChannel statistics:\n";
    $stats = mysqli_query($conn, "
        SELECT ch.id, ch.name, COUNT(n.id) as news_count 
        FROM channels ch 
        LEFT JOIN news n ON ch.id = n.channel_id 
        GROUP BY ch.id, ch.name 
        ORDER BY news_count DESC 
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($stats)) {
        echo "Channel: " . $row['name'] . " - News: " . $row['news_count'] . "\n";
    }
    
} else {
    echo "❌ Channels table does not exist\n";
}

// Check news table structure for relevant columns
echo "\nChecking news table structure...\n";
$news_columns = mysqli_query($conn, "DESCRIBE news");
echo "News table columns (relevant ones):\n";
while ($col = mysqli_fetch_assoc($news_columns)) {
    if (in_array($col['Field'], ['id', 'title', 'channel_id', 'views', 'likes', 'shares', 'status', 'published_at'])) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
