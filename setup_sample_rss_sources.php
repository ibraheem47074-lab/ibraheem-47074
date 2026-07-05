<?php
require_once 'config/database.php';

echo "Setting up sample RSS sources...\n";

// Clear existing sources
mysqli_query($conn, "DELETE FROM news_sources");

// Add sample RSS sources
$sources = [
    [
        'name' => 'BBC News Pakistan',
        'rss_url' => 'http://feeds.bbci.co.uk/news/world/south_asia/rss.xml',
        'category' => 'Pakistan',
        'priority' => 10
    ],
    [
        'name' => 'Dawn News',
        'rss_url' => 'https://www.dawn.com/feed/rss/pakistan',
        'category' => 'Pakistan',
        'priority' => 9
    ],
    [
        'name' => 'Geo News',
        'rss_url' => 'https://www.geo.tv/rss/pakistan',
        'category' => 'Pakistan',
        'priority' => 8
    ],
    [
        'name' => 'ARY News',
        'rss_url' => 'https://arynews.tv/en/feed/',
        'category' => 'Pakistan',
        'priority' => 7
    ],
    [
        'name' => 'CNN World',
        'rss_url' => 'http://rss.cnn.com/rss/edition_world.rss',
        'category' => 'World',
        'priority' => 6
    ],
    [
        'name' => 'Reuters World',
        'rss_url' => 'https://www.reuters.com/world/rss.xml',
        'category' => 'World',
        'priority' => 5
    ]
];

foreach ($sources as $source) {
    $name = mysqli_real_escape_string($conn, $source['name']);
    $rss_url = mysqli_real_escape_string($conn, $source['rss_url']);
    $category = mysqli_real_escape_string($conn, $source['category']);
    $priority = $source['priority'];
    
    $insert_query = "INSERT INTO news_sources (name, rss_url, category_id, priority, is_active) 
                     VALUES ('$name', '$rss_url', 1, $priority, 1)";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Added: {$source['name']}\n";
    } else {
        echo "Error adding {$source['name']}: " . mysqli_error($conn) . "\n";
    }
}

echo "\nSetup complete!\n";

// Verify sources were added
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE is_active = 1");
$row = mysqli_fetch_assoc($result);
echo "Total active RSS sources: {$row['count']}\n";
?>
