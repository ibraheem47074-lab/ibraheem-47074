<?php
require_once 'config/database.php';
$page_title = 'Multi-Camera Live TV';

// Get stream ID from URL
$stream_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($stream_id === 0) {
    // Get current live multi-camera stream
    $live_stream = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM live_stream WHERE status = 'online' AND camera_count > 1 ORDER BY id DESC LIMIT 1"
    ));
} else {
    // Get specific stream
    $stmt = mysqli_prepare($conn, "SELECT * FROM live_stream WHERE id = ? AND camera_count > 1");
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $live_stream = mysqli_fetch_assoc($result);
}

if (!$live_stream) {
    // Redirect to regular live page if no multi-camera stream found
    header('Location: live.php');
    exit();
}

// Get all cameras for this stream
$cameras_query = "SELECT * FROM stream_cameras WHERE stream_id = ? ORDER BY camera_number";
$cameras_stmt = mysqli_prepare($conn, $cameras_query);
mysqli_stmt_bind_param($cameras_stmt, 'i', $live_stream['id']);
mysqli_stmt_execute($cameras_stmt);
$cameras_result = mysqli_stmt_get_result($cameras_stmt);

// Get active camera
$stmt = mysqli_prepare($conn, "SELECT * FROM stream_cameras WHERE stream_id = ? AND camera_number = ?");
mysqli_stmt_bind_param($stmt, 'ii', $live_stream['id'], $live_stream['active_camera']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$active_camera = mysqli_fetch_assoc($result);

// Get overlays for this stream
$overlays_query = "SELECT so.*, ot.html_template, ot.css_styles 
                  FROM stream_overlays so 
                  LEFT JOIN overlay_templates ot ON so.template_id = ot.id 
                  WHERE so.stream_id = ? AND so.is_visible = 1 
                  ORDER BY so.z_index ASC";
$overlays_stmt = mysqli_prepare($conn, $overlays_query);
mysqli_stmt_bind_param($overlays_stmt, 'i', $live_stream['id']);
mysqli_stmt_execute($overlays_stmt);
$overlays_result = mysqli_stmt_get_result($overlays_stmt);

// Get stream configuration
$multi_camera_config = json_decode($live_stream['multi_camera_config'] ?? '{}', true);
$layout = $multi_camera_config['layout'] ?? 'grid';

// Simulate live viewer count
$viewer_count = rand(2000, 8000);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($live_stream['title']); ?> - Multi-Camera Live | PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #000;
            color: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /* Multi-camera layout styles */
        .multi-camera-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            background: #000;
        }

        /* Grid Layout (2x2 for 4 cameras, 2x1 for 2 cameras) */
        .layout-grid {
            display: grid;
            height: 100%;
            gap: 2px;
        }
        .layout-grid.cameras-2 { grid-template-columns: 1fr 1fr; }
        .layout-grid.cameras-3 { 
            grid-template-columns: 2fr 1fr; 
            grid-template-rows: 1fr 1fr;
        }
        .layout-grid.cameras-3 .camera-3 { grid-column: 2; grid-row: 1 / 3; }
        .layout-grid.cameras-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }

        /* Side-by-side layout */
        .layout-side-by-side {
            display: flex;
            height: 100%;
            gap: 2px;
        }
        .layout-side-by-side .camera-feed {
            flex: 1;
        }

        /* Picture-in-Picture layout */
        .layout-pip {
            position: relative;
            height: 100%;
        }
        .layout-pip .main-camera {
            width: 100%;
            height: 100%;
        }
        .layout-pip .pip-camera {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 200px;
            border: 3px solid #dc3545;
            border-radius: 8px;
            overflow: hidden;
        }

        .camera-feed {
            position: relative;
            background: #111;
            border: 1px solid #333;
            overflow: hidden;
        }

        .camera-feed.active {
            border-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }

        .camera-feed video,
        .camera-feed iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .camera-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }

        .camera-feed.active .camera-label {
            background: #dc3545;
        }

        /* Control panel */
        .control-panel {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.9);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 15px 20px;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .camera-switch-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            margin: 0 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .camera-switch-btn:hover,
        .camera-switch-btn.active {
            background: #dc3545;
            border-color: #dc3545;
        }

        /* Live indicator */
        .live-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            z-index: 1001;
            display: flex;
            align-items: center;
            animation: pulse 2s infinite;
        }

        .live-dot {
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            margin-right: 10px;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Viewer count */
        .viewer-count {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 1001;
        }

        /* Overlay styles */
        .overlay-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 100;
        }

        .overlay-item {
            position: absolute;
            pointer-events: none;
        }

        /* Fullscreen button */
        .fullscreen-btn {
            position: fixed;
            top: 20px;
            right: 200px;
            background: rgba(0,0,0,0.8);
            border: 1px solid #333;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }

        .fullscreen-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Layout switcher */
        .layout-switcher {
            position: fixed;
            top: 70px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 10px;
            z-index: 1001;
        }

        .layout-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
        }

        .layout-btn.active {
            background: #667eea;
            border-color: #667eea;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .control-panel {
                bottom: 10px;
                padding: 10px 15px;
                width: 90%;
            }
            
            .camera-switch-btn {
                padding: 6px 12px;
                font-size: 11px;
            }
            
            .layout-pip .pip-camera {
                width: 200px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="multi-camera-container">
        <!-- Camera Feeds -->
        <div id="cameraContainer" class="layout-<?php echo $layout; ?> cameras-<?php echo $live_stream['camera_count']; ?>">
            <?php 
            mysqli_data_seek($cameras_result, 0);
            $camera_number = 1;
            while ($camera = mysqli_fetch_assoc($cameras_result)): 
            ?>
                <div class="camera-feed <?php echo $camera['camera_number'] == $live_stream['active_camera'] ? 'active' : ''; ?>" 
                     id="camera-<?php echo $camera['camera_number']; ?>">
                    <div class="camera-label">
                        <i class="fas fa-video me-1"></i>
                        <?php echo htmlspecialchars($camera['camera_name']); ?>
                        <?php if ($camera['camera_number'] == $live_stream['active_camera']): ?>
                            <span class="badge bg-danger ms-2">LIVE</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($camera['embed_code']): ?>
                        <?php echo $camera['embed_code']; ?>
                    <?php elseif ($camera['stream_url']): ?>
                        <?php
                        // Handle different streaming sources
                        if (strpos($camera['stream_url'], 'youtube.com') !== false || strpos($camera['stream_url'], 'youtu.be') !== false) {
                            $video_id = '';
                            if (strpos($camera['stream_url'], 'youtube.com/watch?v=') !== false) {
                                $video_id = substr($camera['stream_url'], strpos($camera['stream_url'], 'v=') + 2);
                            } elseif (strpos($camera['stream_url'], 'youtu.be/') !== false) {
                                $video_id = substr($camera['stream_url'], strpos($camera['stream_url'], 'youtu.be/') + 9);
                            }
                            $video_id = explode('?', $video_id)[0];
                            
                            echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '?autoplay=1&mute=0" 
                                    frameborder="0" allowfullscreen></iframe>';
                        } else {
                            echo '<iframe src="' . htmlspecialchars($camera['stream_url']) . '" 
                                    frameborder="0" allowfullscreen></iframe>';
                        }
                        ?>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
                            <div class="text-center">
                                <i class="fas fa-video fa-3x mb-3"></i>
                                <p>Camera Feed Not Available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php 
            $camera_number++;
            endwhile; 
            ?>
        </div>

        <!-- Overlay Container -->
        <div class="overlay-container" id="overlayContainer">
            <?php while ($overlay = mysqli_fetch_assoc($overlays_result)): ?>
                <?php
                $custom_data = json_decode($overlay['custom_data'] ?? '{}', true);
                $html_template = $overlay['html_template'] ?? '';
                
                // Replace template variables
                foreach ($custom_data as $key => $value) {
                    $html_template = str_replace('{{' . $key . '}}', $value, $html_template);
                }
                ?>
                <div class="overlay-item" 
                     style="left: <?php echo $overlay['position_x']; ?>px; 
                            top: <?php echo $overlay['position_y']; ?>px; 
                            z-index: <?php echo $overlay['z_index']; ?>;">
                    <?php echo $html_template; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Live Indicator -->
        <div class="live-indicator">
            <div class="live-dot"></div>
            <span>MULTI-CAM LIVE</span>
        </div>

        <!-- Viewer Count -->
        <div class="viewer-count">
            <i class="fas fa-eye me-2"></i>
            <span id="viewerCount"><?php echo number_format($viewer_count); ?></span> viewers
        </div>

        <!-- Fullscreen Button -->
        <button class="fullscreen-btn" onclick="toggleFullscreen()">
            <i class="fas fa-expand me-2"></i>Fullscreen
        </button>

        <!-- Layout Switcher -->
        <div class="layout-switcher">
            <button class="layout-btn <?php echo $layout == 'grid' ? 'active' : ''; ?>" onclick="switchLayout('grid')">Grid</button>
            <button class="layout-btn <?php echo $layout == 'side-by-side' ? 'active' : ''; ?>" onclick="switchLayout('side-by-side')">Side by Side</button>
            <button class="layout-btn <?php echo $layout == 'pip' ? 'active' : ''; ?>" onclick="switchLayout('pip')">PiP</button>
        </div>

        <!-- Camera Control Panel -->
        <div class="control-panel">
            <div class="d-flex align-items-center">
                <span class="me-3 small">Switch Camera:</span>
                <?php 
                mysqli_data_seek($cameras_result, 0);
                while ($camera = mysqli_fetch_assoc($cameras_result)): 
                ?>
                    <button class="camera-switch-btn <?php echo $camera['camera_number'] == $live_stream['active_camera'] ? 'active' : ''; ?>" 
                            onclick="switchCamera(<?php echo $camera['camera_number']; ?>)">
                        <i class="fas fa-video me-1"></i>
                        Cam <?php echo $camera['camera_number']; ?>
                    </button>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        const streamId = <?php echo $live_stream['id']; ?>;
        const totalCameras = <?php echo $live_stream['camera_count']; ?>;
        let currentLayout = '<?php echo $layout; ?>';

        // Switch camera function
        function switchCamera(cameraNumber) {
            // Update active camera visually
            document.querySelectorAll('.camera-feed').forEach(feed => {
                feed.classList.remove('active');
            });
            document.getElementById('camera-' + cameraNumber).classList.add('active');

            // Update control panel buttons
            document.querySelectorAll('.camera-switch-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Update camera labels
            document.querySelectorAll('.camera-label .badge').forEach(badge => {
                badge.remove();
            });
            const activeLabel = document.querySelector('#camera-' + cameraNumber + ' .camera-label');
            if (activeLabel && !activeLabel.querySelector('.badge')) {
                activeLabel.innerHTML += '<span class="badge bg-danger ms-2">LIVE</span>';
            }

            // Send switch request to server
            fetch('admin/advanced-live-stream.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=switch_camera&stream_id=${streamId}&camera_number=${cameraNumber}`
            }).catch(error => console.error('Error switching camera:', error));
        }

        // Switch layout function
        function switchLayout(newLayout) {
            const container = document.getElementById('cameraContainer');
            
            // Remove all layout classes
            container.className = '';
            
            // Add new layout class
            container.classList.add('layout-' + newLayout, 'cameras-' + totalCameras);
            
            // Update layout buttons
            document.querySelectorAll('.layout-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            currentLayout = newLayout;
        }

        // Toggle fullscreen
        function toggleFullscreen() {
            const container = document.querySelector('.multi-camera-container');
            
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    console.error('Error attempting to enable fullscreen:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Update viewer count
        function updateViewerCount() {
            const viewerElement = document.getElementById('viewerCount');
            const currentCount = parseInt(viewerElement.textContent.replace(',', ''));
            
            // Simulate realistic viewer changes
            const change = Math.floor(Math.random() * 51) - 25;
            const newCount = Math.max(1000, currentCount + change);
            
            viewerElement.textContent = newCount.toLocaleString();
        }

        // Update viewer count every 3 seconds
        setInterval(updateViewerCount, 3000);

        // Handle ESC key for fullscreen exit
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.fullscreenElement) {
                document.exitFullscreen();
            }
        });

        // Auto-switch cameras feature (optional - can be enabled)
        let autoSwitchEnabled = false;
        let autoSwitchInterval = null;

        function toggleAutoSwitch() {
            autoSwitchEnabled = !autoSwitchEnabled;
            
            if (autoSwitchEnabled) {
                let currentCamera = <?php echo $live_stream['active_camera']; ?>;
                
                autoSwitchInterval = setInterval(() => {
                    currentCamera = (currentCamera % totalCameras) + 1;
                    switchCamera(currentCamera);
                }, 10000); // Switch every 10 seconds
            } else {
                clearInterval(autoSwitchInterval);
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key >= '1' && e.key <= '4') {
                const cameraNumber = parseInt(e.key);
                if (cameraNumber <= totalCameras) {
                    switchCamera(cameraNumber);
                }
            } else if (e.key === 'f' || e.key === 'F') {
                toggleFullscreen();
            } else if (e.key === 'l' || e.key === 'L') {
                // Cycle through layouts
                const layouts = ['grid', 'side-by-side', 'pip'];
                const currentIndex = layouts.indexOf(currentLayout);
                const nextLayout = layouts[(currentIndex + 1) % layouts.length];
                switchLayout(nextLayout);
            }
        });

        // Initialize tooltips and other UI elements
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Multi-camera live stream initialized');
            console.log('Stream: <?php echo htmlspecialchars($live_stream['title']); ?>');
            console.log('Cameras: <?php echo $live_stream['camera_count']; ?>');
            console.log('Layout: <?php echo $layout; ?>');
        });
    </script>
</body>
</html>
