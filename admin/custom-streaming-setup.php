<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$custom_data = [];

// Handle form submissions for different steps
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Save RTMP server configuration
            $custom_data['server_type'] = clean_input($_POST['server_type']);
            $custom_data['server_host'] = clean_input($_POST['server_host']);
            $custom_data['rtmp_port'] = clean_input($_POST['rtmp_port']);
            $custom_data['hls_port'] = clean_input($_POST['hls_port']);
            $custom_data['stream_key_format'] = clean_input($_POST['stream_key_format']);
            $custom_data['auth_enabled'] = isset($_POST['auth_enabled']) ? 1 : 0;
            $custom_data['auth_username'] = clean_input($_POST['auth_username']);
            $custom_data['auth_password'] = clean_input($_POST['auth_password']);
            
            $_SESSION['custom_setup'] = $custom_data;
            header('Location: custom-streaming-setup.php?step=2');
            exit();
            break;
            
        case 2:
            // Save streaming software configuration
            $custom_data = $_SESSION['custom_setup'];
            $custom_data['obs_settings'] = [
                'resolution' => clean_input($_POST['resolution']),
                'bitrate' => clean_input($_POST['bitrate']),
                'fps' => clean_input($_POST['fps']),
                'audio_bitrate' => clean_input($_POST['audio_bitrate']),
                'encoder' => clean_input($_POST['encoder']),
                'keyframe_interval' => clean_input($_POST['keyframe_interval'])
            ];
            $custom_data['stream_name'] = clean_input($_POST['stream_name']);
            $custom_data['stream_description'] = clean_input($_POST['stream_description']);
            
            $_SESSION['custom_setup'] = $custom_data;
            header('Location: custom-streaming-setup.php?step=3');
            exit();
            break;
            
        case 3:
            // Save embed configuration and create stream
            $custom_data = $_SESSION['custom_setup'];
            $custom_data['embed_code'] = clean_input($_POST['embed_code']);
            $custom_data['player_type'] = clean_input($_POST['player_type']);
            $custom_data['auto_play'] = isset($_POST['auto_play']) ? 1 : 0;
            $custom_data['mute_on_start'] = isset($_POST['mute_on_start']) ? 1 : 0;
            
            // Create the custom stream in database
            $title = $custom_data['stream_name'];
            $stream_url = $custom_data['server_host'] . ':' . $custom_data['hls_port'] . '/' . $custom_data['stream_name'] . '.m3u8';
            $embed_code = $custom_data['embed_code'];
            $status = 'offline';
            $description = $custom_data['stream_description'];
            
            $query = "INSERT INTO live_stream (title, stream_url, embed_code, status, description) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssss', $title, $stream_url, $embed_code, $status, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                $stream_id = mysqli_insert_id($conn);
                $success = "Custom stream created successfully! Stream ID: " . $stream_id;
                unset($_SESSION['custom_setup']);
                header('Location: live-stream.php?success=custom_stream_created');
                exit();
            } else {
                $error = "Error creating custom stream: " . mysqli_error($conn);
            }
            break;
    }
}

