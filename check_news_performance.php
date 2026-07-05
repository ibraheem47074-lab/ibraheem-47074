<?php
require_once 'config/database.php';

echo "Checking news performance API data...\n";

// Check if news_sources table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($result) > 0) {
    echo "news_sources table exists\n";
    $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources");
    $row = mysqli_fetch_assoc($count);
    echo "Total news sources: " . $row['count'] . "\n";
    
    // Show sample data
    $sample = mysqli_query($conn, "SELECT * FROM news_sources LIMIT 3");
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "Source: " . $row['name'] . "\n";
    }
} else {
    echo "news_sources table does not exist\n";
}

// Check news table structure and data
echo "\nChecking news table...\n";
$news_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$news_row = mysqli_fetch_assoc($news_count);
echo "Total news articles: " . $news_row['count'] . "\n";

// Check news table columns
$columns = mysqli_query($conn, "DESCRIBE news");
echo "News table columns:\n";
while ($col = mysqli_fetch_assoc($columns)) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Check if channels table exists (used in news_map.php)
$result = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if (mysqli_num_rows($result) > 0) {
    echo "\nchannels table exists\n";
    $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM channels");
    $row = mysqli_fetch_assoc($count);
    echo "Total channels: " . $row['count'] . "\n";
    
    // Show sample channels with news counts
    $sample = mysqli_query($conn, "
        SELECT ch.name, COUNT(n.id) as news_count 
        FROM channels ch 
        LEFT JOIN news n ON ch.id = n.channel_id 
        GROUP BY ch.id, ch.name 
        ORDER BY news_count DESC 
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "Channel: " . $row['name'] . " - News: " . $row['news_count'] . "\n";
    }
} else {
    echo "\nchannels table does not exist\n";
}

// Test current API query
echo "\nTesting current API query...\n";
try {
    $query = "
        SELECT 
            ns.id,
            ns.name,
            ns.url,
            ns.total_articles,
            ns.total_articles_scraped,
            ns.last_scraped,
            ns.status,
            ns.error_count,
            COUNT(n.id) as published_articles,
            COALESCE(SUM(n.views), 0) as total_views,
            COALESCE(SUM(n.likes_count), 0) as total_likes,
            COALESCE(SUM(n.share_count), 0) as total_shares,
            COALESCE(SUM(n.comment_count), 0) as total_comments,
            COALESCE(AVG(n.engagement_score), 0) as avg_engagement,
            COALESCE(AVG(n.sentiment_score), 0) as avg_sentiment,
            MAX(n.published_at) as last_published,
            c.name as category_name
        FROM news_sources ns
        LEFT JOIN news n ON (ns.url = n.source_url OR ns.url LIKE CONCAT('%', n.source_url) OR n.source_url LIKE CONCAT('%', ns.url))
            AND n.status = 'published'
        LEFT JOIN categories c ON ns.category_id = c.id
        GROUP BY ns.id, ns.name, ns.url, ns.total_articles, ns.total_articles_scraped, 
                 ns.last_scraped, ns.status, ns.error_count, c.name
        ORDER BY published_articles DESC, total_views DESC
        LIMIT 5
    ";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        echo "Query executed successfully\n";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "Source: " . $row['name'] . " - Articles: " . $row['published_articles'] . "\n";
            }
        } else {
            echo "No results returned\n";
        }
    } else {
        echo "Query failed: " . mysqli_error($conn) . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
