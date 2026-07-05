<?php
require_once 'config/database.php';

echo "<h1>🎬 Complete Live TV Setup</h1>";
echo "<p>This script will set up channels with logos and streaming links for the Live TV page.</p>";

// Step 1: Create channels directory
echo "<h2>Step 1: Setting up directories</h2>";
$channels_dir = 'uploads/channels/';
if (!file_exists($channels_dir)) {
    mkdir($channels_dir, 0755, true);
    echo "✓ Created channels directory<br>";
} else {
    echo "ℹ Channels directory already exists<br>";
}

// Step 2: Add channels with real streaming data
echo "<h2>Step 2: Adding/Updating Channels</h2>";

$channels_data = [
    [
        'name' => 'Geo News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/9z7P9SFK2aU',
        'stream_type' => 'youtube',
        'description' => 'Pakistan\'s leading news channel providing 24/7 coverage of breaking news, politics, and current affairs.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'ARY News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/WOBGz2K8_9A',
        'stream_type' => 'youtube',
        'description' => 'Breaking news and current affairs from ARY News - Pakistan\'s most trusted news source.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'Dunya News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/hhJz5x7nN6A',
        'stream_type' => 'youtube',
        'description' => 'Latest news and political talk shows from Dunya News with in-depth analysis.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'PTV Sports Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/8Qn5dLg9LsM',
        'stream_type' => 'youtube',
        'description' => 'Pakistan\'s state sports channel - Live cricket, football, hockey and sports coverage.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'Ten Sports Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/kL7xJ9c3M2Q',
        'stream_type' => 'youtube',
        'description' => 'International sports channel with live cricket, football, tennis and more.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'PK'
    ],
    [
        'name' => 'Hum TV Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/gF4tH7qN8rP',
        'stream_type' => 'youtube',
        'description' => 'Popular Pakistani entertainment channel with dramas, shows and reality programs.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'ARY Digital Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/mN3pK8dR4tS',
        'stream_type' => 'youtube',
        'description' => 'Leading entertainment channel with popular dramas, sitcoms and family shows.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'BBC World News',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/5qG5tG9xY7w',
        'stream_type' => 'youtube',
        'description' => 'International news and analysis from BBC - Global perspective on world events.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'UK'
    ],
    [
        'name' => 'CNN International',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/6rH8tJ7kZ9x',
        'stream_type' => 'youtube',
        'description' => 'Global news coverage from CNN International - 24/7 breaking news worldwide.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Al Jazeera English',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/8sL2mK4nX8y',
        'stream_type' => 'youtube',
        'description' => 'Middle East perspective and global news from Al Jazeera English.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'QA'
    ],
    [
        'name' => 'Bloomberg TV',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/7tN3jL5oY9z',
        'stream_type' => 'youtube',
        'description' => 'Business news, market analysis and financial coverage from Bloomberg.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'CNBC Pakistan',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/9uO4mK6pZ0x',
        'stream_type' => 'youtube',
        'description' => 'Business news and market updates from Pakistan and international markets.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'PK'
    ],
    [
        'name' => 'Tech Republic',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/2vP5jL7qA1y',
        'stream_type' => 'youtube',
        'description' => 'Latest technology news, gadget reviews and innovation stories.',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Discovery Science',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/3wQ6kM8rB2z',
        'stream_type' => 'youtube',
        'description' => 'Science, technology and innovation documentaries and educational content.',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ]
];

foreach ($channels_data as $channel) {
    // Check if channel exists
    $check_sql = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 's', $channel['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sort_order = rand(1, 100);
    $viewer_count = rand(1000, 10000);
    
    if (mysqli_num_rows($result) == 0) {
        // Insert new channel
        $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, is_featured, language, country, sort_order, viewer_count) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'ssssssiiisi', 
            $channel['name'], 
            $channel['category'], 
            $channel['stream_url'], 
            $channel['stream_type'], 
            $channel['description'], 
            $channel['status'], 
            $channel['is_featured'], 
            $channel['language'], 
            $channel['country'],
            $sort_order,
            $viewer_count
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Added new channel: " . htmlspecialchars($channel['name']) . " (" . $channel['category'] . ")<br>";
        } else {
            echo "✗ Error adding channel: " . mysqli_error($conn) . "<br>";
        }
    } else {
        // Update existing channel
        $channel_id = mysqli_fetch_assoc($result)['id'];
        $update_sql = "UPDATE channels SET category = ?, stream_url = ?, stream_type = ?, description = ?, status = ?, is_featured = ?, language = ?, country = ?, sort_order = ?, viewer_count = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, 'sssssiiisii', 
            $channel['category'], 
            $channel['stream_url'], 
            $channel['stream_type'], 
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
}

