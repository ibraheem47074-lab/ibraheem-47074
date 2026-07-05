<?php
/**
 * Update RSS feed URLs with working alternatives
 */
require_once __DIR__ . '/config/database.php';

// Working RSS feed URLs
$workingFeeds = [
    'Al Jazeera' => [
        'url' => 'https://www.aljazeera.com/xml/rss/all.xml',
        'category_id' => 1 // International
    ],
    'BBC News' => [
        'url' => 'http://feeds.bbci.co.uk/news/rss.xml',
        'category_id' => 1 // International
    ],
    'BBC Urdu' => [
        'url' => 'https://www.bbc.com/urdu/rss.xml',
        'category_id' => 2 // Pakistan
    ],
    'CNN News' => [
        'url' => 'http://rss.cnn.com/rss/edition.rss',
        'category_id' => 1 // International
    ],
    'Fox News' => [
        'url' => 'https://www.foxnews.com/about/rss',
        'category_id' => 1 // International
    ],
    'Reuters News' => [
        'url' => 'https://www.reuters.com/rssFeed/worldNews',
        'category_id' => 1 // International
    ],
    'The News International' => [
        'url' => 'https://www.thenews.com.pk/rss/',
        'category_id' => 2 // Pakistan
    ],
    'Dawn News' => [
        'url' => 'https://www.dawn.com/feed/',
        'category_id' => 2 // Pakistan
    ],
    'Geo News' => [
        'url' => 'https://www.geo.tv/rss',
        'category_id' => 2 // Pakistan
    ],
    'Express Tribune' => [
        'url' => 'https://tribune.com.pk/feed/',
        'category_id' => 2 // Pakistan
    ]
];

echo "<h2>Updating RSS Feed Sources</h2>";

try {
    // Clear existing RSS sources
    $clearQuery = "DELETE FROM news_sources WHERE type = 'rss'";
    mysqli_query($conn, $clearQuery);
    echo "<p>Cleared existing RSS sources</p>";

    // Insert new working feeds
    $insertQuery = "INSERT INTO news_sources (name, url, type, status, category_id, created_at) VALUES (?, ?, 'rss', 'active', ?, NOW())";
    $stmt = mysqli_prepare($conn, $insertQuery);

    foreach ($workingFeeds as $name => $feed) {
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $feed['url'], $feed['category_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>✓ Added: $name - {$feed['url']}</p>";
        } else {
            echo "<p>✗ Failed to add: $name - " . mysqli_error($conn) . "</p>";
        }
    }

    mysqli_stmt_close($stmt);
    
    echo "<h3>Update Complete!</h3>";
    echo "<p><a href='check_news_sources.php'>View Updated Sources</a></p>";
    echo "<p><a href='test_rss_feeds.php'>Test RSS Feeds</a></p>";
    echo "<p><a href='admin/view_draft_articles.php'>View Draft Articles</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