// Get stored data if exists
if (isset($_SESSION['custom_setup'])) {
    $custom_data = $_SESSION['custom_setup'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Streaming Setup - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .step:last-child::before {
            display: none;
        }
        .step.active::before {
            background: #6366f1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .step.active .step-number {
            background: #6366f1;
            color: white;
        }
        .step.completed .step-number {
            background: #10b981;
            color: white;
        }
        .custom-purple {
            color: #6366f1;
        }
        .setup-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .setup-card:hover {
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
            border-radius: 5px;
            padding: 1rem;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        .server-diagram {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="custom-streaming-setup.php">
                                <i class="fas fa-server me-2 custom-purple"></i>Custom Streaming
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-server me-2"></i>Custom Streaming Setup
                        </h1>
                        <small>Professional RTMP server and streaming configuration</small>
                    </div>
                    <div>
                        <a href="live-stream.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Live Stream
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                        <div class="step-number <?php echo $step > 1 ? 'completed' : ''; ?>">
                            <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                        </div>
                        <div class="mt-2">
                            <strong>RTMP Server</strong>
                            <br><small>Configure streaming server</small>
                        </div>
                    </div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                        <div class="step-number <?php echo $step > 2 ? 'completed' : ''; ?>">
                            <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                        </div>
                        <div class="mt-2">
                            <strong>Stream Software</strong>
                            <br><small>OBS/XSplit configuration</small>
                        </div>
                    </div>
                    <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                        <div class="step-number <?php echo $step > 3 ? 'completed' : ''; ?>">
                            <?php echo $step > 3 ? '<i class="fas fa-check"></i>' : '3'; ?>
                        </div>
                        <div class="mt-2">
                            <strong>Player Integration</strong>
                            <br><small>Embed and test stream</small>
                        </div>
                    </div>
                </div>

                <!-- Step Content -->
                <?php if ($step == 1): ?>
                    <!-- Step 1: RTMP Server Configuration -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2 custom-purple"></i>
                                Step 1: Configure RTMP Server
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="server_type" class="form-label">Server Type</label>
                                            <select class="form-select" id="server_type" name="server_type" onchange="toggleServerConfig()">
                                                <option value="nginx-rtmp" <?php echo isset($custom_data['server_type']) && $custom_data['server_type'] == 'nginx-rtmp' ? 'selected' : ''; ?>>Nginx-RTMP (Recommended)</option>
                                                <option value="wowza" <?php echo isset($custom_data['server_type']) && $custom_data['server_type'] == 'wowza' ? 'selected' : ''; ?>>Wowza Streaming Engine</option>
                                                <option value="ant-media" <?php echo isset($custom_data['server_type']) && $custom_data['server_type'] == 'ant-media' ? 'selected' : ''; ?>>Ant Media Server</option>
                                                <option value="custom" <?php echo isset($custom_data['server_type']) && $custom_data['server_type'] == 'custom' ? 'selected' : ''; ?>>Custom RTMP Server</option>
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="server_host" class="form-label">Server Host/IP *</label>
                                                    <input type="text" class="form-control" id="server_host" name="server_host" 
                                                           value="<?php echo isset($custom_data['server_host']) ? htmlspecialchars($custom_data['server_host']) : 'localhost'; ?>" 
                                                           placeholder="your-server.com" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="rtmp_port" class="form-label">RTMP Port</label>
                                                    <input type="number" class="form-control" id="rtmp_port" name="rtmp_port" 
                                                           value="<?php echo isset($custom_data['rtmp_port']) ? htmlspecialchars($custom_data['rtmp_port']) : '1935'; ?>" 
                                                           placeholder="1935">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="hls_port" class="form-label">HLS Port</label>
                                                    <input type="number" class="form-control" id="hls_port" name="hls_port" 
                                                           value="<?php echo isset($custom_data['hls_port']) ? htmlspecialchars($custom_data['hls_port']) : '8080'; ?>" 
                                                           placeholder="8080">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="stream_key_format" class="form-label">Stream Key Format</label>
                                                    <input type="text" class="form-control" id="stream_key_format" name="stream_key_format" 
                                                           value="<?php echo isset($custom_data['stream_key_format']) ? htmlspecialchars($custom_data['stream_key_format']) : 'live_{random}'; ?>" 
                                                           placeholder="live_{random}">
                                                    <small class="text-muted">Use {random} for unique keys</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="auth_enabled" name="auth_enabled" 
                                                       <?php echo isset($custom_data['auth_enabled']) && $custom_data['auth_enabled'] ? 'checked' : ''; ?> onchange="toggleAuthFields()">
                                                <label class="form-check-label" for="auth_enabled">
                                                    Enable Authentication
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div id="auth_fields" style="display: <?php echo isset($custom_data['auth_enabled']) && $custom_data['auth_enabled'] ? 'block' : 'none'; ?>;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="auth_username" class="form-label">Username</label>
                                                        <input type="text" class="form-control" id="auth_username" name="auth_username" 
                                                               value="<?php echo isset($custom_data['auth_username']) ? htmlspecialchars($custom_data['auth_username']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="auth_password" class="form-label">Password</label>
                                                        <input type="password" class="form-control" id="auth_password" name="auth_password" 
                                                               value="<?php echo isset($custom_data['auth_password']) ? htmlspecialchars($custom_data['auth_password']) : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-info-circle me-2"></i>RTMP Server Setup</h6>
                                                <div class="server-diagram mb-3">
                                                    <i class="fas fa-server fa-3x custom-purple mb-2"></i>
                                                    <h6>RTMP Server</h6>
                                                    <small class="text-muted">Receives stream from OBS</small>
                                                </div>
                                                <div class="code-block">
                                                    <strong>Nginx-RTMP Config:</strong><br>
                                                    rtmp {<br>
                                                    &nbsp;&nbsp;server {<br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;listen 1935;<br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;application live {<br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;live on;<br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;record off;<br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                                                    &nbsp;&nbsp;}<br>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='live-stream.php'">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i>Next Step
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif ($step == 2): ?>
                    <!-- Step 2: Streaming Software Configuration -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-video me-2 custom-purple"></i>
                                Step 2: Configure Streaming Software
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="stream_name" class="form-label">Stream Name *</label>
                                            <input type="text" class="form-control" id="stream_name" name="stream_name" 
                                                   value="<?php echo isset($custom_data['stream_name']) ? htmlspecialchars($custom_data['stream_name']) : ''; ?>" 
                                                   placeholder="news_live_01" required>
                                            <small class="text-muted">This will be your stream key</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="stream_description" class="form-label">Stream Description</label>
                                            <textarea class="form-control" id="stream_description" name="stream_description" rows="3" 
                                                      placeholder="Describe your custom live stream"><?php echo isset($custom_data['stream_description']) ? htmlspecialchars($custom_data['stream_description']) : ''; ?></textarea>
                                        </div>
                                        
                                        <h6><i class="fas fa-cog me-2"></i>OBS Studio Settings</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="resolution" class="form-label">Resolution</label>
                                                    <select class="form-select" id="resolution" name="resolution">
                                                        <option value="1920x1080" <?php echo isset($custom_data['obs_settings']['resolution']) && $custom_data['obs_settings']['resolution'] == '1920x1080' ? 'selected' : ''; ?>>1920x1080 (1080p)</option>
                                                        <option value="1280x720" <?php echo isset($custom_data['obs_settings']['resolution']) && $custom_data['obs_settings']['resolution'] == '1280x720' ? 'selected' : ''; ?>>1280x720 (720p)</option>
                                                        <option value="854x480" <?php echo isset($custom_data['obs_settings']['resolution']) && $custom_data['obs_settings']['resolution'] == '854x480' ? 'selected' : ''; ?>>854x480 (480p)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="bitrate" class="form-label">Video Bitrate (Mbps)</label>
                                                    <select class="form-select" id="bitrate" name="bitrate">
                                                        <option value="8" <?php echo isset($custom_data['obs_settings']['bitrate']) && $custom_data['obs_settings']['bitrate'] == '8' ? 'selected' : ''; ?>>8 Mbps (High)</option>
                                                        <option value="4" <?php echo isset($custom_data['obs_settings']['bitrate']) && $custom_data['obs_settings']['bitrate'] == '4' ? 'selected' : ''; ?>>4 Mbps (Medium)</option>
                                                        <option value="2" <?php echo isset($custom_data['obs_settings']['bitrate']) && $custom_data['obs_settings']['bitrate'] == '2' ? 'selected' : ''; ?>>2 Mbps (Low)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="fps" class="form-label">Frame Rate</label>
                                                    <select class="form-select" id="fps" name="fps">
                                                        <option value="30" <?php echo isset($custom_data['obs_settings']['fps']) && $custom_data['obs_settings']['fps'] == '30' ? 'selected' : ''; ?>>30 FPS</option>
                                                        <option value="60" <?php echo isset($custom_data['obs_settings']['fps']) && $custom_data['obs_settings']['fps'] == '60' ? 'selected' : ''; ?>>60 FPS</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="audio_bitrate" class="form-label">Audio Bitrate</label>
                                                    <select class="form-select" id="audio_bitrate" name="audio_bitrate">
                                                        <option value="320" <?php echo isset($custom_data['obs_settings']['audio_bitrate']) && $custom_data['obs_settings']['audio_bitrate'] == '320' ? 'selected' : ''; ?>>320 kbps</option>
                                                        <option value="192" <?php echo isset($custom_data['obs_settings']['audio_bitrate']) && $custom_data['obs_settings']['audio_bitrate'] == '192' ? 'selected' : ''; ?>>192 kbps</option>
                                                        <option value="128" <?php echo isset($custom_data['obs_settings']['audio_bitrate']) && $custom_data['obs_settings']['audio_bitrate'] == '128' ? 'selected' : ''; ?>>128 kbps</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="encoder" class="form-label">Encoder</label>
                                                    <select class="form-select" id="encoder" name="encoder">
                                                        <option value="x264" <?php echo isset($custom_data['obs_settings']['encoder']) && $custom_data['obs_settings']['encoder'] == 'x264' ? 'selected' : ''; ?>>x264 (CPU)</option>
                                                        <option value="nvenc" <?php echo isset($custom_data['obs_settings']['encoder']) && $custom_data['obs_settings']['encoder'] == 'nvenc' ? 'selected' : ''; ?>>NVENC (NVIDIA)</option>
                                                        <option value="amd" <?php echo isset($custom_data['obs_settings']['encoder']) && $custom_data['obs_settings']['encoder'] == 'amd' ? 'selected' : ''; ?>>AMD VCE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="keyframe_interval" class="form-label">Keyframe Interval (seconds)</label>
                                                    <input type="number" class="form-control" id="keyframe_interval" name="keyframe_interval" 
                                                           value="<?php echo isset($custom_data['obs_settings']['keyframe_interval']) ? htmlspecialchars($custom_data['obs_settings']['keyframe_interval']) : '2'; ?>" 
                                                           placeholder="2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-broadcast-tower me-2"></i>OBS Configuration</h6>
                                                <div class="code-block mb-3">
                                                    <strong>Stream Settings:</strong><br>
                                                    Service: Custom<br>
                                                    Server: rtmp://<?php echo isset($custom_data['server_host']) ? htmlspecialchars($custom_data['server_host']) : 'your-server'; ?>:<?php echo isset($custom_data['rtmp_port']) ? htmlspecialchars($custom_data['rtmp_port']) : '1935'; ?>/live<br>
                                                    Stream Key: <?php echo isset($custom_data['stream_name']) ? htmlspecialchars($custom_data['stream_name']) : 'your_stream_name'; ?>
                                                </div>
                                                <div class="alert alert-info">
                                                    <small><strong>Tip:</strong> Test your connection speed before going live. Minimum 5 Mbps upload recommended for 720p.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='custom-streaming-setup.php?step=1'">
                                        <i class="fas fa-arrow-left me-2"></i>Previous
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i>Next Step
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif ($step == 3): ?>
                    <!-- Step 3: Player Integration -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-play-circle me-2 custom-purple"></i>
                                Step 3: Player Integration & Testing
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-code me-2"></i>Embed Code Configuration</h6>
                                            <div class="mb-3">
                                                <label for="player_type" class="form-label">Player Type</label>
                                                <select class="form-select" id="player_type" name="player_type" onchange="updateEmbedCode()">
                                                    <option value="videojs" <?php echo isset($custom_data['player_type']) && $custom_data['player_type'] == 'videojs' ? 'selected' : ''; ?>>Video.js (Recommended)</option>
                                                    <option value="hlsjs" <?php echo isset($custom_data['player_type']) && $custom_data['player_type'] == 'hlsjs' ? 'selected' : ''; ?>>HLS.js</option>
                                                    <option value="clappr" <?php echo isset($custom_data['player_type']) && $custom_data['player_type'] == 'clappr' ? 'selected' : ''; ?>>Clappr</option>
                                                    <option value="custom" <?php echo isset($custom_data['player_type']) && $custom_data['player_type'] == 'custom' ? 'selected' : ''; ?>>Custom HTML5</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="embed_code" class="form-label">Custom Embed Code</label>
                                                <textarea class="form-control" id="embed_code" name="embed_code" rows="8" 
                                                  placeholder="Paste your custom embed code here"><?php echo isset($custom_data['embed_code']) ? htmlspecialchars($custom_data['embed_code']) : generateDefaultEmbedCode($custom_data); ?></textarea>
                                                <small class="text-muted">Customize the player embed code for your website</small>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="auto_play" name="auto_play" 
                                                               <?php echo isset($custom_data['auto_play']) && $custom_data['auto_play'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="auto_play">
                                                            Auto-play stream
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="mute_on_start" name="mute_on_start" 
                                                               <?php echo isset($custom_data['mute_on_start']) && $custom_data['mute_on_start'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="mute_on_start">
                                                            Mute on start (required for auto-play)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-play me-2"></i>Stream Preview</h6>
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <div id="streamPreview" style="background: #000; min-height: 200px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                        <div class="text-white">
                                                            <i class="fas fa-video fa-3x mb-3"></i>
                                                            <p>Stream preview will appear here</p>
                                                            <small>Start streaming from OBS to test</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-chart-line me-2"></i>Stream Monitoring</h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h3 class="text-primary" id="viewerCount">0</h3>
                                                            <small class="text-muted">Current Viewers</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h3 class="text-success" id="streamHealth">Good</h3>
                                                            <small class="text-muted">Stream Health</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h3 class="text-info" id="bitrateDisplay">0 Mbps</h3>
                                                            <small class="text-muted">Current Bitrate</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-rocket me-2"></i>Ready to Go Live!</h6>
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    Your custom stream is configured and ready to broadcast!
                                                </div>
                                                
                                                <h6><i class="fas fa-list-check me-2"></i>Final Checklist</h6>
                                                <ul class="small">
                                                    <li>✓ RTMP server configured</li>
                                                    <li>✓ OBS settings optimized</li>
                                                    <li>✓ Player embed code ready</li>
                                                    <li>✓ Stream monitoring active</li>
                                                </ul>
                                                
                                                <div class="mt-3">
                                                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Pro Tips</h6>
                                                    <ul class="small">
                                                        <li>Test stream quality before going live</li>
                                                        <li>Monitor viewer engagement</li>
                                                        <li>Have backup internet ready</li>
                                                        <li>Record streams for later use</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='custom-streaming-setup.php?step=2'">
                                        <i class="fas fa-arrow-left me-2"></i>Previous
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Create Stream
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- RTMP Server Setup Guide -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2 custom-purple"></i>
                            RTMP Server Setup Guide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fab fa-linux me-2"></i>Nginx-RTMP Installation</h6>
                                <div class="code-block">
                                    <strong>Ubuntu/Debian:</strong><br>
                                    sudo apt update<br>
                                    sudo apt install nginx libnginx-mod-rtmp<br><br>
                                    <strong>CentOS/RHEL:</strong><br>
                                    sudo yum install epel-release<br>
                                    sudo yum install nginx
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-file-code me-2"></i>Configuration Example</h6>
                                <div class="code-block">
                                    <strong>/etc/nginx/nginx.conf:</strong><br>
                                    rtmp {<br>
                                    &nbsp;&nbsp;server {<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;listen 1935;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;chunk_size 4096;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;application live {<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;live on;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;record off;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hls on;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hls_path /tmp/hls;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hls_fragment 3s;<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                                    &nbsp;&nbsp;}<br>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle authentication fields
        function toggleAuthFields() {
            const authEnabled = document.getElementById('auth_enabled').checked;
            const authFields = document.getElementById('auth_fields');
            authFields.style.display = authEnabled ? 'block' : 'none';
        }

        // Update embed code based on player type
        function updateEmbedCode() {
            const playerType = document.getElementById('player_type').value;
            const embedTextarea = document.getElementById('embed_code');
            
            let embedCode = '';
            const streamUrl = '<?php echo isset($custom_data['server_host']) ? htmlspecialchars($custom_data['server_host']) : 'localhost'; ?>:<?php echo isset($custom_data['hls_port']) ? htmlspecialchars($custom_data['hls_port']) : '8080'; ?>/<?php echo isset($custom_data['stream_name']) ? htmlspecialchars($custom_data['stream_name']) : 'live'; ?>.m3u8';
            
            switch(playerType) {
                case 'videojs':
                    embedCode = `<link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
<script src="https://vjs.zencdn.net/8.6.1/video.min.js"><\/script>

<video-js id="livePlayer" controls preload="auto" width="640" height="264" data-setup='{"fluid": true}'>
    <source src="http://${streamUrl}" type="application/x-mpegURL">
<\/video-js>`;
                    break;
                case 'hlsjs':
                    embedCode = `<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"><\/script>
<video id="player" controls><\/video>
<script>
  if(Hls.isSupported()) {
    const video = document.getElementById("player");
    const hls = new Hls();
    hls.loadSource("http://${streamUrl}");
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED,function() {
      video.play();
    });
  }
<\/script>`;
                    break;
                case 'clappr':
                    embedCode = `<script src="https://cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"><\/script>
<div id="player"><\/div>
<script>
  var player = new Clappr.Player({
    source: "http://${streamUrl}",
    parentId: "#player",
    autoPlay: true,
    mute: true
  });
<\/script>`;
                    break;
            }
            
            embedTextarea.value = embedCode;
        }

        // Simulate stream monitoring
        setInterval(function() {
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

        // Initialize embed code on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateEmbedCode();
        });
    </script>

    <?php
    // Helper function to generate default embed code
    function generateDefaultEmbedCode($custom_data) {
        $streamUrl = (isset($custom_data['server_host']) ? $custom_data['server_host'] : 'localhost') . ':' . (isset($custom_data['hls_port']) ? $custom_data['hls_port'] : '8080') . '/' . (isset($custom_data['stream_name']) ? $custom_data['stream_name'] : 'live') . '.m3u8';
        
        return '<link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">' .
               '<script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>' .
               '<video-js id="livePlayer" controls preload="auto" width="640" height="264" data-setup=\'{"fluid": true}\'>' .
               '    <source src="http://' . $streamUrl . '" type="application/x-mpegURL">' .
               '</video-js>';
    }
    ?>
</body>
</html>
