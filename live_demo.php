<?php
require_once 'config/database.php';

// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get channels directly from database
$channels_query = "SELECT * FROM channels ORDER BY sort_order ASC";
$channels_result = mysqli_query($conn, $channels_query);

$channels = [];
while ($channel = mysqli_fetch_assoc($channels_result)) {
    $channels[] = $channel;
}

// Set current channel to first live or featured channel
$current_channel = null;
foreach ($channels as $channel) {
    if ($channel['status'] === 'live' || $channel['is_featured'] == 1) {
        $current_channel = $channel;
        break;
    }
}

if (!$current_channel && !empty($channels)) {
    $current_channel = $channels[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live TV Demo - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .live-header {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        .on-air-indicator {
            background: #ff0000;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .channel-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .channel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .video-container {
            background: #000;
            border-radius: 0 0 10px 10px;
            overflow: hidden;
        }
        .live-badge {
            background: #ff0000;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-dark text-white">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="display-4 text-danger">
                <i class="fas fa-broadcast-tower me-3"></i>PK Live TV Demo
            </h1>
            <p class="lead">Watch different channels live with direct streaming links</p>
        </div>

        <?php if ($current_channel): ?>
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="live-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="on-air-indicator me-3">ON AIR</span>
                                <h4 class="mb-0"><?php echo htmlspecialchars($current_channel['name']); ?></h4>
                                <?php if ($current_channel['status'] === 'live'): ?>
                                    <span class="badge bg-warning ms-2">🔴 LIVE</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-eye me-2"></i>
                                <span><?php echo number_format($current_channel['viewer_count']); ?></span>
                            </div>
                        </div>
                        
                        <div class="video-container">
                            <?php
                            if ($current_channel['stream_type'] === 'youtube') {
                                $video_id = '';
                                if (strpos($current_channel['stream_url'], 'youtube.com/watch?v=') !== false) {
                                    $video_id = substr($current_channel['stream_url'], strpos($current_channel['stream_url'], 'v=') + 2);
                                } elseif (strpos($current_channel['stream_url'], 'youtu.be/') !== false) {
                                    $video_id = substr($current_channel['stream_url'], strpos($current_channel['stream_url'], 'youtu.be/') + 9);
                                } elseif (strpos($current_channel['stream_url'], 'youtube.com/embed/') !== false) {
                                    $video_id = substr($current_channel['stream_url'], strpos($current_channel['stream_url'], 'embed/') + 6);
                                }
                                $video_id = explode('?', $video_id)[0];
                                
                                echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '?autoplay=1&mute=1" 
                                        width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                            } elseif ($current_channel['stream_type'] === 'iframe') {
                                echo '<iframe src="' . htmlspecialchars($current_channel['stream_url']) . '" 
                                        width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                echo '<div class="text-center py-5">
                                        <i class="fas fa-tv fa-4x text-muted mb-3"></i>
                                        <h4>Stream Loading...</h4>
                                        <p class="text-muted">Channel: ' . htmlspecialchars($current_channel['name']) . '</p>
                                        <p class="text-muted">Stream URL: ' . htmlspecialchars($current_channel['stream_url']) . '</p>
                                      </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Available Channels</h5>
                        </div>
                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($channels as $channel): ?>
                                <div class="p-3 border-bottom channel-item" 
                                     style="cursor: pointer;"
                                     onclick="switchChannel(<?php echo $channel['id']; ?>)">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($channel['thumbnail']): ?>
                                                <img src="<?php echo htmlspecialchars($channel['thumbnail']); ?>" 
                                                     alt="<?php echo htmlspecialchars($channel['name']); ?>" 
                                                     class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 40px;">
                                                    <i class="fas fa-tv text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($channel['name']); ?></h6>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : 'secondary'; ?> me-2">
                                                    <?php echo strtoupper($channel['status'] ?: 'OFFLINE'); ?>
                                                </span>
                                                <?php if ($channel['status'] === 'live'): ?>
                                                    <span class="live-badge">LIVE</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-eye me-1"></i><?php echo number_format($channel['viewer_count']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card shadow mt-3">
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle me-2"></i>Channel Information</h6>
                            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($current_channel['name']); ?></p>
                            <p class="mb-2"><strong>Category:</strong> <?php echo ucfirst($current_channel['category']); ?></p>
                            <p class="mb-2"><strong>Status:</strong> <?php echo ucfirst($current_channel['status'] ?: 'Offline'); ?></p>
                            <p class="mb-2"><strong>Language:</strong> <?php echo strtoupper($current_channel['language']); ?></p>
                            <p class="mb-2"><strong>Country:</strong> <?php echo htmlspecialchars($current_channel['country']); ?></p>
                            <p class="mb-0"><strong>Viewers:</strong> <?php echo number_format($current_channel['viewer_count']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="bg-secondary rounded p-5">
                    <i class="fas fa-broadcast-tower fa-4x text-muted mb-3"></i>
                    <h3>No Channels Available</h3>
                    <p class="text-muted">Please set up channels first</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function switchChannel(channelId) {
            // In a real implementation, this would switch the main video
            // For demo purposes, we'll just show an alert
            alert('Switching to channel ' + channelId + '. In full implementation, this would change the main video player.');
        }
    </script>
</body>
</html>
