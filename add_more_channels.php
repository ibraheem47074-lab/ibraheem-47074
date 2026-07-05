<?php
require_once 'config/database.php';

echo "<h2>Adding More Live Channels</h2>";

// Additional popular Pakistani and International channels
$additional_channels = [
    // Pakistani News Channels
    [
        'name' => 'Dunya News',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw', // YouTube embed example
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Leading Pakistani news channel with breaking news and current affairs',
        'language' => 'urdu',
        'is_featured' => 1,
        'sort_order' => 6
    ],
    [
        'name' => 'Samaa TV',
        'category' => 'news', 
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => '24/7 Pakistani news channel with comprehensive coverage',
        'language' => 'urdu',
        'is_featured' => 1,
        'sort_order' => 7
    ],
    [
        'name' => 'Express News',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube', 
        'status' => 'live',
        'description' => 'Fast-paced Pakistani news channel with in-depth analysis',
        'language' => 'urdu',
        'is_featured' => 0,
        'sort_order' => 8
    ],
    [
        'name' => '92 News HD',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'HD Pakistani news channel with modern presentation',
        'language' => 'urdu',
        'is_featured' => 0,
        'sort_order' => 9
    ],
    
    // Sports Channels
    [
        'name' => 'PTV Sports',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Pakistan\'s premier sports channel featuring cricket, hockey, and more',
        'language' => 'urdu',
        'is_featured' => 1,
        'sort_order' => 10
    ],
    [
        'name' => 'Ten Sports',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'International sports channel with live matches and highlights',
        'language' => 'english',
        'is_featured' => 0,
        'sort_order' => 11
    ],
    
    // Entertainment Channels
    [
        'name' => 'Hum TV',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Popular Pakistani entertainment channel with dramas and shows',
        'language' => 'urdu',
        'is_featured' => 1,
        'sort_order' => 12
    ],
    [
        'name' => 'ARY Digital',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Leading Pakistani entertainment channel with premium dramas',
        'language' => 'urdu',
        'is_featured' => 1,
        'sort_order' => 13
    ],
    [
        'name' => 'Geo TV',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Popular Pakistani entertainment and drama channel',
        'language' => 'urdu',
        'is_featured' => 0,
        'sort_order' => 14
    ],
    
    // Business Channels
    [
        'name' => 'Business Plus',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Pakistani business news and financial analysis channel',
        'language' => 'urdu',
        'is_featured' => 0,
        'sort_order' => 15
    ],
    
    // International Channels
    [
        'name' => 'BBC World News',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'International news from the BBC with global perspective',
        'language' => 'english',
        'is_featured' => 1,
        'sort_order' => 16
    ],
    [
        'name' => 'CNN International',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => '24/7 international news and current affairs from CNN',
        'language' => 'english',
        'is_featured' => 1,
        'sort_order' => 17
    ],
    [
        'name' => 'Al Jazeera English',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Middle Eastern perspective on global news and events',
        'language' => 'english',
        'is_featured' => 0,
        'sort_order' => 18
    ],
    
    // Religious Channels
    [
        'name' => 'Peace TV',
        'category' => 'religious',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Islamic religious and educational content',
        'language' => 'english',
        'is_featured' => 0,
        'sort_order' => 19
    ],
    
    // Music Channels
    [
        'name' => 'ATV Music',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
        'stream_type' => 'youtube',
        'status' => 'live',
        'description' => 'Pakistani music channel with latest songs and performances',
        'language' => 'urdu',
        'is_featured' => 0,
        'sort_order' => 20
    ]
];

$added_count = 0;
$errors = [];

foreach ($additional_channels as $channel) {
    // Check if channel already exists
    $check_query = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $channel['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // Insert new channel
        $insert_query = "INSERT INTO channels (name, category, stream_url, stream_type, status, description, language, is_featured, sort_order, viewer_count, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_query);
        $viewer_count = rand(1000, 5000);
        mysqli_stmt_bind_param($stmt, 'sssssssiii', 
            $channel['name'], 
            $channel['category'], 
            $channel['stream_url'], 
            $channel['stream_type'], 
            $channel['status'], 
            $channel['description'], 
            $channel['language'], 
            $channel['is_featured'], 
            $channel['sort_order'],
            $viewer_count
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✓ Added channel: " . htmlspecialchars($channel['name']) . "</p>";
            $added_count++;
        } else {
            $errors[] = "Error adding " . $channel['name'] . ": " . mysqli_error($conn);
        }
    } else {
        echo "<p style='color: orange;'>⚠ Channel already exists: " . htmlspecialchars($channel['name']) . "</p>";
    }
}

echo "<h3>Summary:</h3>";
echo "<p style='color: green; font-weight: bold;'>Successfully added $added_count new channels</p>";

if (!empty($errors)) {
    echo "<h4 style='color: red;'>Errors:</h4>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>- $error</p>";
    }
}

// Show updated channel count
$total_query = "SELECT COUNT(*) as total FROM channels";
$total_result = mysqli_query($conn, $total_query);
$total_channels = mysqli_fetch_assoc($total_result)['total'];

echo "<p><strong>Total channels in database: $total_channels</strong></p>";

echo "<p><a href='live.php' class='btn btn-primary'>View Live Channels Page</a></p>";
echo "<p><a href='check_channels.php' class='btn btn-secondary'>Check All Channels</a></p>";
?>
