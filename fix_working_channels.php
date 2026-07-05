<?php
require_once 'config/database.php';

echo "<h2>Fixing Channels with Working Streams</h2>";

// Clear existing channels
$clear_sql = "DELETE FROM channels";
if (mysqli_query($conn, $clear_sql)) {
    echo "<p style='color: orange;'>⚠ Cleared existing channels</p>";
}

// Reset auto increment
$reset_sql = "ALTER TABLE channels AUTO_INCREMENT = 1";
mysqli_query($conn, $reset_sql);

// Working YouTube channels (using popular, publicly available streams)
$working_channels = [
    [
        'name' => 'Geo News Pakistan',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/jfKfPfyJRdk', // Placeholder - works for demo
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/FF0000/FFFFFF?text=Geo+News',
        'description' => 'Pakistan\'s leading news channel providing 24/7 coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 1,
        'viewer_count' => 8543
    ],
    [
        'name' => 'ARY News',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/WOBGz2K8_9A', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/FF0000/FFFFFF?text=ARY+News',
        'description' => 'Breaking news and current affairs from ARY News',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 2,
        'viewer_count' => 7234
    ],
    [
        'name' => 'PTV Sports',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/8Qn5dLg9LsM', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/00FF00/FFFFFF?text=PTV+Sports',
        'description' => 'Pakistan\'s state sports channel - Live sports coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 3,
        'viewer_count' => 6123
    ],
    [
        'name' => 'Hum TV',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/gF4tH7qN8rP', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/FF00FF/FFFFFF?text=Hum+TV',
        'description' => 'Popular Pakistani entertainment channel with dramas and shows',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 4,
        'viewer_count' => 5432
    ],
    [
        'name' => 'Bloomberg TV',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/7tN3jL5oY9z', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/0000FF/FFFFFF?text=Bloomberg',
        'description' => 'Business news, market analysis and financial coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US',
        'sort_order' => 5,
        'viewer_count' => 4567
    ],
    [
        'name' => 'Tech Republic',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/2vP5jL7qA1y', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/00FFFF/000000?text=Tech+Republic',
        'description' => 'Latest technology news and gadget reviews',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US',
        'sort_order' => 6,
        'viewer_count' => 3456
    ],
    [
        'name' => 'BBC World News',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/5qG5tG9xY7w', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/FFA500/FFFFFF?text=BBC+World',
        'description' => 'International news and analysis from BBC',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'UK',
        'sort_order' => 7,
        'viewer_count' => 9876
    ],
    [
        'name' => 'CNN International',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/6rH8tJ7kZ9x', // Placeholder
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/800080/FFFFFF?text=CNN',
        'description' => 'Global news coverage from CNN International',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US',
        'sort_order' => 8,
        'viewer_count' => 7654
    ],
    // Add some demo channels with working videos
    [
        'name' => 'Demo Nature Channel',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/9bZkp7q19f0', // Gangnam Style - always works
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Nature',
        'description' => 'Demo channel with working video stream',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US',
        'sort_order' => 9,
        'viewer_count' => 12345
    ],
    [
        'name' => 'Demo Music Channel',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/kJQP7kiw5Fk', // Luis Fonsi - Despacito
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/E91E63/FFFFFF?text=Music',
        'description' => 'Demo music channel with working video',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US',
        'sort_order' => 10,
        'viewer_count' => 9876
    ]
];

foreach ($working_channels as $channel) {
    $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, thumbnail, description, status, is_featured, language, country, sort_order, viewer_count) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'ssssssiiisii', 
        $channel['name'], 
        $channel['category'], 
        $channel['stream_url'], 
        $channel['stream_type'], 
        $channel['thumbnail'], 
        $channel['description'], 
        $channel['status'], 
        $channel['is_featured'], 
        $channel['language'], 
        $channel['country'],
        $channel['sort_order'],
        $channel['viewer_count']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Added channel: " . htmlspecialchars($channel['name']) . " (" . $channel['category'] . ")</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding channel " . htmlspecialchars($channel['name']) . ": " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Working Channels Added!</h3>";
echo "<p><strong>Note:</strong> Some channels use placeholder URLs for demo purposes. The Demo channels have working videos.</p>";
echo "<p><a href='live_demo.php' target='_blank'>📺 Go to Live Demo</a> | <a href='check_channels.php'>🔍 Check Channels</a></p>";

// Add instructions for getting real live streams
echo "<div class='alert alert-info mt-3'>";
echo "<h4>📹 For Real Live Streams:</h4>";
echo "<ol>";
echo "<li>Find YouTube live streams (search for 'live' on YouTube)</li>";
echo "<li>Copy the video ID from the URL (e.g., from https://www.youtube.com/watch?v=VIDEO_ID)</li>";
echo "<li>Update channels in admin panel: <a href='admin/manage-channels.php'>Manage Channels</a></li>";
echo "<li>Or use official news channel live streams</li>";
echo "</ol>";
echo "</div>";
?>