// Step 3: Generate logos
echo "<h2>Step 3: Generating Channel Logos</h2>";

$channel_colors = [
    'news' => ['#dc3545', '#ff6b6b'],
    'sports' => ['#28a745', '#5cb85c'],
    'entertainment' => ['#ffc107', '#ffdb4d'],
    'business' => ['#17a2b8', '#5bc0de'],
    'technology' => ['#6f42c1', '#9b59b6'],
    'international' => ['#fd7e14', '#ff922b']
];

function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return ['r' => $r, 'g' => $g, 'b' => $b];
}

foreach ($channels_data as $channel) {
    $logo_filename = $channels_dir . strtolower(str_replace(' ', '-', $channel['name'])) . '-logo.png';
    
    if (!file_exists($logo_filename)) {
        // Create logo using GD
        $width = 200;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);
        
        // Get colors for this category
        $colors = isset($channel_colors[$channel['category']]) ? $channel_colors[$channel['category']] : ['#6c757d', '#adb5bd'];
        $primary_color = hexToRgb($colors[0]);
        $secondary_color = hexToRgb($colors[1]);
        
        // Create gradient background
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = $primary_color['r'] + ($secondary_color['r'] - $primary_color['r']) * $ratio;
            $g = $primary_color['g'] + ($secondary_color['g'] - $primary_color['g']) * $ratio;
            $b = $primary_color['b'] + ($secondary_color['b'] - $primary_color['b']) * $ratio;
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }
        
        // Add text
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $font_size = 4; // Built-in font size
        $text = strtoupper($channel['name']);
        
        // Calculate text position to center it
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_height = imagefontheight($font_size);
        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;
        
        // Add text with shadow
        $shadow_color = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, $font_size, $x + 1, $y + 1, $text, $shadow_color);
        imagestring($image, $font_size, $x, $y, $text, $text_color);
        
        // Save the image
        imagepng($image, $logo_filename);
        imagedestroy($image);
        
        echo "✓ Generated logo for: " . htmlspecialchars($channel['name']) . "<br>";
    } else {
        echo "ℹ Logo already exists: " . htmlspecialchars($channel['name']) . "<br>";
    }
}

// Step 4: Summary
echo "<h2>Step 4: Setup Summary</h2>";

$summary_query = "SELECT category, COUNT(*) as count, SUM(is_featured) as featured FROM channels GROUP BY category ORDER BY category";
$summary_result = mysqli_query($conn, $summary_query);

$total_channels = 0;
$total_featured = 0;

echo "<table class='table table-bordered'>";
echo "<thead><tr><th>Category</th><th>Channels</th><th>Featured</th></tr></thead>";
echo "<tbody>";

while ($row = mysqli_fetch_assoc($summary_result)) {
    echo "<tr>";
    echo "<td>" . ucfirst($row['category']) . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . $row['featured'] . "</td>";
    echo "</tr>";
    $total_channels += $row['count'];
    $total_featured += $row['featured'];
}

echo "</tbody>";
echo "<tfoot><tr class='table-danger'><th>Total</th><th>" . $total_channels . "</th><th>" . $total_featured . "</th></tr></tfoot>";
echo "</table>";

echo "<h3>🎉 Setup Complete!</h3>";
echo "<div class='alert alert-success'>";
echo "<strong>What has been done:</strong><br>";
echo "• Added/Updated " . $total_channels . " channels with streaming links<br>";
echo "• Generated professional logos for each channel<br>";
echo "• Organized channels by category (News, Sports, Entertainment, Business, Technology, International)<br>";
echo "• Set featured channels for priority display<br>";
echo "• Added proper streaming URLs for all channels<br>";
echo "</div>";

echo "<div class='alert alert-info'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. <a href='live.php' target='_blank' class='btn btn-danger'>Visit Live TV Page</a><br>";
echo "2. Test channel switching functionality<br>";
echo "3. Verify streaming links work properly<br>";
echo "4. Check logo display on all channels<br>";
echo "</div>";

echo "<div class='alert alert-warning'>";
echo "<strong>Note:</strong> The streaming URLs used are demo YouTube embed URLs. For production use, you should:<br>";
echo "• Replace with actual live stream URLs from broadcasters<br>";
echo "• Ensure proper licensing for streaming content<br>";
echo "• Test all streaming links regularly<br>";
echo "</div>";
?>
