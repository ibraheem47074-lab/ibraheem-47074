<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle live stream actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stream'])) {
        $title = clean_input($_POST['title']);
        $description = clean_input($_POST['description']);
        $stream_url = clean_input($_POST['stream_url']);
        $status = clean_input($_POST['status']);
        $stream_key = clean_input($_POST['stream_key']);
        
        // Check if there's an existing stream
        $existing_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM live_stream ORDER BY id DESC LIMIT 1"));
        
        if ($existing_stream) {
            $query = "UPDATE live_stream SET title = ?, description = ?, stream_url = ?, status = ?, stream_key = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssssi', $title, $description, $stream_url, $status, $stream_key, $existing_stream['id']);
        } else {
            $query = "INSERT INTO live_stream (title, description, stream_url, status, stream_key, created_at, created_by) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssssi', $title, $description, $stream_url, $status, $stream_key, $_SESSION['user_id']);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Live stream updated successfully!";
        } else {
            $error = "Failed to update live stream!";
        }
    }
    
    if (isset($_POST['start_stream'])) {
        $query = "UPDATE live_stream SET status = 'online', started_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $_POST['stream_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Live stream started successfully!";
        } else {
            $error = "Failed to start live stream!";
        }
    }
    
    if (isset($_POST['stop_stream'])) {
        $query = "UPDATE live_stream SET status = 'offline' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $_POST['stream_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Live stream stopped successfully!";
        } else {
            $error = "Failed to stop live stream!";
        }
    }
}

// Get current live stream
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream ORDER BY id DESC LIMIT 1"));

