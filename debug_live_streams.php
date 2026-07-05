<?php
require_once 'config/database.php';

echo "<h2>Live Stream Debug Information</h2>";

// Get all channels
$channels_query = "SELECT * FROM channels ORDER BY name ASC";
$channels_result = mysqli_query($conn, $channels_query);

if ($channels_result && mysqli_num_rows($channels_result) > 0) {
    echo "<h3>Found " . mysqli_num_rows($channels_result) . " channels:</h3>";
    
    while ($channel = mysqli_fetch_assoc($channels_result)) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<h4>" . htmlspecialchars($channel['name']) . "</h4>";
        echo "<p><strong>Stream Type:</strong> " . $channel['stream_type'] . "</p>";
        echo "<p><strong>Status:</strong> " . $channel['status'] . "</p>";
        echo "<p><strong>Stream URL:</strong> " . htmlspecialchars($channel['stream_url']) . "</p>";
        
        // Test YouTube URL parsing
        if ($channel['stream_type'] === 'youtube') {
            $video_id = '';
            $stream_url = $channel['stream_url'];
            
            echo "<p><strong>YouTube URL Parsing Test:</strong></p>";
            
            if (strpos($stream_url, 'youtube.com/watch?v=') !== false) {
                $video_id = substr($stream_url, strpos($stream_url, 'v=') + 2);
                echo "Found watch?v= format, Video ID: " . $video_id . "<br>";
            } elseif (strpos($stream_url, 'youtu.be/') !== false) {
                $video_id = substr($stream_url, strpos($stream_url, 'youtu.be/') + 9);
                echo "Found youtu.be format, Video ID: " . $video_id . "<br>";
            } elseif (strpos($stream_url, 'youtube.com/embed/') !== false) {
                $video_id = substr($stream_url, strpos($stream_url, 'embed/') + 6);
                echo "Found embed format, Video ID: " . $video_id . "<br>";
            } else {
                echo "No matching YouTube URL format found!<br>";
            }
            
            $video_id = explode('?', $video_id)[0];
            echo "Final Video ID: " . $video_id . "<br>";
            
            if (!empty($video_id)) {
                $embed_url = "https://www.youtube.com/embed/" . $video_id . "?autoplay=1&mute=1";
                echo "<p><strong>Embed URL:</strong> " . htmlspecialchars($embed_url) . "</p>";
                echo "<iframe width='300' height='200' src='" . $embed_url . "' frameborder='0' allowfullscreen></iframe>";
            } else {
                echo "<p style='color: red;'>Failed to extract video ID!</p>";
            }
        }
        
        echo "</div>";
    }
} else {
    echo "<p>No channels found in database.</p>";
}

// Test adding a sample YouTube channel if none exist
if (mysqli_num_rows($channels_result) == 0) {
    echo "<h3>Adding Sample YouTube Channel for Testing...</h3>";
    
    $sample_urls = [
        'https://www.youtube.com/watch?v=jfKfPfyJRdk', // Live news stream example
        'https://www.youtube.com/watch?v=21X3lQD4FhQ', // Another example
        'https://youtu.be/jfKfPfyJRdk' // Short format
    ];
    
    foreach ($sample_urls as $index => $url) {
        $name = "Test Channel " . ($index + 1);
        $query = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, language, country, is_featured) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssii', 
            $name, 'news', $url, 'youtube', 'Test YouTube channel for debugging', 'live', 'en', 'PK', 0);
        mysqli_stmt_execute($stmt);
        echo "<p>Added: " . $name . " with URL: " . htmlspecialchars($url) . "</p>";
    }
}
?>
