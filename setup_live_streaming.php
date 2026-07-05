<?php
require_once 'config/database.php';

echo "<h2>Setting Up Live Streaming Schedule</h2>";

// Create live_stream table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS live_stream (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    stream_url VARCHAR(500),
    embed_code TEXT,
    schedule_time DATETIME,
    end_time DATETIME,
    status ENUM('scheduled', 'online', 'offline', 'ended') DEFAULT 'scheduled',
    auto_start TINYINT(1) DEFAULT 0,
    category VARCHAR(100),
    thumbnail VARCHAR(500),
    viewer_count INT DEFAULT 0,
    max_viewers INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_table)) {
    echo "<p class='text-success'>✓ Live stream table ready</p>";
} else {
    echo "<p class='text-danger'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
}

// Professional live streaming schedule for Pakistani audience
$live_streams = [
    [
        'title' => 'PK Live News Morning Bulletin - Daily Headlines',
        'description' => 'Start your day with comprehensive coverage of Pakistan\'s most important news stories, political developments, and economic updates.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_morning',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_morning" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d 08:00:00', strtotime('next Monday')),
        'end_time' => date('Y-m-d 09:00:00', strtotime('next Monday')),
        'status' => 'scheduled',
        'auto_start' => 1,
        'category' => 'News Bulletin',
        'thumbnail' => 'uploads/live/morning-bulletin.jpg'
    ],
    [
        'title' => 'Economic Insights - Business & Market Analysis',
        'description' => 'Weekly analysis of Pakistan\'s economic performance, stock market trends, and business opportunities with expert guests.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_economic',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_economic" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d 18:00:00', strtotime('next Tuesday')),
        'end_time' => date('Y-m-d 19:30:00', strtotime('next Tuesday')),
        'status' => 'scheduled',
        'auto_start' => 1,
        'category' => 'Business',
        'thumbnail' => 'uploads/live/economic-insights.jpg'
    ],
    [
        'title' => 'Sports Roundup - Cricket & Beyond',
        'description' => 'Comprehensive sports coverage focusing on cricket, hockey, football, and other sports with expert analysis and player interviews.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_sports',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_sports" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d 20:00:00', strtotime('next Wednesday')),
        'end_time' => date('Y-m-d 21:00:00', strtotime('next Wednesday')),
        'status' => 'scheduled',
        'auto_start' => 1,
        'category' => 'Sports',
        'thumbnail' => 'uploads/live/sports-roundup.jpg'
    ],
    [
        'title' => 'Political Talk Show - National Issues',
        'description' => 'In-depth discussion on current political issues with prominent politicians, analysts, and policy experts.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_political',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_political" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d 19:00:00', strtotime('next Thursday')),
        'end_time' => date('Y-m-d 20:30:00', strtotime('next Thursday')),
        'status' => 'scheduled',
        'auto_start' => 1,
        'category' => 'Politics',
        'thumbnail' => 'uploads/live/political-talk.jpg'
    ],
    [
        'title' => 'Weekend Special - Cultural & Entertainment Show',
        'description' => 'Celebrating Pakistan\'s rich culture, music, arts, and entertainment industry with celebrity guests and cultural performances.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_cultural',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_cultural" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d 21:00:00', strtotime('next Friday')),
        'end_time' => date('Y-m-d 22:30:00', strtotime('next Friday')),
        'status' => 'scheduled',
        'auto_start' => 1,
        'category' => 'Entertainment',
        'thumbnail' => 'uploads/live/cultural-show.jpg'
    ],
    [
        'title' => 'Breaking News Live - Special Coverage',
        'description' => 'Special live coverage of major breaking news events as they happen, with on-ground reporting and expert analysis.',
        'stream_url' => 'https://www.youtube.com/watch?v=dummy_breaking',
        'embed_code' => '<iframe width="100%" height="450" src="https://www.youtube.com/embed/dummy_breaking" frameborder="0" allowfullscreen></iframe>',
        'schedule_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        'end_time' => date('Y-m-d H:i:s', strtotime('+4 hours')),
        'status' => 'scheduled',
        'auto_start' => 0,
        'category' => 'Breaking News',
        'thumbnail' => 'uploads/live/breaking-news.jpg'
    ]
];

// Clear existing scheduled streams
$clear_query = "DELETE FROM live_stream WHERE status = 'scheduled'";
mysqli_query($conn, $clear_query);

// Insert live streams
$success_count = 0;
foreach ($live_streams as $stream) {
    $title = mysqli_real_escape_string($conn, $stream['title']);
    $description = mysqli_real_escape_string($conn, $stream['description']);
    $stream_url = mysqli_real_escape_string($conn, $stream['stream_url']);
    $embed_code = mysqli_real_escape_string($conn, $stream['embed_code']);
    $schedule_time = $stream['schedule_time'];
    $end_time = $stream['end_time'];
    $status = $stream['status'];
    $auto_start = $stream['auto_start'];
    $category = mysqli_real_escape_string($conn, $stream['category']);
    $thumbnail = $stream['thumbnail'];
    
    $insert_query = "INSERT INTO live_stream (title, description, stream_url, embed_code, schedule_time, end_time, status, auto_start, category, thumbnail) 
                     VALUES ('$title', '$description', '$stream_url', '$embed_code', '$schedule_time', '$end_time', '$status', $auto_start, '$category', '$thumbnail')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success_count++;
        echo "<p class='text-success'>✓ Scheduled: " . htmlspecialchars($stream['title']) . "</p>";
    } else {
        echo "<p class='text-danger'>✗ Error scheduling stream: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Live Streaming Schedule Setup Complete!</h3>";
echo "<p class='text-success'><strong>Streams Scheduled:</strong> $success_count live streams</p>";

echo "<h3>Weekly Schedule:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Day</th><th>Time</th><th>Show</th><th>Category</th><th>Duration</th></tr>";

foreach ($live_streams as $stream) {
    $day = date('l', strtotime($stream['schedule_time']));
    $time = date('h:i A', strtotime($stream['schedule_time']));
    $duration = round((strtotime($stream['end_time']) - strtotime($stream['schedule_time'])) / 3600, 1) . ' hours';
    
    echo "<tr>";
    echo "<td>$day</td>";
    echo "<td>$time</td>";
    echo "<td>" . htmlspecialchars($stream['title']) . "</td>";
    echo "<td>" . htmlspecialchars($stream['category']) . "</td>";
    echo "<td>$duration</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='live.php'>View live streaming page</a></li>";
echo "<li><a href='admin/manage-live-streams.php'>Manage streams in admin panel</a></li>";
echo "<li><a href='setup_social_media.php'>Configure social media sharing</a></li>";
echo "<li>Replace dummy YouTube URLs with real stream URLs</li>";
echo "</ul>";

echo "<div class='alert alert-info'>";
echo "<h4>📺 Pro Tips for Live Streaming:</h4>";
echo "<ul>";
echo "<li>Use professional lighting and audio equipment</li>";
echo "<li>Test internet connection before going live</li>";
echo "<li>Promote streams 24 hours in advance</li>";
echo "<li>Engage with viewers through live chat</li>";
echo "<li>Record streams for on-demand viewing</li>";
echo "</ul>";
echo "</div>";
?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
table { margin: 20px 0; }
th, td { padding: 10px; text-align: left; }
.alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; }
</style>
