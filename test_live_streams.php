<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Live Streams - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .stream-test { margin-bottom: 30px; }
        .video-container { background: #000; border-radius: 10px; overflow: hidden; }
        .working { border-color: #28a745; }
        .not-working { border-color: #dc3545; }
    </style>
</head>
<body class='bg-light'>
    <div class='container py-4'>
        <h1 class='text-center mb-4'>📺 Live Stream Testing</h1>
        <p class='text-center mb-4'>Test all channels to see which ones are working</p>";

// Get all channels
$channels_query = "SELECT * FROM channels ORDER BY sort_order ASC";
$channels_result = mysqli_query($conn, $channels_query);

echo "<div class='row'>";

while ($channel = mysqli_fetch_assoc($channels_result)) {
    $video_id = '';
    if (strpos($channel['stream_url'], 'youtube.com/watch?v=') !== false) {
        $video_id = substr($channel['stream_url'], strpos($channel['stream_url'], 'v=') + 2);
    } elseif (strpos($channel['stream_url'], 'youtu.be/') !== false) {
        $video_id = substr($channel['stream_url'], strpos($channel['stream_url'], 'youtu.be/') + 9);
    } elseif (strpos($channel['stream_url'], 'youtube.com/embed/') !== false) {
        $video_id = substr($channel['stream_url'], strpos($channel['stream_url'], 'embed/') + 6);
    }
    $video_id = explode('?', $video_id)[0];
    
    echo "<div class='col-lg-6 mb-4'>
            <div class='card stream-test'>
                <div class='card-header bg-primary text-white'>
                    <h5 class='mb-0'>" . htmlspecialchars($channel['name']) . "</h5>
                    <small>Category: " . ucfirst($channel['category']) . " | Viewers: " . number_format($channel['viewer_count']) . "</small>
                </div>
                <div class='card-body p-0'>
                    <div class='video-container'>
                        <iframe src='https://www.youtube.com/embed/" . $video_id . "?autoplay=0&mute=1' 
                                width='100%' height='315' frameborder='0' allowfullscreen></iframe>
                    </div>
                </div>
                <div class='card-footer'>
                    <small class='text-muted'>
                        <strong>Stream Type:</strong> " . $channel['stream_type'] . "<br>
                        <strong>Video ID:</strong> " . $video_id . "<br>
                        <strong>Full URL:</strong> " . htmlspecialchars($channel['stream_url']) . "
                    </small>
                </div>
            </div>
          </div>";
}

echo "</div>";

echo "<div class='alert alert-info mt-4'>
        <h4>📹 How to Add Real Live Streams:</h4>
        <ol>
            <li><strong>Find Live Streams:</strong> Go to YouTube and search for 'live news', 'live sports', etc.</li>
            <li><strong>Get Video ID:</strong> Copy the video ID from the URL (the part after v=)</li>
            <li><strong>Update Channel:</strong> Go to <a href='admin/manage-channels.php'>Admin Panel</a></li>
            <li><strong>Replace URL:</strong> Update the stream URL with the new video ID</li>
        </ol>
        
        <h5>Examples of Live Stream Sources:</h5>
        <ul>
            <li><strong>News:</strong> Geo News, ARY News, Dunya News, BBC World News</li>
            <li><strong>Sports:</strong> PTV Sports, Ten Sports, ESPN</li>
            <li><strong>International:</strong> CNN, BBC, Al Jazeera</li>
        </ul>
        
        <p><strong>For FYP Demo:</strong> The current demo channels with working videos are perfect for showing the streaming functionality!</p>
      </div>";

echo "<div class='text-center mt-4'>
        <a href='live_demo.php' class='btn btn-primary me-2'>📺 Live Demo</a>
        <a href='live.php' class='btn btn-success me-2'>🎬 Main Live Page</a>
        <a href='admin/manage-channels.php' class='btn btn-warning'>⚙️ Manage Channels</a>
      </div>";

echo "</body>
</html>";
?>
