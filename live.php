<?php
require_once 'config/database.php';
require_once 'includes/ad-functions.php';
$page_title = 'Live TV';

// Check if all required tables exist
$tables_to_check = ['channels', 'live_chat', 'channel_schedule'];
$missing_tables = [];

foreach ($tables_to_check as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    // Tables don't exist - show setup message
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Setup Required - PK Live News</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'includes/header.php'; ?>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h4>Database Setup Required</h4>
                        </div>
                        <div class="card-body">
                            <p>The following database tables need to be created:</p>
                            <ul>
                                <?php foreach ($missing_tables as $table): ?>
                                    <li><?php echo htmlspecialchars($table); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p>Please click the button below to set up the database:</p>
                            <a href="test_db.php" class="btn btn-danger">Setup Database Tables</a>
                            <hr>
                            <small class="text-muted">This will create the necessary tables for live streaming functionality.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Get admin live stream (highest priority)
$admin_live_stream = null;
$live_stream_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'live_stream'");
if (mysqli_num_rows($live_stream_table_exists) > 0) {
    $admin_live_stream = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1"
    ));
}

// Define priority channels to show first
$priority_channels = ['BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters'];

// Create unified channel list including admin live stream
$all_channels = [];
$current_channel = null; // Initialize current_channel variable

// Add admin live stream as highest priority if online
if ($admin_live_stream) {
    $admin_channel = [
        'id' => -1, // Special ID for admin stream
        'name' => $admin_live_stream['title'] . ' (Admin Live)',
        'category' => 'admin',
        'stream_url' => $admin_live_stream['stream_url'],
        'embed_code' => $admin_live_stream['embed_code'] ?? '',
        'stream_type' => 'admin',
        'description' => $admin_live_stream['description'],
        'status' => 'live',
        'language' => 'en',
        'country' => 'PK',
        'is_featured' => 1,
        'viewer_count' => 0,
        'thumbnail' => '',
        'admin_stream' => true,
        'started_at' => $admin_live_stream['started_at']
    ];
    $all_channels[] = $admin_channel;
    $current_channel = $admin_channel; // Set admin stream as current
}

// Get featured/live channels from channels table
$featured_channels_query = "SELECT * FROM channels WHERE (status = 'live' OR is_featured = 1) 
                              ORDER BY 
                                CASE 
                                    WHEN name IN ('BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters') THEN 0
                                    ELSE 1
                                END,
                                is_featured DESC, 
                                sort_order ASC, 
                                name ASC";
$featured_result = mysqli_query($conn, $featured_channels_query);

// Add channels table results to unified list
while ($channel = mysqli_fetch_assoc($featured_result)) {
    $channel['admin_stream'] = false;
    $all_channels[] = $channel;
}

// If no admin stream, get current channel from channels table
if (!$current_channel) {
    $current_channel = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM channels WHERE status = 'live' 
         ORDER BY 
            CASE 
                WHEN name IN ('BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters') THEN 0
                ELSE 1
            END,
            is_featured DESC, 
            sort_order ASC 
         LIMIT 1"
    ));

    if (!$current_channel) {
        $current_channel = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT * FROM channels WHERE is_featured = 1 
             ORDER BY 
                CASE 
                    WHEN name IN ('BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters') THEN 0
                    ELSE 1
                END,
                sort_order ASC 
             LIMIT 1"
        ));
    }
}

// Get all channels grouped by category with priority ordering
$channels_query = "SELECT * FROM channels 
                   ORDER BY 
                        CASE 
                            WHEN name IN ('BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters') THEN 0
                            ELSE 1
                        END,
                        sort_order ASC, 
                        is_featured DESC, 
                        name ASC";
$channels_result = mysqli_query($conn, $channels_query);

// Get upcoming scheduled streams
$upcoming_query = "SELECT * FROM channels WHERE status = 'scheduled' AND schedule_time > NOW() ORDER BY schedule_time ASC LIMIT 5";
$upcoming_result = mysqli_query($conn, $upcoming_query);

// Get live chat messages for current channel
$chat_messages = [];
if ($current_channel) {
    $chat_query = "SELECT * FROM live_chat WHERE channel_id = ? AND is_deleted = 0 ORDER BY timestamp DESC LIMIT 50";
    $stmt = mysqli_prepare($conn, $chat_query);
    mysqli_stmt_bind_param($stmt, 'i', $current_channel['id']);
    mysqli_stmt_execute($stmt);
    $chat_result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($chat_result)) {
        $chat_messages[] = $row;
    }
}

// Simulate live viewer count
$viewer_count = 1234;
if ($current_channel) {
    $viewer_count = $current_channel['viewer_count'] > 0 ? $current_channel['viewer_count'] : rand(1000, 5000);
}

// Group channels by category
$channels_by_category = [];
while ($channel = mysqli_fetch_assoc($channels_result)) {
    $channels_by_category[$channel['category']][] = $channel;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Video.js CSS -->
<link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
<!-- HLS.js for better HLS support -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<style>
/* Channel Logo Styles */
.channel-thumbnail img {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.channel-thumbnail img:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

/* CSS-based logo hover effects */
.css-logo:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    filter: brightness(1.1);
}

.channel-item {
    transition: all 0.3s ease;
    cursor: pointer;
}

.channel-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.channel-item.active {
    background-color: #fff5f5;
    border-left: 4px solid #dc3545;
}

.channel-card {
    transition: all 0.3s ease;
}

.channel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.channel-card .watch-btn {
    transition: all 0.3s ease;
}

.channel-card:hover .watch-btn {
    background-color: #dc3545;
    color: white;
    transform: scale(1.05);
}

/* Live indicator animation */
.live-indicator {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 2s infinite;
    margin-right: 5px;
}

@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
    100% { opacity: 1; transform: scale(1); }
}

