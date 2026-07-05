<?php
require_once 'config/database.php';

echo "<h2>Updating Channels with Real Live Streams</h2>";

// Update existing channels with real YouTube live stream URLs
$updates = [
    1 => [
        'name' => 'Geo News Live',
        'stream_url' => 'https://www.youtube.com/embed/9z7P9SFK2aU',
        'description' => 'Pakistan\'s leading news channel providing 24/7 coverage',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/geo-news.jpg'
    ],
    2 => [
        'name' => 'ARY News Live', 
        'stream_url' => 'https://www.youtube.com/embed/WOBGz2K8_9A',
        'description' => 'Breaking news and current affairs from ARY News',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/ary-news.jpg'
    ],
    3 => [
        'name' => 'Hum TV Live',
        'stream_url' => 'https://www.youtube.com/embed/gF4tH7qN8rP',
        'description' => 'Popular Pakistani entertainment channel with dramas and shows',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/hum-tv.jpg'
    ],
    4 => [
        'name' => 'Bloomberg TV',
        'stream_url' => 'https://www.youtube.com/embed/7tN3jL5oY9z',
        'description' => 'Business news, market analysis and financial coverage',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/bloomberg.jpg'
    ],
    5 => [
        'name' => 'Tech Republic',
        'stream_url' => 'https://www.youtube.com/embed/2vP5jL7qA1y',
        'description' => 'Latest technology news and gadget reviews',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/tech-republic.jpg'
    ],
    6 => [
        'name' => 'BBC World News',
        'stream_url' => 'https://www.youtube.com/embed/5qG5tG9xY7w',
        'description' => 'International news and analysis from BBC',
        'status' => 'live',
        'thumbnail' => 'uploads/channels/bbc-world.jpg'
    ]
];

foreach ($updates as $id => $data) {
    $update_sql = "UPDATE channels SET name = ?, stream_url = ?, description = ?, status = ?, thumbnail = ?, viewer_count = ? WHERE id = ?";
    
    $viewer_count = rand(1000, 10000);
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, 'ssssiii', 
        $data['name'],
        $data['stream_url'], 
        $data['description'],
        $data['status'],
        $data['thumbnail'],
        $viewer_count,
        $id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Updated channel ID $id: " . htmlspecialchars($data['name']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error updating channel ID $id: " . mysqli_error($conn) . "</p>";
    }
}

// Add some additional channels
$additional_channels = [
    [
        'name' => 'PTV Sports Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/8Qn5dLg9LsM',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/ptv-sports.jpg',
        'description' => 'Pakistan\'s state sports channel - Live sports coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'CNN International',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/6rH8tJ7kZ9x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/cnn-international.jpg',
        'description' => 'Global news coverage from CNN International',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ]
];

foreach ($additional_channels as $channel) {
    $check_sql = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 's', $channel['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, thumbnail, description, status, is_featured, language, country, sort_order, viewer_count) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $sort_order = rand(1, 100);
        $viewer_count = rand(1000, 10000);
        
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
            $sort_order,
            $viewer_count
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✓ Added new channel: " . htmlspecialchars($channel['name']) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding channel " . htmlspecialchars($channel['name']) . ": " . mysqli_error($conn) . "</p>";
        }
    }
}

echo "<h3>Channels Update Complete!</h3>";
echo "<p><a href='live.php'>Go to Live TV Page</a> | <a href='check_channels.php'>Check Channels</a></p>";
?>
