<?php
require_once 'config/database.php';
echo 'Checking database tables...' . PHP_EOL;

// Check if news table exists and has data
$result = mysqli_query($conn, 'SHOW TABLES LIKE \'news\'');
if (mysqli_num_rows($result) > 0) {
    echo 'News table exists' . PHP_EOL;
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM news');
    $row = mysqli_fetch_assoc($count);
    echo 'Total news articles: ' . $row['count'] . PHP_EOL;
    
    // Check if news has views and other metrics
    $metrics = mysqli_query($conn, 'SELECT SUM(views) as total_views, SUM(likes) as total_likes, SUM(shares) as total_shares FROM news');
    $metrics_row = mysqli_fetch_assoc($metrics);
    echo 'Total views: ' . $metrics_row['total_views'] . PHP_EOL;
    echo 'Total likes: ' . $metrics_row['total_likes'] . PHP_EOL;
    echo 'Total shares: ' . $metrics_row['total_shares'] . PHP_EOL;
    
    // Check news table structure
    echo 'News table structure:' . PHP_EOL;
    $structure = mysqli_query($conn, 'DESCRIBE news');
    while ($col = mysqli_fetch_assoc($structure)) {
        echo '- ' . $col['Field'] . ' (' . $col['Type'] . ')' . PHP_EOL;
    }
} else {
    echo 'News table does not exist' . PHP_EOL;
}

// Check if channels table exists
$result = mysqli_query($conn, 'SHOW TABLES LIKE \'channels\'');
if (mysqli_num_rows($result) > 0) {
    echo 'Channels table exists' . PHP_EOL;
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM channels');
    $row = mysqli_fetch_assoc($count);
    echo 'Total channels: ' . $row['count'] . PHP_EOL;
    
    // Show some channel data
    $channels = mysqli_query($conn, 'SELECT * FROM channels LIMIT 5');
    while ($channel = mysqli_fetch_assoc($channels)) {
        echo '- ' . $channel['name'] . PHP_EOL;
    }
} else {
    echo 'Channels table does not exist' . PHP_EOL;
}

// Check if news_sources table exists
$result = mysqli_query($conn, 'SHOW TABLES LIKE \'news_sources\'');
if (mysqli_num_rows($result) > 0) {
    echo 'News sources table exists' . PHP_EOL;
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM news_sources');
    $row = mysqli_fetch_assoc($count);
    echo 'Total news sources: ' . $row['count'] . PHP_EOL;
} else {
    echo 'News sources table does not exist' . PHP_EOL;
}

// Show all tables
echo PHP_EOL . 'All tables in database:' . PHP_EOL;
$tables = mysqli_query($conn, 'SHOW TABLES');
while ($table = mysqli_fetch_row($tables)) {
    echo '- ' . $table[0] . PHP_EOL;
}
?>