/* Channel status badges */
.badge.bg-danger {
    background: linear-gradient(45deg, #dc3545, #ff6b6b) !important;
}

.badge.bg-warning {
    background: linear-gradient(45deg, #ffc107, #ffdb4d) !important;
}

.badge.bg-success {
    background: linear-gradient(45deg, #28a745, #5cb85c) !important;
}

/* Video player improvements */
.video-wrapper iframe,
.video-wrapper video {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

/* Channel list scrollbar */
.channel-list::-webkit-scrollbar {
    width: 6px;
}

.channel-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.channel-list::-webkit-scrollbar-thumb {
    background: #dc3545;
    border-radius: 10px;
}

.channel-list::-webkit-scrollbar-thumb:hover {
    background: #c82333;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .channel-thumbnail img {
        width: 50px !important;
        height: 35px !important;
    }
    
    .channel-card .channel-thumbnail img {
        width: 60px !important;
        height: 45px !important;
    }
    
    .css-logo {
        font-size: 8px !important;
    }
}
</style>

<!-- Live TV Section -->
<section class="live-tv-section py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 text-danger mb-3">
                <i class="fas fa-broadcast-tower me-3"></i>PK Live TV
            </h1>
            <p class="lead">Watch breaking news and live coverage 24/7</p>
        </div>

        <!-- Live Header Ads -->
        <?php echo display_live_ads(); ?>

        <!-- Category Tabs with Live Broadcasting -->
        <div class="category-tabs mb-4">
            <ul class="nav nav-pills justify-content-center" id="categoryTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="featured-tab" data-bs-toggle="pill" data-bs-target="#featured" type="button" role="tab">
                        <i class="fas fa-star me-2"></i>Featured
                    </button>
                </li>
                
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="news-tab" data-bs-toggle="pill" data-bs-target="#news" type="button" role="tab">
                        <i class="fas fa-newspaper me-2"></i>News
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sports-tab" data-bs-toggle="pill" data-bs-target="#sports" type="button" role="tab">
                        <i class="fas fa-football-ball me-2"></i>Sports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="entertainment-tab" data-bs-toggle="pill" data-bs-target="#entertainment" type="button" role="tab">
                        <i class="fas fa-film me-2"></i>Entertainment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="business-tab" data-bs-toggle="pill" data-bs-target="#business" type="button" role="tab">
                        <i class="fas fa-chart-line me-2"></i>Business
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="technology-tab" data-bs-toggle="pill" data-bs-target="#technology" type="button" role="tab">
                        <i class="fas fa-microchip me-2"></i>Technology
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="international-tab" data-bs-toggle="pill" data-bs-target="#international" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i>International
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="categoryTabContent">
            <!-- Live Broadcasting Tab -->
            <div class="tab-pane fade" id="broadcast" role="tabpanel">
                <div class="broadcast-section bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 30px; color: white;">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="text-center mb-4">
                                <h2><i class="fas fa-satellite-dish me-3"></i>Start Your Live Broadcast</h2>
                                <p>Stream from camera, screen, or upload video file - Real live broadcasting!</p>
                            </div>
                            
                            <div class="bg-black rounded p-4 mb-4" style="min-height: 400px;">
                                <video id="broadcastVideo" class="w-100" controls autoplay muted style="border-radius: 10px; max-height: 400px; background: #000;">
                                    <source src="" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="bg-secondary rounded p-3">
                                        <i class="fas fa-eye fa-2x mb-2"></i>
                                        <h5 id="liveViewerCount">0</h5>
                                        <small>Viewers</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="bg-secondary rounded p-3">
                                        <i class="fas fa-clock fa-2x mb-2"></i>
                                        <h5 id="streamDuration">00:00</h5>
                                        <small>Duration</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="bg-secondary rounded p-3">
                                        <i class="fas fa-signal fa-2x mb-2"></i>
                                        <h5>HD</h5>
                                        <small>Quality</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="bg-secondary rounded p-3">
                                        <i class="fas fa-microphone fa-2x mb-2"></i>
                                        <h5>ON</h5>
                                        <small>Audio</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="control-panel p-4" style="background: rgba(0,0,0,0.2); border-radius: 15px; backdrop-filter: blur(10px);">
                                <h4 class="mb-4"><i class="fas fa-sliders-h me-2"></i>Broadcast Controls</h4>
                                
                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-camera me-2"></i>Camera Stream</label>
                                    <button onclick="startCameraBroadcast()" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-video me-2"></i>Start Camera
                                    </button>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-desktop me-2"></i>Screen Share</label>
                                    <button onclick="startScreenBroadcast()" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-desktop me-2"></i>Share Screen
                                    </button>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-file-video me-2"></i>Video File</label>
                                    <input type="file" id="videoFile" accept="video/*" class="form-control mb-2">
                                    <button onclick="startFileBroadcast()" class="btn btn-warning w-100">
                                        <i class="fas fa-play me-2"></i>Play File
                                    </button>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-link me-2"></i>External URL</label>
                                    <input type="text" id="externalUrl" placeholder="Enter stream URL..." class="form-control mb-2">
                                    <button onclick="loadExternalBroadcast()" class="btn btn-info w-100">
                                        <i class="fas fa-external-link-alt me-2"></i>Load URL
                                    </button>
                                </div>
                                
                                <div class="text-center">
                                    <button onclick="toggleBroadcastFullscreen()" class="btn btn-outline-light">
                                        <i class="fas fa-expand me-2"></i>Fullscreen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Featured Tab -->
            <div class="tab-pane fade show active" id="featured" role="tabpanel">
                <?php if ($current_channel): ?>
                    <!-- Current Live Stream -->
                    <div class="row mb-5">
                        <div class="col-lg-8">
                            <div class="live-video-container shadow-lg">
                                <div class="live-header bg-danger text-white p-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="on-air-indicator me-3">ON AIR</span>
                                        <h4 class="mb-0"><?php echo htmlspecialchars($current_channel['name']); ?></h4>
                                        <?php if ($current_channel['status'] === 'live'): ?>
                                            <span class="badge bg-warning ms-2">🔴 LIVE</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-eye me-2"></i>
                                        <span class="viewer-count" id="liveViewerCount"><?php echo number_format($viewer_count); ?></span>
                                    </div>
                                </div>
                                
                                <div class="video-wrapper bg-black" id="videoWrapper">
                                    <?php
                                    // Handle different streaming sources with fallback
                                    if (isset($current_channel['admin_stream']) && $current_channel['admin_stream']) {
                                     // Admin live stream - check embed_code first, then stream_url
                                        if (!empty($current_channel['embed_code'])) {
                                           // Decode HTML entities and render the embed code
                                            $embed_code = html_entity_decode($current_channel['embed_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            echo $embed_code;
                                        } elseif (!empty($current_channel['stream_url'])) {
                                            echo '<iframe src="' . htmlspecialchars($current_channel['stream_url']) . '" 
                                                    width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                        } else {
                                            echo '<div class="text-center text-white p-5" style="height: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center;">';
                                            echo '<i class="fas fa-broadcast-tower fa-4x mb-4 text-danger"></i>';
                                            echo '<h3 class="mb-3">' . htmlspecialchars($current_channel['name']) . '</h3>';
                                            echo '<p class="mb-4">Admin live stream is online but no URL or embed code configured</p>';
                                            echo '<div class="bg-dark p-3 rounded" style="max-width: 400px;">';
                                            echo '<p class="mb-2"><i class="fas fa-info-circle me-2"></i>Stream Status: ';
                                            echo '<span class="badge bg-success">ADMIN LIVE</span></p>';
                                            echo '<p class="mb-2"><i class="fas fa-users me-2"></i>Viewers: ' . number_format($viewer_count) . '</p>';
                                            echo '<p class="mb-0"><i class="fas fa-cog me-2"></i>Admin Stream</p>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    } elseif ($current_channel['stream_type'] === 'youtube') {
                                        $video_id = '';
                                        $stream_url = $current_channel['stream_url'];
                                        
                                        // Parse YouTube URL more robustly
                                        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $stream_url, $matches)) {
                                            $video_id = $matches[1];
                                        } elseif (preg_match('/youtu\.be\/([^?]+)/', $stream_url, $matches)) {
                                            $video_id = $matches[1];
                                        } elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $stream_url, $matches)) {
                                            $video_id = $matches[1];
                                        } elseif (preg_match('/youtube\.com\/v\/([^?]+)/', $stream_url, $matches)) {
                                            $video_id = $matches[1];
                                        }
                                        
                                        // Clean up video ID (remove any remaining parameters)
                                        $video_id = explode('?', $video_id)[0];
                                        $video_id = explode('&', $video_id)[0];
                                        
                                        if (!empty($video_id)) {
                                            echo '<iframe id="youtubePlayer" class="embed-responsive-item" src="https://www.youtube.com/embed/' . $video_id . '?autoplay=1&mute=1" 
                                                    width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                        } else {
                                            // Fallback content with debugging
                                            echo '<div class="text-center text-white p-5" style="height: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center;">';
                                            echo '<i class="fas fa-broadcast-tower fa-4x mb-4 text-warning"></i>';
                                            echo '<h3 class="mb-3">' . htmlspecialchars($current_channel['name']) . '</h3>';
                                            echo '<p class="mb-2">Could not extract YouTube video ID from URL</p>';
                                            echo '<div class="bg-dark p-3 rounded" style="max-width: 500px; font-size: 12px;">';
                                            echo '<p class="mb-2"><strong>Original URL:</strong> ' . htmlspecialchars($stream_url) . '</p>';
                                            echo '<p class="mb-2"><strong>Stream Type:</strong> ' . $current_channel['stream_type'] . '</p>';
                                            echo '<p class="mb-2"><strong>Channel Status:</strong> ';
                                            echo '<span class="badge bg-' . ($current_channel['status'] === 'live' ? 'success' : 'secondary') . '">' . strtoupper($current_channel['status']) . '</span></p>';
                                            echo '<p class="mb-2"><i class="fas fa-users me-2"></i>Viewers: ' . number_format($viewer_count) . '</p>';
                                            echo '<p class="mb-0"><i class="fas fa-globe me-2"></i>Category: ' . ucfirst($current_channel['category']) . '</p>';
                                            echo '</div>';
                                            echo '<button class="btn btn-danger mt-3" onclick="location.reload()">';
                                            echo '<i class="fas fa-sync me-2"></i>Refresh Stream';
                                            echo '</button>';
                                            echo '</div>';
                                        }
                                    } elseif ($current_channel['stream_type'] === 'hls') {
                                        echo '<video id="hlsPlayer" class="video-js vjs-default-skin" controls preload="auto" width="100%" height="500" data-setup="{}">
                                                <source src="' . htmlspecialchars($current_channel['stream_url']) . '" type="application/x-mpegURL">
                                                Your browser does not support HTML5 video.
                                              </video>';
                                    } elseif ($current_channel['stream_type'] === 'iframe') {
                                        echo '<iframe src="' . htmlspecialchars($current_channel['stream_url']) . '" 
                                                width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                    } else {
                                        // Generic embed with fallback
                                        if (!empty($current_channel['stream_url'])) {
                                            echo '<iframe src="' . htmlspecialchars($current_channel['stream_url']) . '" 
                                                    width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                        } else {
                                            // Fallback content when no URL
                                            echo '<div class="text-center text-white p-5" style="height: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center;">';
                                            echo '<i class="fas fa-broadcast-tower fa-4x mb-4 text-warning"></i>';
                                            echo '<h3 class="mb-3">' . htmlspecialchars($current_channel['name']) . '</h3>';
                                            echo '<p class="mb-4">Stream URL not available</p>';
                                            echo '<button class="btn btn-danger" onclick="location.reload()">';
                                            echo '<i class="fas fa-sync me-2"></i>Refresh Stream';
                                            echo '</button>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>

                                <!-- Live Chat Section -->
                                <div class="live-chat bg-light p-3">
                                    <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Live Chat</h5>
                                    <div class="chat-messages bg-white border rounded p-3 mb-3" id="chatMessages" style="height: 200px; overflow-y: auto;">
                                        <?php if (!empty($chat_messages)): ?>
                                            <?php foreach (array_reverse($chat_messages) as $msg): ?>
                                                <div class="chat-message mb-2">
                                                    <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong> 
                                                    <?php echo htmlspecialchars($msg['message']); ?>
                                                    <small class="text-muted d-block"><?php echo date('h:i A', strtotime($msg['timestamp'])); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-muted text-center">No messages yet. Start the conversation!</div>
                                        <?php endif; ?>
                                    </div>
                                    <form class="chat-form d-flex" id="chatForm">
                                        <input type="text" class="form-control me-2" id="chatInput" placeholder="Type your message..." 
                                               <?php echo $current_channel['status'] === 'live' ? '' : 'disabled'; ?>>
                                        <button type="submit" class="btn btn-danger" id="chatSubmit" 
                                                <?php echo $current_channel['status'] === 'live' ? '' : 'disabled'; ?>>
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Channel List -->
                            <div class="card shadow mb-4">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Live Channels</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="channel-list" style="max-height: 400px; overflow-y: auto;">
                                        <?php 
                                        // Use unified channel list including admin stream
                                        foreach ($all_channels as $channel): 
                                        ?>
                                            <div class="channel-item p-3 border-bottom clickable-channel <?php echo $current_channel['id'] == $channel['id'] ? 'active' : ''; ?>" 
                                                 data-channel-id="<?php echo $channel['id']; ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="channel-thumbnail me-3">
                                                        <?php 
                                                        // CSS-based logo system (no GD required)
                                                        $logo_class = strtolower(str_replace(' ', '-', $channel['name']));
                                                        $category_colors = [
                                                            'news' => '#dc3545',
                                                            'sports' => '#28a745', 
                                                            'entertainment' => '#ffc107',
                                                            'business' => '#17a2b8',
                                                            'technology' => '#6f42c1',
                                                            'international' => '#fd7e14',
                                                            'admin' => '#6f42c1'  // Purple for admin streams
                                                        ];
                                                        $bg_color = isset($category_colors[$channel['category']]) ? $category_colors[$channel['category']] : '#6c757d';
                                                        
                                                        // Special handling for admin streams
                                                        if (isset($channel['admin_stream']) && $channel['admin_stream']) {
                                                            $channel_short = 'ADM';
                                                            $bg_color = '#6f42c1'; // Purple for admin
                                                        } else {
                                                            $channel_short = strtoupper(substr($channel['name'], 0, 3));
                                                        }
                                                        ?>
                                                        <div class="css-logo" 
                                                             style="width: 60px; height: 40px; border-radius: 5px; 
                                                                    background: linear-gradient(45deg, <?php echo $bg_color; ?>, <?php echo $bg_color; ?>dd);
                                                                    display: flex; align-items: center; justify-content: center;
                                                                    color: white; font-weight: bold; font-size: 10px;
                                                                    border: 2px solid <?php echo $bg_color; ?>;
                                                                    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                                                                    cursor: pointer; transition: all 0.3s ease;"
                                                             title="<?php echo htmlspecialchars($channel['name']); ?>">
                                                            <?php echo $channel_short; ?>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($channel['name']); ?>
                                                            <?php 
                                                            // Show admin stream indicator
                                                            if (isset($channel['admin_stream']) && $channel['admin_stream']) {
                                                                echo '<i class="fas fa-crown text-warning ms-1" title="Admin Stream"></i>';
                                                            }
                                                            // Show priority indicator for major news channels
                                                            elseif (in_array($channel['name'], ['BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters'])) {
                                                                echo '<i class="fas fa-star text-warning ms-1" title="Priority Channel"></i>';
                                                            }
                                                            ?>
                                                        </h6>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : ($channel['status'] === 'scheduled' ? 'warning' : 'secondary'); ?> me-2">
                                                                <?php echo strtoupper($channel['status']); ?>
                                                            </span>
                                                            <?php if ($channel['status'] === 'live'): ?>
                                                                <span class="text-danger small"><i class="fas fa-circle me-1"></i>LIVE</span>
                                                            <?php endif; ?>
                                                            <?php 
                                                            // Show special badges
                                                            if (isset($channel['admin_stream']) && $channel['admin_stream']) {
                                                                echo '<span class="badge bg-warning small">ADMIN LIVE</span>';
                                                            }
                                                            // Show "TOP NEWS" badge for priority channels
                                                            elseif (in_array($channel['name'], ['BBC News', 'CNN', 'Fox News', 'Al Jazeera', 'MSNBC', 'NBC News', 'CBS News', 'ABC News', 'Reuters'])) {
                                                                echo '<span class="badge bg-primary small">TOP NEWS</span>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="channel-viewers">
                                                        <small class="text-muted">
                                                            <i class="fas fa-eye me-1"></i><?php echo number_format($channel['viewer_count']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Stream Info -->
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Channel Information</h5>
                                    <p class="card-text"><?php echo htmlspecialchars($current_channel['description']); ?></p>
                                    
                                    <div class="stream-stats">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-eye me-1"></i> Live Viewers</span>
                                            <strong><?php echo number_format($viewer_count); ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-tag me-1"></i> Category</span>
                                            <strong><?php echo ucfirst($current_channel['category']); ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-signal me-1"></i> Quality</span>
                                            <strong>HD 1080p</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fas fa-globe me-1"></i> Language</span>
                                            <strong><?php echo strtoupper($current_channel['language']); ?></strong>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-danger w-100" onclick="toggleFullscreen()">
                                            <i class="fas fa-expand me-2"></i>Fullscreen Mode
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule -->
                            <div class="card shadow">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-calendar me-2"></i>Today's Schedule</h5>
                                    <div class="schedule-list">
                                        <?php
                                        $today_schedule_query = "SELECT cs.*, c.name as channel_name FROM channel_schedule cs 
                                                             JOIN channels c ON cs.channel_id = c.id 
                                                             WHERE DATE(cs.start_time) = CURDATE() 
                                                             ORDER BY cs.start_time ASC LIMIT 5";
                                        $schedule_result = mysqli_query($conn, $today_schedule_query);
                                        
                                        if (mysqli_num_rows($schedule_result) > 0):
                                            while ($schedule = mysqli_fetch_assoc($schedule_result)):
                                                $now = new DateTime();
                                                $start_time = new DateTime($schedule['start_time']);
                                                $end_time = new DateTime($schedule['end_time']);
                                                
                                                $badge_class = 'secondary';
                                                $badge_text = 'LATER';
                                                
                                                if ($now >= $start_time && $now <= $end_time) {
                                                    $badge_class = 'success';
                                                    $badge_text = 'NOW';
                                                } elseif ($now < $start_time) {
                                                    $badge_class = 'warning';
                                                    $badge_text = 'UPCOMING';
                                                }
                                        ?>
                                            <div class="schedule-item mb-3 pb-3 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                                    <small><?php echo date('h:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('h:i A', strtotime($schedule['end_time'])); ?></small>
                                                </div>
                                                <h6 class="mt-2"><?php echo htmlspecialchars($schedule['program_title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($schedule['channel_name']); ?></small>
                                            </div>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <div class="text-muted text-center">
                                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                                <p>No scheduled programs for today</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- No Live Stream -->
                    <div class="text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-broadcast-tower fa-4x text-muted mb-3"></i>
                            <h3>No Channels Available</h3>
                            <p class="text-muted">Please check back later for live channels</p>
                            <button class="btn btn-danger" onclick="location.reload()">
                                <i class="fas fa-sync me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Category Tabs Content -->
            <?php foreach (['news', 'sports', 'entertainment', 'business', 'technology', 'international'] as $category): ?>
                <div class="tab-pane fade" id="<?php echo $category; ?>" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="category-channels">
                                <h4 class="mb-4"><i class="fas fa-<?php echo $category === 'news' ? 'newspaper' : ($category === 'sports' ? 'football-ball' : ($category === 'entertainment' ? 'film' : ($category === 'business' ? 'chart-line' : ($category === 'technology' ? 'microchip' : 'globe')))); ?> me-2"></i><?php echo ucfirst($category); ?> Channels</h4>
                                <div class="row">
                                    <?php if (isset($channels_by_category[$category]) && !empty($channels_by_category[$category])): ?>
                                        <?php foreach ($channels_by_category[$category] as $channel): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card shadow h-100 channel-card clickable-channel" data-channel-id="<?php echo $channel['id']; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center mb-3">
                                                            <div class="channel-thumbnail me-3">
                                                                <?php 
                                                                // CSS-based logo system (no GD required)
                                                                $category_colors = [
                                                                    'news' => '#dc3545',
                                                                    'sports' => '#28a745', 
                                                                    'entertainment' => '#ffc107',
                                                                    'business' => '#17a2b8',
                                                                    'technology' => '#6f42c1',
                                                                    'international' => '#fd7e14'
                                                                ];
                                                                $bg_color = isset($category_colors[$channel['category']]) ? $category_colors[$channel['category']] : '#6c757d';
                                                                $channel_short = strtoupper(substr($channel['name'], 0, 3));
                                                                ?>
                                                                <div class="css-logo" 
                                                                     style="width: 80px; height: 60px; border-radius: 5px; 
                                                                            background: linear-gradient(45deg, <?php echo $bg_color; ?>, <?php echo $bg_color; ?>dd);
                                                                            display: flex; align-items: center; justify-content: center;
                                                                            color: white; font-weight: bold; font-size: 14px;
                                                                            border: 2px solid <?php echo $bg_color; ?>;
                                                                            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                                                                            cursor: pointer; transition: all 0.3s ease;"
                                                                     title="<?php echo htmlspecialchars($channel['name']); ?>">
                                                                    <?php echo $channel_short; ?>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($channel['name']); ?></h5>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : ($channel['status'] === 'scheduled' ? 'warning' : 'secondary'); ?> me-2">
                                                                        <?php echo strtoupper($channel['status']); ?>
                                                                    </span>
                                                                    <?php if ($channel['status'] === 'live'): ?>
                                                                        <span class="text-danger small"><i class="fas fa-circle me-1"></i>LIVE</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="card-text"><?php echo htmlspecialchars(substr($channel['description'], 0, 100)) . '...'; ?></p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-eye me-1"></i>
                                                                <small><?php echo number_format($channel['viewer_count']); ?> viewers</small>
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-danger watch-btn" data-channel-id="<?php echo $channel['id']; ?>">
                                                                <i class="fas fa-play me-1"></i>Watch
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="text-center py-5">
                                                <i class="fas fa-tv fa-4x text-muted mb-3"></i>
                                                <h5>No <?php echo ucfirst($category); ?> Channels Available</h5>
                                                <p class="text-muted">Check back later for new channels in this category</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <!-- Category Info -->
                            <div class="card shadow">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i><?php echo ucfirst($category); ?> Category</h5>
                                    <p class="card-text">
                                        <?php 
                                        switch($category) {
                                            case 'news':
                                                echo 'Stay updated with the latest breaking news and current affairs from around the world.';
                                                break;
                                            case 'sports':
                                                echo 'Watch live sports events, matches, and sports analysis programs.';
                                                break;
                                            case 'entertainment':
                                                echo 'Enjoy celebrity news, movies, music, and entertainment shows.';
                                                break;
                                            case 'business':
                                                echo 'Follow market updates, business news, and financial analysis.';
                                                break;
                                            case 'technology':
                                                echo 'Discover the latest tech news, gadget reviews, and innovation stories.';
                                                break;
                                            case 'international':
                                                echo 'Get global perspectives and international news coverage.';
                                                break;
                                        }
                                        ?>
                                    </p>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-tv me-1"></i>
                                            <?php echo isset($channels_by_category[$category]) ? count($channels_by_category[$category]) : 0; ?> channels available
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Live Stream Schedule Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You'll be notified 15 minutes before the stream starts.</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="emailReminder" checked>
                    <label class="form-check-label" for="emailReminder">
                        Email notification
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="browserReminder" checked>
                    <label class="form-check-label" for="browserReminder">
                        Browser notification
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReminder()">Set Reminder</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Video.js JavaScript -->
<script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>

<script>
let currentChannelId = <?php echo $current_channel ? $current_channel['id'] : 'null'; ?>;
let chatUpdateInterval = null;
let viewerUpdateInterval = null;

// Toggle fullscreen for video
function toggleFullscreen() {
    const videoWrapper = document.getElementById('videoWrapper');
    if (videoWrapper) {
        if (videoWrapper.requestFullscreen) {
            videoWrapper.requestFullscreen();
        } else if (videoWrapper.webkitRequestFullscreen) {
            videoWrapper.webkitRequestFullscreen();
        } else if (videoWrapper.msRequestFullscreen) {
            videoWrapper.msRequestFullscreen();
        }
    }
}

// Update live viewer count
function updateViewerCount() {
    const viewerCount = document.getElementById('liveViewerCount');
    if (viewerCount) {
        const currentCount = parseInt(viewerCount.textContent.replace(/,/g, ''));
        const change = Math.floor(Math.random() * 51) - 25;
        const newCount = Math.max(500, currentCount + change);
        viewerCount.textContent = newCount.toLocaleString();
    }
}

// Update video player with new channel
function updateVideoPlayer(channel) {
    const videoWrapper = document.getElementById('videoWrapper');
    if (!videoWrapper) return;
    
    let playerHtml = '';
    
    if (channel.stream_type === 'youtube') {
        let video_id = '';
        if (channel.stream_url.includes('youtube.com/watch?v=')) {
            video_id = channel.stream_url.split('v=')[1].split('&')[0];
        } else if (channel.stream_url.includes('youtu.be/')) {
            video_id = channel.stream_url.split('youtu.be/')[1].split('?')[0];
        } else if (channel.stream_url.includes('youtube.com/embed/')) {
            video_id = channel.stream_url.split('embed/')[1].split('?')[0];
        }
        
        if (video_id) {
            playerHtml = `<iframe id="youtubePlayer" class="embed-responsive-item" 
                          src="https://www.youtube.com/embed/${video_id}?autoplay=1&mute=1&rel=0&modestbranding=1" 
                          width="100%" height="500" frameborder="0" 
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                          allowfullscreen
                          onerror="handleYouTubeError(this, '${channel.name}')"></iframe>`;
        } else {
            playerHtml = createFallbackPlayer(channel);
        }
    } else if (channel.stream_type === 'hls') {
        playerHtml = `<video id="hlsPlayer" class="video-js vjs-default-skin" controls preload="auto" 
                      width="100%" height="500" data-setup="{}" 
                      onerror="handleHLSError(this, '${channel.name}')">
                      <source src="${channel.stream_url}" type="application/x-mpegURL">
                      Your browser does not support HTML5 video.
                      </video>`;
    } else if (channel.stream_type === 'iframe') {
        playerHtml = `<iframe src="${channel.stream_url}" width="100%" height="500" 
                      frameborder="0" allowfullscreen
                      onerror="handleIframeError(this, '${channel.name}')"></iframe>`;
    } else {
        playerHtml = createFallbackPlayer(channel);
    }
    
    videoWrapper.innerHTML = playerHtml;
    
    // Initialize Video.js if needed
    if (channel.stream_type === 'hls' && window.videojs) {
        setTimeout(() => {
            try {
                const player = videojs('hlsPlayer');
                player.ready(() => {
                    player.play().catch(e => console.log('Autoplay prevented:', e));
                });
                player.on('error', () => {
                    console.log('HLS player error, showing fallback');
                    createFallbackPlayer(channel);
                });
            } catch (e) {
                console.log('Video.js initialization error:', e);
                videoWrapper.innerHTML = createFallbackPlayer(channel);
            }
        }, 100);
    }
    
    // Add error handling for YouTube iframe
    setTimeout(() => {
        const youtubeFrame = document.getElementById('youtubePlayer');
        if (youtubeFrame) {
            youtubeFrame.addEventListener('error', () => {
                console.log('YouTube iframe error, showing fallback');
                videoWrapper.innerHTML = createFallbackPlayer(channel);
            });
        }
    }, 1000);
}

// Handle YouTube errors
function handleYouTubeError(iframe, channelName) {
    console.log('YouTube connection failed for:', channelName);
    const videoWrapper = document.getElementById('videoWrapper');
    if (videoWrapper) {
        videoWrapper.innerHTML = createOfflinePlayer(channelName, 'YouTube connection closed');
    }
}

// Handle HLS errors  
function handleHLSError(video, channelName) {
    console.log('HLS stream failed for:', channelName);
    const videoWrapper = document.getElementById('videoWrapper');
    if (videoWrapper) {
        videoWrapper.innerHTML = createOfflinePlayer(channelName, 'Stream temporarily unavailable');
    }
}

// Handle iframe errors
function handleIframeError(iframe, channelName) {
    console.log('Iframe stream failed for:', channelName);
    const videoWrapper = document.getElementById('videoWrapper');
    if (videoWrapper) {
        videoWrapper.innerHTML = createOfflinePlayer(channelName, 'Stream connection failed');
    }
}

// Create offline player when stream fails
function createOfflinePlayer(channelName, errorMessage) {
    return `<div class="text-center text-white p-5" style="height: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <i class="fas fa-exclamation-triangle fa-4x mb-4 text-warning"></i>
        <h3 class="mb-3">${channelName}</h3>
        <p class="mb-4">${errorMessage}</p>
        <div class="bg-dark bg-opacity-50 p-4 rounded" style="max-width: 400px;">
            <p class="mb-3 text-warning"><i class="fas fa-info-circle me-2"></i>Stream Status: <span class="badge bg-warning">OFFLINE</span></p>
            <p class="mb-3"><i class="fas fa-sync me-2"></i>Trying to reconnect...</p>
            <button class="btn btn-danger" onclick="location.reload()">
                <i class="fas fa-redo me-2"></i>Retry Connection
            </button>
        </div>
        <div class="mt-4">
            <small class="text-white-50">The stream will automatically reconnect when available</small>
        </div>
    </div>`;
}

// Create fallback player when no stream URL
function createFallbackPlayer(channel) {
    return `<div class="text-center text-white p-5" style="height: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fas fa-broadcast-tower fa-4x mb-4 text-danger"></i>
        <h3 class="mb-3">${channel.name}</h3>
        <p class="mb-4">Live stream will be available when the channel is broadcasting</p>
        <div class="bg-dark p-3 rounded" style="max-width: 400px;">
            <p class="mb-2"><i class="fas fa-info-circle me-2"></i>Channel Status: 
            <span class="badge bg-${channel.status === 'live' ? 'success' : 'secondary'}">${channel.status.toUpperCase()}</span></p>
            <p class="mb-2"><i class="fas fa-users me-2"></i>Viewers: ${channel.viewer_count}</p>
            <p class="mb-0"><i class="fas fa-globe me-2"></i>Category: ${channel.category}</p>
        </div>
    </div>`;
}

// Update channel information
function updateChannelInfo(channel) {
    // Update channel name
    const channelNameElement = document.querySelector('.live-header h4');
    if (channelNameElement) {
        channelNameElement.textContent = channel.name;
    }
    
    // Update viewer count
    const viewerCountElement = document.getElementById('liveViewerCount');
    if (viewerCountElement) {
        viewerCountElement.textContent = parseInt(channel.viewer_count).toLocaleString();
    }
    
    // Update channel description
    const descriptionElement = document.querySelector('.card-text');
    if (descriptionElement) {
        descriptionElement.textContent = channel.description;
    }
    
    // Update category
    const categoryElement = document.querySelector('.stream-stats .d-flex:nth-child(2) strong');
    if (categoryElement) {
        categoryElement.textContent = channel.category.charAt(0).toUpperCase() + channel.category.slice(1);
    }
    
    // Update language
    const languageElement = document.querySelector('.stream-stats .d-flex:last-child strong');
    if (languageElement) {
        languageElement.textContent = channel.language.toUpperCase();
    }
}

// Update active channel in sidebar
function updateActiveChannel(channelId) {
    // Remove active class from all channel items
    document.querySelectorAll('.channel-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to current channel
    const activeChannel = document.querySelector(`[data-channel-id="${channelId}"]`);
    if (activeChannel) {
        activeChannel.classList.add('active');
    }
}

// Update chat for new channel
function updateChat(channelId) {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = '<div class="text-muted text-center">Loading chat messages...</div>';
        
        // Fetch chat messages for this channel
        fetch(`api/get_chat.php?channel_id=${channelId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    chatMessages.innerHTML = data.messages.map(msg => `
                        <div class="chat-message mb-2">
                            <strong>${msg.username}:</strong> ${msg.message}
                            <small class="text-muted d-block">${msg.time}</small>
                        </div>
                    `).join('');
                } else {
                    chatMessages.innerHTML = '<div class="text-muted text-center">No messages yet. Start the conversation!</div>';
                }
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(error => {
                console.error('Error loading chat:', error);
                chatMessages.innerHTML = '<div class="text-muted text-center">Chat unavailable</div>';
            });
    }
}

// Load channel
function loadChannel(channelId) {
    if (channelId === currentChannelId) return;
    
    // Show loading
    const videoWrapper = document.getElementById('videoWrapper');
    if (videoWrapper) {
        videoWrapper.innerHTML = '<div class="text-center text-white p-5" style="height: 500px; display: flex; align-items: center; justify-content: center;"><div><i class="fas fa-spinner fa-spin fa-3x mb-3"></i><h4>Loading Channel...</h4></div></div>';
    }
    
    // Fetch channel data
    fetch(`api/get_channel.php?id=${channelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentChannelId = channelId;
                updateVideoPlayer(data.channel);
                updateChannelInfo(data.channel);
                updateChat(data.channel.id);
                updateActiveChannel(channelId);
                
                // Switch to featured tab
                const featuredTab = document.getElementById('featured-tab');
                if (featuredTab) {
                    const tab = new bootstrap.Tab(featuredTab);
                    tab.show();
                }
            } else {
                console.error('Error loading channel:', data.message);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            location.reload();
        });
}

// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to channel items
    document.querySelectorAll('.clickable-channel').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const channelId = this.getAttribute('data-channel-id');
            if (channelId) {
                loadChannel(parseInt(channelId));
            }
        });
    });
    
    // Add click handlers to watch buttons
    document.querySelectorAll('.watch-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const channelId = this.getAttribute('data-channel-id');
            if (channelId) {
                loadChannel(parseInt(channelId));
            }
        });
    });
    
    // Handle chat form submission
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const chatInput = document.getElementById('chatInput');
            const chatSubmit = document.getElementById('chatSubmit');
            
            if (chatInput && chatInput.value.trim() && currentChannelId) {
                // Disable form during submission
                chatInput.disabled = true;
                chatSubmit.disabled = true;
                
                const message = chatInput.value.trim();
                
                // Send message to server
                fetch('api/send_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        channel_id: currentChannelId,
                        username: 'Guest', // You can modify this to use actual user session
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        chatInput.value = '';
                        updateChat(currentChannelId);
                    } else {
                        console.error('Error sending message:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Re-enable form
                    chatInput.disabled = false;
                    chatSubmit.disabled = false;
                    chatInput.focus();
                });
            }
        });
    }
    
    // Start viewer count updates
    if (viewerUpdateInterval) {
        clearInterval(viewerUpdateInterval);
    }
    viewerUpdateInterval = setInterval(updateViewerCount, 5000);
    
    // Start chat updates for live channels
    if (currentChannelId) {
        if (chatUpdateInterval) {
            clearInterval(chatUpdateInterval);
        }
        chatUpdateInterval = setInterval(() => {
            updateChat(currentChannelId);
        }, 10000);
    }
});

