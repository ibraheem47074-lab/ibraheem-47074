<?php
require_once 'config/database.php';
$page_title = 'YouTube Stream Test';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_url'])) {
    $test_url = clean_input($_POST['test_url']);
    $channel_name = clean_input($_POST['channel_name']);
    
    // Test URL parsing
    $video_id = '';
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $test_url, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtu\.be\/([^?]+)/', $test_url, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $test_url, $matches)) {
        $video_id = $matches[1];
    }
    
    $video_id = explode('?', $video_id)[0];
    $video_id = explode('&', $video_id)[0];
    
    // Add to database if parsing successful
    if (!empty($video_id) && !empty($channel_name)) {
        $query = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, language, country, is_featured) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        // Create variables for bind_param (required by reference)
        $category = 'news';
        $stream_type = 'youtube';
        $description = 'Test channel from YouTube test page';
        $status = 'live';
        $language = 'en';
        $country = 'PK';
        $is_featured = 0;
        
        mysqli_stmt_bind_param($stmt, 'sssssssii', 
            $channel_name, $category, $test_url, $stream_type, $description, $status, $language, $country, $is_featured);
        mysqli_stmt_execute($stmt);
        $success = "Channel added successfully!";
    }
}

// Get existing channels
$channels_query = "SELECT * FROM channels WHERE stream_type = 'youtube' ORDER BY name ASC";
$channels_result = mysqli_query($conn, $channels_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>YouTube Stream Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>YouTube Stream Test & Debug</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Add YouTube Channel Test</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Channel Name</label>
                                <input type="text" name="channel_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="test_url" class="form-control" required
                                       placeholder="https://www.youtube.com/watch?v=...">
                                <div class="form-text">
                                    Supports: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger">Test & Add Channel</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Test YouTube URLs</h4>
                    </div>
                    <div class="card-body">
                        <h6>Example URLs to test:</h6>
                        <ul class="small">
                            <li>https://www.youtube.com/watch?v=jfKfPfyJRdk</li>
                            <li>https://youtu.be/jfKfPfyJRdk</li>
                            <li>https://www.youtube.com/embed/jfKfPfyJRdk</li>
                        </ul>
                        
                        <h6>Live News Channels (examples):</h6>
                        <ul class="small">
                            <li>Al Jazeera: https://www.youtube.com/watch?v=wGBzr_8qPm4</li>
                            <li>BBC News: https://www.youtube.com/watch?v=wGBzr_8qPm4</li>
                            <li>CNN: https://www.youtube.com/watch?v=wGBzr_8qPm4</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h4>Existing YouTube Channels</h4>
                </div>
                <div class="card-body">
                    <?php if ($channels_result && mysqli_num_rows($channels_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Original URL</th>
                                        <th>Status</th>
                                        <th>Test Embed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($channel = mysqli_fetch_assoc($channels_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($channel['name']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($channel['stream_url']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : 'secondary'; ?>">
                                                    <?php echo strtoupper($channel['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                // Test the same parsing logic
                                                $video_id = '';
                                                $stream_url = $channel['stream_url'];
                                                if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $stream_url, $matches)) {
                                                    $video_id = $matches[1];
                                                } elseif (preg_match('/youtu\.be\/([^?]+)/', $stream_url, $matches)) {
                                                    $video_id = $matches[1];
                                                } elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $stream_url, $matches)) {
                                                    $video_id = $matches[1];
                                                }
                                                $video_id = explode('?', $video_id)[0];
                                                $video_id = explode('&', $video_id)[0];
                                                
                                                if (!empty($video_id)) {
                                                    echo '<iframe width="200" height="120" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                                                } else {
                                                    echo '<span class="text-danger">Failed to parse</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No YouTube channels found. Add one using the form above!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h4>Quick Links</h4>
                </div>
                <div class="card-body">
                    <a href="live.php" class="btn btn-danger me-2">View Live TV Page</a>
                    <a href="admin/manage-channels.php" class="btn btn-primary me-2">Manage Channels (Admin)</a>
                    <a href="debug_live_streams.php" class="btn btn-info">Debug All Streams</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
