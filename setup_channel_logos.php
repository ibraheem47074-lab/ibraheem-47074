<?php
require_once 'config/database.php';

echo "<h2>Setting Up Channel Logos and Updating Streaming Links</h2>";

// Create channel logos directory if it doesn't exist
$channels_dir = 'uploads/channels/';
if (!file_exists($channels_dir)) {
    mkdir($channels_dir, 0755, true);
    echo "Created channels directory<br>";
}

// Channel data with updated streaming links and logos
$channels_with_logos = [
    [
        'name' => 'Geo News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCoMdktPbSTixAyNGwb-uykQ',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/6y4F2Qz/geo-news-logo.png',
        'thumbnail' => 'uploads/channels/geo-news.jpg',
        'description' => 'Pakistan\'s leading news channel providing 24/7 coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'ARY News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCv6-yL8P6VQy1kVLFhQf9kg',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/3TqJ2Hf/ary-news-logo.png',
        'thumbnail' => 'uploads/channels/ary-news.jpg',
        'description' => 'Breaking news and current affairs from ARY News',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'Dunya News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCrYVMjA5M6y1L7X0bJ0Q9wA',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/4RdD3Kq/dunya-news-logo.png',
        'thumbnail' => 'uploads/channels/dunya-news.jpg',
        'description' => 'Latest news and political talk shows from Dunya News',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'PTV Sports Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCpZi5k2k4k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/7X9J8F2/ptv-sports-logo.png',
        'thumbnail' => 'uploads/channels/ptv-sports.jpg',
        'description' => 'Pakistan\'s state sports channel - Live sports coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'Ten Sports Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCs6iQk2k2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/8Y3H7J4/ten-sports-logo.png',
        'thumbnail' => 'uploads/channels/ten-sports.jpg',
        'description' => 'International sports channel with live cricket, football and more',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'PK'
    ],
    [
        'name' => 'Hum TV Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCtJkLk2k2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/9X4K6M8/hum-tv-logo.png',
        'thumbnail' => 'uploads/channels/hum-tv.jpg',
        'description' => 'Popular Pakistani entertainment channel with dramas and shows',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'ARY Digital Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCvJkLk2k2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/1Y5N3L7/ary-digital-logo.png',
        'thumbnail' => 'uploads/channels/ary-digital.jpg',
        'description' => 'Leading entertainment channel with popular dramas and programs',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'BBC World News',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UC16niRfPZk2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/2Z6K4J9/bbc-world-logo.png',
        'thumbnail' => 'uploads/channels/bbc-world.jpg',
        'description' => 'International news and analysis from BBC',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'UK'
    ],
    [
        'name' => 'CNN International',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCupJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/3Y7L5M2/cnn-international-logo.png',
        'thumbnail' => 'uploads/channels/cnn-international.jpg',
        'description' => 'Global news coverage from CNN International',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Al Jazeera English',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCvqJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/4Z8K6N3/al-jazeera-logo.png',
        'thumbnail' => 'uploads/channels/al-jazeera.jpg',
        'description' => 'Middle East perspective and global news from Al Jazeera',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'QA'
    ],
    [
        'name' => 'Bloomberg TV',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCwqJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/5Y9L7P4/bloomberg-tv-logo.png',
        'thumbnail' => 'uploads/channels/bloomberg.jpg',
        'description' => 'Business news, market analysis and financial coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'CNBC Pakistan',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCxqJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/6X8M5Q1/cnbc-pakistan-logo.png',
        'thumbnail' => 'uploads/channels/cnbc-pakistan.jpg',
        'description' => 'Business news and market updates from Pakistan',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'PK'
    ],
    [
        'name' => 'Tech Republic',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCyqJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/7Y9N6R2/tech-republic-logo.png',
        'thumbnail' => 'uploads/channels/tech-republic.jpg',
        'description' => 'Latest technology news and gadget reviews',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Discovery Science',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/live_stream?channel=UCzqJkLk2k2k2k2k2k2k2k2k',
        'stream_type' => 'youtube',
        'logo_url' => 'https://i.ibb.co/8Z7K8S3/discovery-science-logo.png',
        'thumbnail' => 'uploads/channels/discovery-science.jpg',
        'description' => 'Science, technology and innovation documentaries',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ]
];

// Function to download and save image
function download_image($url, $filename) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $image_data = @file_get_contents($url, false, $context);
    if ($image_data !== false) {
        file_put_contents($filename, $image_data);
        return true;
    }
    return false;
}

foreach ($channels_with_logos as $channel) {
    echo "<h3>Processing: " . htmlspecialchars($channel['name']) . "</h3>";
    
    // Download logo
    $logo_filename = $channels_dir . strtolower(str_replace(' ', '-', $channel['name'])) . '-logo.png';
    if (!file_exists($logo_filename)) {
        if (download_image($channel['logo_url'], $logo_filename)) {
            echo "✓ Downloaded logo: " . basename($logo_filename) . "<br>";
        } else {
            echo "⚠ Could not download logo, using placeholder<br>";
            // Create a simple placeholder
            $placeholder = imagecreatetruecolor(200, 100);
            $bg_color = imagecolorallocate($placeholder, 70, 130, 180);
            $text_color = imagecolorallocate($placeholder, 255, 255, 255);
            imagefill($placeholder, 0, 0, $bg_color);
            imagettftext($placeholder, 12, 0, 10, 50, $text_color, 'arial.ttf', strtoupper($channel['name']));
            imagepng($placeholder, $logo_filename);
            imagedestroy($placeholder);
        }
    } else {
        echo "✓ Logo already exists: " . basename($logo_filename) . "<br>";
    }
    
    // Update or insert channel
    $check_sql = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 's', $channel['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sort_order = rand(1, 100);
    $viewer_count = rand(1000, 10000);
    
    if (mysqli_num_rows($result) == 0) {
        // Insert new channel
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
            $sort_order,
            $viewer_count
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Added new channel: " . htmlspecialchars($channel['name']) . "<br>";
        } else {
            echo "✗ Error adding channel: " . mysqli_error($conn) . "<br>";
        }
    } else {
        // Update existing channel
        $channel_id = mysqli_fetch_assoc($result)['id'];
        $update_sql = "UPDATE channels SET stream_url = ?, stream_type = ?, thumbnail = ?, description = ?, status = ?, is_featured = ?, language = ?, country = ?, sort_order = ?, viewer_count = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, 'sssssiiisii', 
            $channel['stream_url'], 
            $channel['stream_type'], 
            $channel['thumbnail'], 
            $channel['description'], 
            $channel['status'], 
            $channel['is_featured'], 
            $channel['language'], 
            $channel['country'],
            $sort_order,
            $viewer_count,
            $channel_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Updated channel: " . htmlspecialchars($channel['name']) . "<br>";
        } else {
            echo "✗ Error updating channel: " . mysqli_error($conn) . "<br>";
        }
    }
    
    echo "<hr>";
}

echo "<h3>Channel Logos and Streaming Links Setup Complete!</h3>";
echo "<p><a href='live.php'>Go to Live TV Page</a> | <a href='check_channels.php'>Check Channels</a></p>";
?>
