<?php
require_once '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle broadcast control actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'start_broadcast':
            $channel_id = $_POST['channel_id'] ?? 0;
            $stream_key = $_POST['stream_key'] ?? '';
            $stream_title = $_POST['stream_title'] ?? '';
            $stream_description = $_POST['stream_description'] ?? '';
            
            // Update channel status to live
            $update_query = "UPDATE channels SET status = 'live', stream_key = ?, stream_title = ?, stream_description = ?, start_time = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $stream_key, $stream_title, $stream_description, $channel_id);
            mysqli_stmt_execute($stmt);
            
            // Log broadcast start
            $log_query = "INSERT INTO broadcast_logs (channel_id, action, admin_id, timestamp) VALUES (?, 'start', ?, NOW())";
            $stmt = mysqli_prepare($conn, $log_query);
            mysqli_stmt_bind_param($stmt, "ii", $channel_id, $_SESSION['admin_id']);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "Broadcast started successfully!";
            break;
            
        case 'stop_broadcast':
            $channel_id = $_POST['channel_id'] ?? 0;
            
            // Update channel status to offline
            $update_query = "UPDATE channels SET status = 'offline', end_time = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $channel_id);
            mysqli_stmt_execute($stmt);
            
            // Log broadcast stop
            $log_query = "INSERT INTO broadcast_logs (channel_id, action, admin_id, timestamp) VALUES (?, 'stop', ?, NOW())";
            $stmt = mysqli_prepare($conn, $log_query);
            mysqli_stmt_bind_param($stmt, "ii", $channel_id, $_SESSION['admin_id']);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "Broadcast stopped successfully!";
            break;
            
        case 'update_settings':
            $channel_id = $_POST['channel_id'] ?? 0;
            $max_viewers = $_POST['max_viewers'] ?? 1000;
            $quality = $_POST['quality'] ?? '720p';
            $allow_chat = isset($_POST['allow_chat']) ? 1 : 0;
            $record_stream = isset($_POST['record_stream']) ? 1 : 0;
            
            // Update channel settings
            $update_query = "UPDATE channels SET max_viewers = ?, quality = ?, allow_chat = ?, record_stream = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssiii", $max_viewers, $quality, $allow_chat, $record_stream, $channel_id);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "Channel settings updated successfully!";
            break;
            
        case 'ban_user':
            $user_id = $_POST['user_id'] ?? 0;
            $channel_id = $_POST['channel_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            
            // Ban user from channel
            $ban_query = "INSERT INTO channel_bans (channel_id, user_id, admin_id, reason, ban_time) VALUES (?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $ban_query);
            mysqli_stmt_bind_param($stmt, "iiis", $channel_id, $user_id, $_SESSION['admin_id'], $reason);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "User banned successfully!";
            break;
    }
    
    header('Location: broadcast_controls.php');
    exit;
}

