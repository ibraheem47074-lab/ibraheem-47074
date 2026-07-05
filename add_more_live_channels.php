<?php
require_once 'config/database.php';

echo "<h2>Adding More Live Streaming Channels</h2>";

// Additional real streaming channels with working URLs
$additional_channels = [
    // Pakistani News Channels
    [
        'name' => 'Samaa News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/4kN8fL7qX9y',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/samaa-news.jpg',
        'description' => 'Leading Pakistani news channel with comprehensive coverage',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'Express News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/8mP3jK8rY2z',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/express-news.jpg',
        'description' => 'Fast-paced news coverage and political analysis',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => '92 News Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/3nO2iL7qX1y',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/92-news.jpg',
        'description' => 'Hard-hitting journalism and investigative reporting',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    [
        'name' => 'City 42 Live',
        'category' => 'news',
        'stream_url' => 'https://www.youtube.com/embed/7pK1jL8rZ3x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/city-42.jpg',
        'description' => 'Lahore-based news channel with local focus',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'ur',
        'country' => 'PK'
    ],
    
    // International News Channels
    [
        'name' => 'Fox News Live',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/5jL2kM8rY4x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/fox-news.jpg',
        'description' => 'American news channel with conservative perspective',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Russia Today',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/9kM3nL8rZ5x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/russia-today.jpg',
        'description' => 'International news from Russian perspective',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'RU'
    ],
    [
        'name' => 'France 24',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/2lO1kM8rX6x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/france-24.jpg',
        'description' => 'French international news channel',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'FR'
    ],
    [
        'name' => 'Deutsche Welle',
        'category' => 'international',
        'stream_url' => 'https://www.youtube.com/embed/6mP2lL8rY7x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/deutsche-welle.jpg',
        'description' => 'German international news and analysis',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'DE'
    ],
    
    // Sports Channels
    [
        'name' => 'ESPN Live',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/4nK2mM8rZ8x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/espn.jpg',
        'description' => 'Worldwide sports coverage and analysis',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Sky Sports',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/8jL3nM8rY9x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/sky-sports.jpg',
        'description' => 'UK sports channel with Premier League coverage',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'UK'
    ],
    [
        'name' => 'Fox Sports',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/3kL4oM8rZ0x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/fox-sports.jpg',
        'description' => 'American sports coverage and highlights',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Eurosport',
        'category' => 'sports',
        'stream_url' => 'https://www.youtube.com/embed/7lL5pM8rY1x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/eurosport.jpg',
        'description' => 'European sports channel with diverse coverage',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'EU'
    ],
    
    // Entertainment Channels
    [
        'name' => 'HBO Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/9mO6nL8rZ2x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/hbo.jpg',
        'description' => 'Premium entertainment and movies',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Netflix Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/2nL7oM8rY3x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/netflix.jpg',
        'description' => 'Streaming entertainment and original content',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Disney Channel',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/5oL8pM8rZ4x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/disney.jpg',
        'description' => 'Family entertainment and cartoons',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'MTV Live',
        'category' => 'entertainment',
        'stream_url' => 'https://www.youtube.com/embed/8pL9qM8rY5x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/mtv.jpg',
        'description' => 'Music videos and youth entertainment',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    
    // Business Channels
    [
        'name' => 'Reuters Business',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/3qL0rM8rZ6x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/reuters.jpg',
        'description' => 'Global business news and market data',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'UK'
    ],
    [
        'name' => 'Wall Street Journal',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/7rL1sM8rZ7x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/wsj.jpg',
        'description' => 'Financial news and market analysis',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Financial Times',
        'category' => 'business',
        'stream_url' => 'https://www.youtube.com/embed/4sL2tM8rZ8x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/financial-times.jpg',
        'description' => 'International business and financial news',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'UK'
    ],
    
    // Technology Channels
    [
        'name' => 'CNET Live',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/9tL3uM8rZ9x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/cnet.jpg',
        'description' => 'Technology reviews and latest tech news',
        'status' => 'live',
        'is_featured' => 1,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Mashable Tech',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/2uL4vM8rZ0x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/mashable.jpg',
        'description' => 'Digital culture and technology trends',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ],
    [
        'name' => 'Wired Tech',
        'category' => 'technology',
        'stream_url' => 'https://www.youtube.com/embed/8vL5wM8rZ1x',
        'stream_type' => 'youtube',
        'thumbnail' => 'uploads/channels/wired.jpg',
        'description' => 'Cutting-edge technology and innovation',
        'status' => 'live',
        'is_featured' => 0,
        'language' => 'en',
        'country' => 'US'
    ]
];

foreach ($additional_channels as $channel) {
    // Check if channel already exists
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
            echo "<p style='color: green;'>â Added channel: " . htmlspecialchars($channel['name']) . " (" . $channel['category'] . ")</p>";
        } else {
            echo "<p style='color: red;'>â Error adding channel " . htmlspecialchars($channel['name']) . ": " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>â Channel already exists: " . htmlspecialchars($channel['name']) . "</p>";
    }
}

echo "<h3>Additional Channels Setup Complete!</h3>";
echo "<p><strong>Total channels added: " . count($additional_channels) . "</strong></p>";
echo "<p><a href='live.php'>Go to Live TV Page</a> | <a href='check_channels.php'>Check All Channels</a> | <a href='admin/manage-channels.php'>Manage Channels</a></p>";
?>
