<?php
require_once 'config/database.php';

echo "<h2>Adding Tamasha Channel to Live Streaming</h2>";

// Tamasha channel details
$tamasha_channel = [
    'name' => 'Tamasha Live',
    'category' => 'entertainment',
    'stream_url' => 'https://www.youtube.com/embed/hBbHEqJQhIo', // Working demo video
    'stream_type' => 'youtube',
    'thumbnail' => 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=Tamasha',
    'description' => 'Tamasha - Pakistan\'s leading entertainment channel with dramas, shows, and movies',
    'status' => 'live',
    'is_featured' => 1,
    'language' => 'ur',
    'country' => 'PK',
    'sort_order' => 0, // Will be first
    'viewer_count' => 12543 // High viewers for popular channel
];

// Check if Tamasha already exists
$check_sql = "SELECT id FROM channels WHERE name LIKE '%Tamasha%'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: blue;'>ℹ Tamasha channel already exists. Updating...</p>";
    
    // Update existing Tamasha channel
    $update_sql = "UPDATE channels SET 
                   name = ?, 
                   category = ?, 
                   stream_url = ?, 
                   stream_type = ?, 
                   thumbnail = ?, 
                   description = ?, 
                   status = ?, 
                   is_featured = ?, 
                   language = ?, 
                   country = ?,
                   sort_order = ?,
                   viewer_count = ?
                   WHERE name LIKE '%Tamasha%'";
    
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, 'ssssssiiisii', 
        $tamasha_channel['name'],
        $tamasha_channel['category'],
        $tamasha_channel['stream_url'],
        $tamasha_channel['stream_type'],
        $tamasha_channel['thumbnail'],
        $tamasha_channel['description'],
        $tamasha_channel['status'],
        $tamasha_channel['is_featured'],
        $tamasha_channel['language'],
        $tamasha_channel['country'],
        $tamasha_channel['sort_order'],
        $tamasha_channel['viewer_count']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Tamasha channel updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error updating Tamasha: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>➕ Adding new Tamasha channel...</p>";
    
    // Insert new Tamasha channel
    $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, thumbnail, description, status, is_featured, language, country, sort_order, viewer_count) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'ssssssiiisii', 
        $tamasha_channel['name'], 
        $tamasha_channel['category'], 
        $tamasha_channel['stream_url'], 
        $tamasha_channel['stream_type'], 
        $tamasha_channel['thumbnail'], 
        $tamasha_channel['description'], 
        $tamasha_channel['status'], 
        $tamasha_channel['is_featured'], 
        $tamasha_channel['language'], 
        $tamasha_channel['country'],
        $tamasha_channel['sort_order'],
        $tamasha_channel['viewer_count']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Tamasha channel added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding Tamasha: " . mysqli_error($conn) . "</p>";
    }
}

// Also add some other popular Pakistani entertainment channels
$other_channels = [
    [
        'name' => 'Hum Sitaray Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/9bZkp7q19f0', // Working demo
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/9C27B0/FFFFFF?text=Hum+Sitaray',
        'description' => 'Hum Sitaray - Premium entertainment with dramas and shows',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 10,
        'viewer_count' => 6543
    ],
    [
        'name' => 'ARY Musik Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/kJQP7kiw5Fk', // Working demo
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/E91E63/FFFFFF?text=ARY+Musik',
        'description' => 'ARY Musik - Music videos and entertainment programs',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 11,
        'viewer_count' => 5432
    ],
    [
        'name' => 'TV One Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/16lR2fCj1bU', // Working demo
        'stream_type' => 'youtube',
        'thumbnail' => 'https://via.placeholder.com/300x200/FF5722/FFFFFF?text=TV+One',
        'description' => 'TV One - Entertainment channel with dramas and talk shows',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK',
        'sort_order' => 12,
        'viewer_count' => 4321
    ]
];

foreach ($other_channels as $channel) {
    // Check if channel already exists
    $check_sql = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 's', $channel['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
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
            echo "<p style='color: green;'>✅ Added channel: " . htmlspecialchars($channel['name']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding " . htmlspecialchars($channel['name']) . ": " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ Channel already exists: " . htmlspecialchars($channel['name']) . "</p>";
    }
}

echo "<h3>🎉 Tamasha and Entertainment Channels Added!</h3>";
echo "<div class='alert alert-success'>";
echo "<h4>📺 Added Channels:</h4>";
echo "<ul>";
echo "<li><strong>Tamasha Live</strong> - Featured entertainment channel</li>";
echo "<li><strong>Hum Sitaray Live</strong> - Premium entertainment</li>";
echo "<li><strong>ARY Musik Live</strong> - Music and entertainment</li>";
echo "<li><strong>TV One Live</strong> - Dramas and talk shows</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='live_demo.php' target='_blank'>📺 Watch Live Now</a> | <a href='test_live_streams.php' target='_blank'>🧪 Test Streams</a></p>";

// Show current channel count
$count_sql = "SELECT COUNT(*) as total FROM channels";
$result = mysqli_query($conn, $count_sql);
$row = mysqli_fetch_assoc($result);
echo "<p><strong>Total channels in system:</strong> " . $row['total'] . "</p>";
?>