// Get all channels with their current status
$channels_query = "SELECT c.*, 
                        (SELECT COUNT(*) FROM live_chat WHERE channel_id = c.id AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as recent_chats,
                        (SELECT COUNT(*) FROM channel_bans WHERE channel_id = c.id AND ban_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as recent_bans
                 FROM channels c 
                 ORDER BY c.name ASC";
$channels_result = mysqli_query($conn, $channels_query);

// Get recent broadcast logs
$logs_query = "SELECT bl.*, c.name as channel_name, a.username as admin_name 
               FROM broadcast_logs bl 
               JOIN channels c ON bl.channel_id = c.id 
               JOIN admins a ON bl.admin_id = a.id 
               ORDER BY bl.timestamp DESC 
               LIMIT 20";
$logs_result = mysqli_query($conn, $logs_query);

// Get active broadcasts
$active_query = "SELECT * FROM channels WHERE status = 'live' ORDER BY start_time DESC";
$active_result = mysqli_query($conn, $active_query);

// Get system stats
$total_channels = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM channels"));
$active_broadcasts = mysqli_num_rows($active_result);
$total_viewers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(viewer_count) as total FROM channels WHERE status = 'live'"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Controls - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .channel-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .channel-card:hover {
            transform: translateY(-5px);
        }
        .live-indicator {
            background: #ff0000;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .control-btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
        }
        .broadcast-stats {
            background: rgba(0,0,0,0.05);
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-broadcast-tower me-3"></i>Broadcast Controls
                    </h1>
                    <p class="mb-0 opacity-75">Manage live broadcasts and streaming settings</p>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-light btn-lg" onclick="location.reload()">
                        <i class="fas fa-sync me-2"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0"><?php echo $total_channels; ?></h3>
                            <small>Total Channels</small>
                        </div>
                        <i class="fas fa-tv fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0"><?php echo $active_broadcasts; ?></h3>
                            <small>Live Now</small>
                        </div>
                        <i class="fas fa-satellite-dish fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0"><?php echo number_format($total_viewers); ?></h3>
                            <small>Total Viewers</small>
                        </div>
                        <i class="fas fa-eye fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">24/7</h3>
                            <small>Uptime</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Broadcasts -->
        <?php if (mysqli_num_rows($active_result) > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-broadcast-tower me-2"></i>Active Broadcasts
                                <span class="badge bg-light text-danger ms-2"><?php echo mysqli_num_rows($active_result); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php while ($channel = mysqli_fetch_assoc($active_result)): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="broadcast-stats">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-circle text-danger me-2"></i>
                                                    <?php echo htmlspecialchars($channel['name']); ?>
                                                </h6>
                                                <span class="live-indicator">LIVE</span>
                                            </div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted">Viewers</small>
                                                    <strong><?php echo number_format($channel['viewer_count']); ?></strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Duration</small>
                                                    <strong><?php echo date('H:i', strtotime($channel['start_time'])); ?></strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Quality</small>
                                                    <strong><?php echo $channel['quality']; ?></strong>
                                                </div>
                                            </div>
                                            <form method="POST" class="mt-3">
                                                <input type="hidden" name="action" value="stop_broadcast">
                                                <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Stop this broadcast?')">
                                                    <i class="fas fa-stop me-2"></i>Stop Broadcast
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Channel Controls -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-sliders-h me-2"></i>Channel Controls
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($channel = mysqli_fetch_assoc($channels_result)): ?>
                                <div class="col-lg-6">
                                    <div class="card channel-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($channel['name']); ?></h6>
                                                <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : ($channel['status'] === 'scheduled' ? 'warning' : 'secondary'); ?>">
                                                    <?php echo strtoupper($channel['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="row text-center mb-3">
                                                <div class="col-4">
                                                    <small class="text-muted">Viewers</small>
                                                    <strong><?php echo number_format($channel['viewer_count']); ?></strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Chats</small>
                                                    <strong><?php echo $channel['recent_chats']; ?></strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Bans</small>
                                                    <strong><?php echo $channel['recent_bans']; ?></strong>
                                                </div>
                                            </div>
                                            
                                            <?php if ($channel['status'] !== 'live'): ?>
                                                <!-- Start Broadcast Form -->
                                                <form method="POST" class="mb-2">
                                                    <input type="hidden" name="action" value="start_broadcast">
                                                    <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <input type="text" name="stream_title" class="form-control form-control-sm" placeholder="Stream Title">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <input type="text" name="stream_key" class="form-control form-control-sm" placeholder="Stream Key">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <textarea name="stream_description" class="form-control form-control-sm" rows="2" placeholder="Stream Description"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success btn-sm control-btn w-100">
                                                        <i class="fas fa-play me-2"></i>Start Broadcast
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Stop Broadcast Form -->
                                                <form method="POST" class="mb-2">
                                                    <input type="hidden" name="action" value="stop_broadcast">
                                                    <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm control-btn w-100" onclick="return confirm('Stop this broadcast?')">
                                                        <i class="fas fa-stop me-2"></i>Stop Broadcast
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <!-- Settings Form -->
                                            <form method="POST">
                                                <input type="hidden" name="action" value="update_settings">
                                                <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <input type="number" name="max_viewers" class="form-control form-control-sm" value="<?php echo $channel['max_viewers'] ?? 1000; ?>" placeholder="Max Viewers">
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <select name="quality" class="form-select form-select-sm">
                                                            <option value="480p" <?php echo ($channel['quality'] ?? '720p') === '480p' ? 'selected' : ''; ?>>480p</option>
                                                            <option value="720p" <?php echo ($channel['quality'] ?? '720p') === '720p' ? 'selected' : ''; ?>>720p</option>
                                                            <option value="1080p" <?php echo ($channel['quality'] ?? '720p') === '1080p' ? 'selected' : ''; ?>>1080p</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                                            <i class="fas fa-cog me-2"></i>Update
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="allow_chat" value="1" <?php echo ($channel['allow_chat'] ?? 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">Allow Chat</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="record_stream" value="1" <?php echo ($channel['record_stream'] ?? 0) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">Record Stream</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Channel</th>
                                        <th>Action</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = mysqli_fetch_assoc($logs_result)): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y H:i', strtotime($log['timestamp'])); ?></td>
                                            <td><?php echo htmlspecialchars($log['channel_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $log['action'] === 'start' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($log['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
        
        // Real-time viewer count simulation
        document.addEventListener('DOMContentLoaded', function() {
            const viewerElements = document.querySelectorAll('[data-viewers]');
            viewerElements.forEach(element => {
                setInterval(() => {
                    const current = parseInt(element.textContent.replace(/,/g, ''));
                    const change = Math.floor(Math.random() * 10) - 5;
                    element.textContent = (current + change).toLocaleString();
                }, 5000);
            });
        });
    </script>

<?php include '../includes/admin_footer.php'; ?>
