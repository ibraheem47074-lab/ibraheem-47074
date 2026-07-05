<?php
require_once '../config/database.php';

// Get live stream information
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream ORDER BY id DESC LIMIT 1"));

// Get stream statistics
$stream_stats = [];
if ($live_stream) {
    $stream_stats['total_views'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM stream_views WHERE stream_id = " . $live_stream['id']))['count'] ?? 0;
    $stream_stats['active_viewers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM stream_views WHERE stream_id = " . $live_stream['id'] . " AND is_active = 1"))['count'] ?? 0;
}

// Create tables if they don't exist
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_stream'");
if (mysqli_num_rows($table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE live_stream (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        stream_url VARCHAR(500),
        stream_key VARCHAR(255),
        status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
        started_at TIMESTAMP NULL,
        stopped_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
}

$views_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'stream_views'");
if (mysqli_num_rows($views_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE stream_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stream_id INT NOT NULL,
        viewer_ip VARCHAR(45),
        viewer_session VARCHAR(255),
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT 1,
        FOREIGN KEY (stream_id) REFERENCES live_stream(id) ON DELETE CASCADE
    )");
}

// Record viewer
if ($live_stream && $live_stream['status'] === 'online') {
    $viewer_ip = $_SERVER['REMOTE_ADDR'];
    $viewer_session = session_id();
    
    // Check if viewer already recorded
    $existing_viewer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM stream_views WHERE stream_id = " . $live_stream['id'] . " AND viewer_session = '$viewer_session' AND is_active = 1"));
    
    if (!$existing_viewer) {
        $query = "INSERT INTO stream_views (stream_id, viewer_ip, viewer_session) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iss', $live_stream['id'], $viewer_ip, $viewer_session);
        mysqli_stmt_execute($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Stream - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            color: white;
        }
        
        .live-header {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
        }
        
        .live-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .video-wrapper {
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
        }
        
        .video-js {
            width: 100%;
            height: 70vh;
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            background: #ff0000;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .live-offline {
            background: #6c757d;
            animation: none;
        }
        
        .stream-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .chat-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            height: 70vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        
        .chat-message {
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .chat-input {
            display: flex;
            gap: 0.5rem;
        }
        
        .chat-input input {
            flex: 1;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.75rem 1rem;
        }
        
        .chat-input input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .chat-input button {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .video-js .vjs-big-play-button {
            background: rgba(102, 126, 234, 0.8);
            border: none;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            font-size: 3rem;
        }
        
        .video-js .vjs-control-bar {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
        }
        
        .no-stream {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
        }
        
        .no-stream i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <header class="live-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-broadcast-tower me-3"></i>PK Live News
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="live-indicator <?php echo ($live_stream && $live_stream['status'] === 'online') ? '' : 'live-offline'; ?>">
                        <i class="fas fa-circle me-2"></i>
                        <?php echo ($live_stream && $live_stream['status'] === 'online') ? 'LIVE NOW' : 'OFFLINE'; ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div class="live-container">
        <?php if ($live_stream && $live_stream['status'] === 'online'): ?>
            <!-- Stream Info -->
            <div class="stream-info">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2"><?php echo htmlspecialchars($live_stream['title']); ?></h2>
                        <p class="mb-0 opacity-75"><?php echo htmlspecialchars($live_stream['description'] ?? ''); ?></p>
                        <small class="opacity-50">
                            Started: <?php echo date('g:i A', strtotime($live_stream['started_at'])); ?> • 
                            Duration: <?php echo $live_stream['started_at'] ? gmdate('H:i:s', time() - strtotime($live_stream['started_at'])) : '00:00:00'; ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end gap-3">
                            <div class="text-center">
                                <i class="fas fa-eye fa-2x mb-2"></i>
                                <div class="fw-bold"><?php echo number_format($stream_stats['active_viewers']); ?></div>
                                <small>Watching</small>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <div class="fw-bold"><?php echo number_format($stream_stats['total_views']); ?></div>
                                <small>Total Views</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Video Player -->
                <div class="col-lg-8">
                    <div class="video-wrapper">
                        <video-js 
                            id="livePlayer" 
                            controls 
                            preload="auto" 
                            poster="https://via.placeholder.com/1280x720/1e3c72/ffffff?text=LIVE+STREAM"
                            data-setup='{"fluid": true, "liveui": true, "autoplay": true}'>
                            <source src="<?php echo htmlspecialchars($live_stream['stream_url'] ?? 'http://localhost:8080/war.m3u8'); ?>" type="application/x-mpegURL">
                        </video-js>
                    </div>

                    <!-- Stream Statistics -->
                    <div class="row mt-3">
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-signal fa-2x mb-2"></i>
                                <h3>HD</h3>
                                <small>Quality</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-globe fa-2x mb-2"></i>
                                <h3>Global</h3>
                                <small>Reach</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-bolt fa-2x mb-2"></i>
                                <h3>Low</h3>
                                <small>Latency</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Chat -->
                <div class="col-lg-4">
                    <div class="chat-container">
                        <h5 class="mb-3">
                            <i class="fas fa-comments me-2"></i>Live Chat
                        </h5>
                        <div class="chat-messages" id="chatMessages">
                            <div class="chat-message">
                                <strong>System:</strong> Welcome to the live stream!
                            </div>
                            <div class="chat-message">
                                <strong>John:</strong> Great coverage!
                            </div>
                            <div class="chat-message">
                                <strong>Sarah:</strong> Loving the content 🎉
                            </div>
                        </div>
                        <div class="chat-input">
                            <input type="text" id="chatInput" placeholder="Type your message..." maxlength="200">
                            <button onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- No Stream Available -->
            <div class="no-stream">
                <i class="fas fa-video-slash"></i>
                <h2>No Live Stream Currently</h2>
                <p class="lead opacity-75">Check back later for live news coverage</p>
                <div class="mt-4">
                    <button class="btn btn-light btn-lg" onclick="checkStreamStatus()">
                        <i class="fas fa-sync me-2"></i>Check Again
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Video.js player
        let player;
        
        document.addEventListener('DOMContentLoaded', function() {
            const videoElement = document.getElementById('livePlayer');
            if (videoElement) {
                player = videojs('livePlayer', {
                    controls: true,
                    fluid: true,
                    liveui: true,
                    autoplay: true,
                    muted: true,
                    preload: 'auto',
                    html5: {
                        hlsjsConfig: {
                            enableWorker: false,
                            lowLatencyMode: true,
                        }
                    }
                });

                // Handle player events
                player.ready(function() {
                    console.log('Player is ready');
                });

                player.on('error', function(error) {
                    console.error('Player error:', error);
                    // Retry connection
                    setTimeout(() => {
                        player.src({
                            src: player.currentSource().src,
                            type: 'application/x-mpegURL'
                        });
                    }, 5000);
                });

                // Auto-reconnect on connection loss
                player.on('stalled', function() {
                    console.log('Stream stalled, attempting to reconnect...');
                    setTimeout(() => {
                        player.load();
                    }, 3000);
                });

                // Update viewer count periodically
                setInterval(updateViewerCount, 30000);
            }
        });

        // Chat functionality
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const messages = document.getElementById('chatMessages');
            
            if (input.value.trim() === '') return;
            
            // Add message to chat (in real implementation, this would be sent to server)
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message';
            messageDiv.innerHTML = `<strong>You:</strong> ${input.value}`;
            messages.appendChild(messageDiv);
            
            // Scroll to bottom
            messages.scrollTop = messages.scrollHeight;
            
            // Clear input
            input.value = '';
            
            // Simulate response (in real implementation, this would come from server)
            setTimeout(() => {
                const responseDiv = document.createElement('div');
                responseDiv.className = 'chat-message';
                responseDiv.innerHTML = `<strong>Bot:</strong> Thanks for your message!`;
                messages.appendChild(responseDiv);
                messages.scrollTop = messages.scrollHeight;
            }, 1000);
        }

        // Enter key to send message
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Update viewer count
        function updateViewerCount() {
            // In real implementation, this would fetch from server
            console.log('Updating viewer count...');
        }

        // Check stream status
        function checkStreamStatus() {
            location.reload();
        }

        // Auto-refresh stream status every 30 seconds
        setInterval(() => {
            if (!<?php echo ($live_stream && $live_stream['status'] === 'online') ? 'true' : 'false'; ?>) {
                checkStreamStatus();
            }
        }, 30000);

        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && player) {
                // Page became visible, refresh stream
                player.load();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (player) {
                player.dispose();
            }
        });
    </script>
</body>
</html>