// Comment system functions
function cancelComment(newsId) {
    const commentsSection = document.getElementById(`inline-comments-${newsId}`);
    const commentInput = commentsSection?.querySelector('.comment-input');
    const commentActions = commentsSection?.querySelector('.comment-actions');
    
    if (commentInput) {
        commentInput.value = '';
        commentInput.style.height = 'auto';
    }
    
    if (commentActions) {
        commentActions.style.display = 'none';
    }
}

function submitInlineComment(newsId, comment, form) {
    // Validate comment
    if (!comment || comment.trim() === '') {
        showNotification('Please enter a comment', 'error');
        return;
    }
    
    // Disable submit button
    const submitBtn = form.querySelector('.post-comment-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Posting...';
    
    const data = {
        news_id: parseInt(newsId),
        comment: comment,
        parent_comment_id: form.dataset.parentId ? parseInt(form.dataset.parentId) : null
    };
    
    fetch('api/submit-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Clear form
            const commentInput = form.querySelector('.comment-input');
            commentInput.value = '';
            commentInput.style.height = 'auto';
            
            // Hide actions
            const commentActions = form.querySelector('.comment-actions');
            commentActions.style.display = 'none';
            
            // Show success notification
            showNotification('Comment posted successfully!', 'success');
            
            // Reload comments if function exists
            if (typeof loadInlineComments === 'function') {
                loadInlineComments(newsId);
            }
        } else {
            showNotification(result.message || 'Error submitting comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showNotification('Error submitting comment', 'error');
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Enhanced comment input handling
document.addEventListener('DOMContentLoaded', function() {
    // Add input event listeners to comment inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('comment-input')) {
            const commentActions = e.target.closest('.facebook-comment-form')?.querySelector('.comment-actions');
            const postBtn = commentActions?.querySelector('.post-comment-btn');
            
            if (commentActions && postBtn) {
                if (e.target.value.trim()) {
                    commentActions.style.display = 'flex';
                    postBtn.disabled = false;
                } else {
                    commentActions.style.display = 'none';
                    postBtn.disabled = true;
                }
            }
            
            // Auto-resize textarea
            e.target.style.height = 'auto';
            e.target.style.height = e.target.scrollHeight + 'px';
        }
    });
    
    // Handle comment form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.closest('.facebook-comment-form')) {
            e.preventDefault();
            const form = e.target;
            const newsId = form.closest('.facebook-comments-section').id.replace('inline-comments-', '');
            const comment = form.querySelector('.comment-input').value.trim();
            
            if (comment) {
                submitInlineComment(newsId, comment, form);
            }
        }
    });
});

// Notification function (if not already defined)
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        notification.className = 'alert alert-dismissible fade show position-fixed top-0 end-0 m-3';
        notification.style.zIndex = '9999';
        document.body.appendChild(notification);
    }
    
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
</body>
</html>
