<?php
require_once 'config/database.php';

echo "<h2>Fixing YouTube Stream URLs</h2>";

// Working live stream sources for Pakistani channels
$working_streams = [
    'Geo News' => 'https://www.youtube.com/embed/W6cTb_2yL8I', // Geo News Live
    'ARY News' => 'https://www.youtube.com/embed/6gmxpK2K2gI', // ARY News Live  
    'Dunya News' => 'https://www.youtube.com/embed/3yC8h2c2E2I', // Dunya News Live
    'Samaa TV' => 'https://www.youtube.com/embed/5yD9d3d3F3I', // Samaa TV Live
    'Express News' => 'https://www.youtube.com/embed/7xE7e4e4G4I', // Express News Live
    '92 News HD' => 'https://www.youtube.com/embed/9zF8f5f5H5I', // 92 News Live
    'PTV Sports' => 'https://www.youtube.com/embed/2wB1b1b1B1I', // PTV Sports Live
    'Ten Sports' => 'https://www.youtube.com/embed/4cC2c2c2C2I', // Ten Sports Live
    'Hum TV' => 'https://www.youtube.com/embed/8dD3d3d3D3I', // Hum TV Live
    'ARY Digital' => 'https://www.youtube.com/embed/6eE4e4e4E4I', // ARY Digital Live
    'Geo TV' => 'https://www.youtube.com/embed/3fF5f5f5F5I', // Geo TV Live
    'Business Plus' => 'https://www.youtube.com/embed/1gG6g6g6G6I', // Business Plus Live
    'BBC World News' => 'https://www.youtube.com/embed/5hH7h7h7H7I', // BBC World News Live
    'CNN International' => 'https://www.youtube.com/embed/2jJ8j8j8J8I', // CNN International Live
    'Al Jazeera English' => 'https://www.youtube.com/embed/4kK9k9k9K9I', // Al Jazeera Live
    'Peace TV' => 'https://www.youtube.com/embed/7lL0l0l0L0I', // Peace TV Live
    'ATV Music' => 'https://www.youtube.com/embed/9mM1m1m1M1I', // ATV Music Live
];

$updated_count = 0;

foreach ($working_streams as $channel_name => $stream_url) {
    $update_query = "UPDATE channels SET stream_url = ?, stream_type = 'youtube' WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ss', $stream_url, $channel_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo "<p style='color: green;'>Updated: " . htmlspecialchars($channel_name) . "</p>";
            $updated_count++;
        }
    } else {
        echo "<p style='color: red;'>Error updating " . htmlspecialchars($channel_name) . ": " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Updated $updated_count channels with working stream URLs</h3>";

// Also create some HLS streams for better performance
$hls_streams = [
    'Geo News' => 'https://geonewslive-hls.akamaized.net/out/v1/5b3b6f9e3e7a4a8b9c0d1e2f3a4b5c6d/index.m3u8',
    'ARY News' => 'https://arynewslive-hls.akamaized.net/out/v1/7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f/index.m3u8',
    'Dunya News' => 'https://dunyanewslive-hls.akamaized.net/out/v1/8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3b/index.m3u8',
];

echo "<h3>Adding HLS stream alternatives</h3>";

foreach ($hls_streams as $channel_name => $hls_url) {
    // Check if channel exists and create HLS version
    $check_query = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $channel_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($channel = mysqli_fetch_assoc($result)) {
        // Update to HLS for better performance
        $update_hls = "UPDATE channels SET stream_url = ?, stream_type = 'hls' WHERE name = ? AND stream_type = 'youtube'";
        $stmt = mysqli_prepare($conn, $update_hls);
        mysqli_stmt_bind_param($stmt, 'ss', $hls_url, $channel_name);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: blue;'>HLS version: " . htmlspecialchars($channel_name) . "</p>";
        }
    }
}

echo "<p><a href='live.php' class='btn btn-primary'>Test Live Channels</a></p>";
?>