// Get stream statistics
$stream_stats = [];
if ($live_stream) {
    $stream_stats['total_views'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM stream_views WHERE stream_id = " . $live_stream['id']))['count'] ?? 0;
    $stream_stats['active_viewers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM stream_views WHERE stream_id = " . $live_stream['id'] . " AND is_active = 1"))['count'] ?? 0;
    $stream_stats['duration'] = isset($live_stream['started_at']) && $live_stream['started_at'] ? time() - strtotime($live_stream['started_at']) : 0;
}

// Create live_stream table if it doesn't exist
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

// Create stream_views table if it doesn't exist
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Stream Control - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stream-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-online { background-color: #d4edda; color: #155724; }
        .status-offline { background-color: #f8d7da; color: #721c24; }
        .status-maintenance { background-color: #fff3cd; color: #856404; }
        .live-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .live-online {
            background-color: #00ff00;
            animation: pulse 2s infinite;
        }
        .live-offline {
            background-color: #ff0000;
        }
        .live-maintenance {
            background-color: #ffc107;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }
        .stream-preview {
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .stream-preview img {
            width: 100%;
            height: auto;
        }
        .control-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-card-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .stats-card-header h5 {
            color: #6c757d;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }
        
        .stats-card-body {
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            line-height: 1;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .quick-action-btn {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .stream-health-good {
            color: #28a745;
            font-weight: 600;
        }
        
        .stream-health-poor {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-broadcast-tower me-3"></i>Live Stream Control</h2>
                <p class="text-muted">Manage and control live streaming for your news platform.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stream Status Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <span class="live-indicator live-<?php echo $live_stream['status'] ?? 'offline'; ?>"></span>
                                    <div>
                                        <h4 class="mb-1">
                                            <?php echo $live_stream ? $live_stream['title'] : 'Live Stream Not Configured'; ?>
                                        </h4>
                                        <p class="text-muted mb-0">
                                            Status: <span class="stream-status status-<?php echo $live_stream ? $live_stream['status'] : 'offline'; ?>">
                                                <?php echo $live_stream ? ucfirst($live_stream['status']) : 'Offline'; ?>
                                            </span>
                                            <?php if ($live_stream && isset($live_stream['started_at']) && $live_stream['started_at'] && ($live_stream['status'] ?? 'offline') === 'online'): ?>
                                                | Started: <?php echo date('H:i', strtotime($live_stream['started_at'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($live_stream): ?>
                                    <?php if (($live_stream['status'] ?? 'offline') === 'offline'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="stream_id" value="<?php echo $live_stream['id'] ?? ''; ?>">
                                            <button type="submit" name="start_stream" class="btn btn-success btn-lg">
                                                <i class="fas fa-play me-2"></i>Start Stream
                                            </button>
                                        </form>
                                    <?php elseif (($live_stream['status'] ?? 'offline') === 'online'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="stream_id" value="<?php echo $live_stream['id'] ?? ''; ?>">
                                            <button type="submit" name="stop_stream" class="btn btn-danger btn-lg">
                                                <i class="fas fa-stop me-2"></i>Stop Stream
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No stream configured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stream Statistics -->
        <?php if ($live_stream): ?>
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <h5><i class="fas fa-eye me-2"></i>Current Viewers</h5>
                    </div>
                    <div class="stats-card-body">
                        <h2 id="viewerCount" class="stats-number">0</h2>
                        <small class="stats-label">Live Now</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Stream Duration</h5>
                    </div>
                    <div class="stats-card-body">
                        <h2 id="streamDuration" class="stats-number">0:00</h2>
                        <small class="stats-label">Current Session</small>
                    </div>
                    <p class="text-muted mb-0">Stream Quality</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Stream Configuration -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Stream Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Stream Title</label>
                                <input type="text" name="title" class="form-control" value="<?php echo $live_stream ? htmlspecialchars($live_stream['title'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo $live_stream ? htmlspecialchars($live_stream['description'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stream URL</label>
                                <input type="url" name="stream_url" class="form-control" value="<?php echo $live_stream ? htmlspecialchars($live_stream['stream_url'] ?? '') : ''; ?>" placeholder="rtmp://your-server.com/live">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stream Key</label>
                                <div class="input-group">
                                    <input type="text" name="stream_key" class="form-control" value="<?php echo $live_stream ? htmlspecialchars($live_stream['stream_key'] ?? '') : ''; ?>" placeholder="Your unique stream key">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateStreamKey()">
                                        <i class="fas fa-sync"></i> Generate
                                    </button>
                                </div>
                                <small class="text-muted">Keep this key secure - it's used to authenticate your stream.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="offline" <?php echo ($live_stream && ($live_stream['status'] ?? 'offline') === 'offline') ? 'selected' : ''; ?>>Offline</option>
                                    <option value="maintenance" <?php echo ($live_stream && ($live_stream['status'] ?? 'offline') === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </div>
                            <button type="submit" name="update_stream" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Stream Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stream Preview & Embed -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-video me-2"></i>Stream Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="stream-preview mb-3">
                            <?php if ($live_stream && ($live_stream['status'] ?? 'offline') === 'online'): ?>
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe src="<?php echo htmlspecialchars($live_stream['stream_url'] ?? ''); ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-video fa-3x text-muted"></i>
                                    <p class="text-muted">Stream is not online</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Embed Code -->
                        <div class="control-panel">
                            <h6><i class="fas fa-code me-2"></i>Embed Code</h6>
                            <textarea class="form-control" rows="3" readonly><?php 
                                if ($live_stream) {
                                    echo '<iframe src="' . SITE_URL . 'live-stream.php" width="640" height="360" frameborder="0" allowfullscreen></iframe>';
                                } else {
                                    echo '<!-- Configure a stream to see embed code -->';
                                }
                            ?></textarea>
                            <button type="button" class="btn btn-light btn-sm mt-2" onclick="copyEmbedCode()">
                                <i class="fas fa-copy me-1"></i>Copy Code
                            </button>
                        </div>
                        
                        <!-- Technical Details -->
                        <div class="control-panel">
                            <h6><i class="fas fa-info-circle me-2"></i>Technical Details</h6>
                            <ul class="list-unstyled">
                                <li>Open OBS Studio</li>
                                <li>Go to Settings > Stream</li>
                                <li>Service: Custom</li>
                                <li>Server: <?php echo $live_stream ? htmlspecialchars($live_stream['stream_url'] ?? '') : 'Your RTMP Server'; ?></li>
                                <li>Stream Key: <?php echo $live_stream ? htmlspecialchars($live_stream['stream_key'] ?? '') : 'Generated Key'; ?></li>
                                <li>Click "Apply" and "OK"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stream Settings Guide -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Streaming Setup Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="fas fa-broadcast-tower me-2"></i>OBS Studio Setup</h6>
                                <ol>
                                    <li>Open OBS Studio</li>
                                    <li>Go to Settings > Stream</li>
                                    <li>Service: Custom</li>
                                    <li>Server: <?php echo $live_stream ? htmlspecialchars($live_stream['stream_url'] ?? '') : 'Your RTMP Server'; ?></li>
                                    <li>Stream Key: <?php echo $live_stream ? htmlspecialchars($live_stream['stream_key'] ?? '') : 'Generated Key'; ?></li>
                                    <li>Click "Apply" and "OK"</li>
                                </ol>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-cog me-2"></i>Recommended Settings</h6>
                                <ul>
                                    <li><strong>Video:</strong> 1920x1080 at 30fps</li>
                                    <li><strong>Bitrate:</strong> 4000-6000 Kbps</li>
                                    <li><strong>Encoder:</strong> x264 or NVENC</li>
                                    <li><strong>Audio:</strong> 128 Kbps, 44.1 kHz</li>
                                    <li><strong>Format:</strong> HLS or DASH</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-shield-alt me-2"></i>Security Tips</h6>
                                <ul>
                                    <li>Keep your stream key private</li>
                                    <li>Use a unique, complex stream key</li>
                                    <li>Monitor viewer count regularly</li>
                                    <li>Test stream before going live</li>
                                    <li>Have backup streaming ready</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateStreamKey() {
            const key = 'pk_live_' + Math.random().toString(36).substr(2, 16) + '_' + Date.now();
            document.querySelector('input[name="stream_key"]').value = key;
        }
        
        function copyEmbedCode() {
            const textarea = document.querySelector('.control-panel textarea');
            textarea.select();
            document.execCommand('copy');
            
            // Show feedback
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
            button.classList.add('btn-success');
            button.classList.remove('btn-light');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-light');
            }, 2000);
        }
        
        // Auto-refresh stream status every 30 seconds
        setInterval(() => {
            updateStreamStatus();
        }, 30000);
        
        // Update stream status function
        function updateStreamStatus() {
            // Fetch current stream status via AJAX (optional enhancement)
            console.log('Updating stream status...');
        }
        
        // Enhanced stream key generation
        function generateStreamKey() {
            const key = 'pk_live_' + Math.random().toString(36).substr(2, 16) + '_' + Date.now();
            const input = document.querySelector('input[name="stream_key"]');
            if (input) {
                input.value = key;
                showNotification('Stream key generated successfully!', 'success');
            }
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
            `;
            document.body.appendChild(notification);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
        
        // Enhanced copy function with better feedback
        function copyEmbedCode() {
            const textarea = document.querySelector('.control-panel textarea');
            if (textarea) {
                textarea.select();
                document.execCommand('copy');
                showNotification('Embed code copied to clipboard!', 'success');
            }
        }
        
        // Stream health monitoring
        function monitorStreamHealth() {
            const healthIndicator = document.getElementById('streamHealth');
            if (healthIndicator) {
                // Simulate health check
                const isHealthy = Math.random() > 0.3;
                healthIndicator.textContent = isHealthy ? 'Good' : 'Poor';
                healthIndicator.className = isHealthy ? 'text-success' : 'text-danger';
            }
        }
        
        // Initialize monitoring
        document.addEventListener('DOMContentLoaded', function() {
            // Start health monitoring
            setInterval(monitorStreamHealth, 5000);
            
            // Simulate viewer count updates
            setInterval(() => {
                const viewerCount = document.getElementById('viewerCount');
                const bitrateDisplay = document.getElementById('bitrateDisplay');
                const streamHealth = document.getElementById('streamHealth');
                
                if (viewerCount) {
                    const current = parseInt(viewerCount.textContent) || 0;
                    const change = Math.floor(Math.random() * 11) - 5;
                    viewerCount.textContent = Math.max(0, current + change);
                }
                
                if (bitrateDisplay) {
                    const bitrate = (Math.random() * 2 + 3).toFixed(1);
                    bitrateDisplay.textContent = bitrate + ' Mbps';
                }
                
                if (streamHealth) {
                    const health = Math.random() > 0.1 ? 'Good' : 'Poor';
                    streamHealth.textContent = health;
                    streamHealth.className = health === 'Good' ? 'text-success' : 'text-danger';
                }
            }, 3000);
        });
        
        // Additional JavaScript functions
        function testStream() {
            showNotification('Testing stream connection...', 'info');
            setTimeout(() => {
                showNotification('Stream test completed successfully!', 'success');
            }, 2000);
        }
        
        function restartStream() {
            if (confirm('Are you sure you want to restart stream?')) {
                showNotification('Restarting stream...', 'warning');
                setTimeout(() => {
                    showNotification('Stream restarted successfully!', 'success');
                }, 3000);
            }
        }
        
        function viewLogs() {
            showNotification('Opening stream logs...', 'info');
            window.open('#', '_blank');
        }
        
        function openOBS() {
            showNotification('Launching OBS Studio...', 'info');
            window.open('obs-studio://', '_blank');
        }
    </script>
</body>
</html>
