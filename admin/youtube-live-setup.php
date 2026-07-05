<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$youtube_data = [];

// Handle form submissions for different steps
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Save YouTube Live event details
            $youtube_data['event_title'] = clean_input($_POST['event_title']);
            $youtube_data['event_description'] = clean_input($_POST['event_description']);
            $youtube_data['privacy_status'] = clean_input($_POST['privacy_status']);
            $youtube_data['scheduled_time'] = clean_input($_POST['scheduled_time']);
            $youtube_data['thumbnail'] = clean_input($_POST['thumbnail']);
            
            $_SESSION['youtube_setup'] = $youtube_data;
            header('Location: youtube-live-setup.php?step=2');
            exit();
            break;
            
        case 2:
            // Save streaming configuration
            $youtube_data = $_SESSION['youtube_setup'];
            $youtube_data['stream_key'] = clean_input($_POST['stream_key']);
            $youtube_data['stream_url'] = clean_input($_POST['stream_url']);
            $youtube_data['server_url'] = clean_input($_POST['server_url']);
            $youtube_data['resolution'] = clean_input($_POST['resolution']);
            $youtube_data['bitrate'] = clean_input($_POST['bitrate']);
            $youtube_data['fps'] = clean_input($_POST['fps']);
            
            $_SESSION['youtube_setup'] = $youtube_data;
            header('Location: youtube-live-setup.php?step=3');
            exit();
            break;
            
        case 3:
            // Save OBS configuration and create stream
            $youtube_data = $_SESSION['youtube_setup'];
            $youtube_data['obs_settings'] = $_POST;
            
            // Create the live stream in database
            $title = $youtube_data['event_title'];
            $stream_url = $youtube_data['stream_url'];
            $description = $youtube_data['event_description'];
            $status = 'scheduled';
            $schedule_time = $youtube_data['scheduled_time'];
            
            $query = "INSERT INTO live_stream (title, stream_url, embed_code, status, description, schedule_time) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssss', $title, $stream_url, $embed_code, $status, $description, $schedule_time);
            
            if (mysqli_stmt_execute($stmt)) {
                $stream_id = mysqli_insert_id($conn);
                $success = "YouTube Live stream created successfully! Stream ID: " . $stream_id;
                unset($_SESSION['youtube_setup']);
                header('Location: live-stream.php?success=youtube_live_created');
                exit();
            } else {
                $error = "Error creating YouTube Live stream: " . mysqli_error($conn);
            }
            break;
    }
}

// Get stored data if exists
if (isset($_SESSION['youtube_setup'])) {
    $youtube_data = $_SESSION['youtube_setup'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Live Setup - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
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
            background: #ff0000;
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
            background: #ff0000;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .youtube-red {
            color: #ff0000;
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
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
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
                            <a class="nav-link active" href="youtube-live-setup.php">
                                <i class="fab fa-youtube me-2 youtube-red"></i>YouTube Live Setup
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
                            <i class="fab fa-youtube me-2"></i>YouTube Live Setup
                        </h1>
                        <small>Step-by-step YouTube Live streaming configuration</small>
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
                            <strong>YouTube Event</strong>
                            <br><small>Create YouTube Live event</small>
                        </div>
                    </div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                        <div class="step-number <?php echo $step > 2 ? 'completed' : ''; ?>">
                            <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                        </div>
                        <div class="mt-2">
                            <strong>Stream Settings</strong>
                            <br><small>Configure streaming parameters</small>
                        </div>
                    </div>
                    <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                        <div class="step-number <?php echo $step > 3 ? 'completed' : ''; ?>">
                            <?php echo $step > 3 ? '<i class="fas fa-check"></i>' : '3'; ?>
                        </div>
                        <div class="mt-2">
                            <strong>OBS Setup</strong>
                            <br><small>Configure broadcasting software</small>
                        </div>
                    </div>
                </div>

                <!-- Step Content -->
                <?php if ($step == 1): ?>
                    <!-- Step 1: YouTube Event Creation -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fab fa-youtube me-2 youtube-red"></i>
                                Step 1: Create YouTube Live Event
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="event_title" class="form-label">Event Title *</label>
                                            <input type="text" class="form-control" id="event_title" name="event_title" 
                                                   value="<?php echo isset($youtube_data['event_title']) ? htmlspecialchars($youtube_data['event_title']) : ''; ?>" 
                                                   placeholder="Enter your live stream title" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="event_description" class="form-label">Event Description</label>
                                            <textarea class="form-control" id="event_description" name="event_description" rows="4" 
                                                      placeholder="Describe your live stream content"><?php echo isset($youtube_data['event_description']) ? htmlspecialchars($youtube_data['event_description']) : ''; ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="privacy_status" class="form-label">Privacy Status</label>
                                                    <select class="form-select" id="privacy_status" name="privacy_status">
                                                        <option value="public" <?php echo isset($youtube_data['privacy_status']) && $youtube_data['privacy_status'] == 'public' ? 'selected' : ''; ?>>Public</option>
                                                        <option value="unlisted" <?php echo isset($youtube_data['privacy_status']) && $youtube_data['privacy_status'] == 'unlisted' ? 'selected' : ''; ?>>Unlisted</option>
                                                        <option value="private" <?php echo isset($youtube_data['privacy_status']) && $youtube_data['privacy_status'] == 'private' ? 'selected' : ''; ?>>Private</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                                    <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time" 
                                                           value="<?php echo isset($youtube_data['scheduled_time']) ? htmlspecialchars($youtube_data['scheduled_time']) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-info-circle me-2"></i>YouTube Studio Instructions</h6>
                                                <ol class="small">
                                                    <li>Go to <a href="https://studio.youtube.com" target="_blank">YouTube Studio</a></li>
                                                    <li>Click "Create" → "Go Live"</li>
                                                    <li>Enter event details above</li>
                                                    <li>Copy stream key from next step</li>
                                                    <li>Set up thumbnail if desired</li>
                                                </ol>
                                                <div class="alert alert-info mt-3">
                                                    <small><strong>Note:</strong> You'll need to complete this setup in YouTube Studio after configuring the stream settings.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='live-stream.php'">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-arrow-right me-2"></i>Next Step
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif ($step == 2): ?>
                    <!-- Step 2: Stream Configuration -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cog me-2 youtube-red"></i>
                                Step 2: Configure Stream Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="stream_key" class="form-label">YouTube Stream Key *</label>
                                            <input type="text" class="form-control" id="stream_key" name="stream_key" 
                                                   value="<?php echo isset($youtube_data['stream_key']) ? htmlspecialchars($youtube_data['stream_key']) : ''; ?>" 
                                                   placeholder="xxxx-xxxx-xxxx-xxxx" required>
                                            <small class="text-muted">Get this from YouTube Studio → Live → Stream Key</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="stream_url" class="form-label">YouTube Video URL *</label>
                                            <input type="url" class="form-control" id="stream_url" name="stream_url" 
                                                   value="<?php echo isset($youtube_data['stream_url']) ? htmlspecialchars($youtube_data['stream_url']) : ''; ?>" 
                                                   placeholder="https://www.youtube.com/watch?v=xxxxx" required>
                                            <small class="text-muted">Your YouTube live stream URL</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="server_url" class="form-label">RTMP Server URL</label>
                                            <input type="url" class="form-control" id="server_url" name="server_url" 
                                                   value="<?php echo isset($youtube_data['server_url']) ? htmlspecialchars($youtube_data['server_url']) : 'rtmp://a.rtmp.youtube.com/live2'; ?>" 
                                                   readonly>
                                            <small class="text-muted">YouTube's RTMP server (usually fixed)</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="resolution" class="form-label">Resolution</label>
                                                    <select class="form-select" id="resolution" name="resolution">
                                                        <option value="1920x1080" <?php echo isset($youtube_data['resolution']) && $youtube_data['resolution'] == '1920x1080' ? 'selected' : ''; ?>>1920x1080 (1080p)</option>
                                                        <option value="1280x720" <?php echo isset($youtube_data['resolution']) && $youtube_data['resolution'] == '1280x720' ? 'selected' : ''; ?>>1280x720 (720p)</option>
                                                        <option value="854x480" <?php echo isset($youtube_data['resolution']) && $youtube_data['resolution'] == '854x480' ? 'selected' : ''; ?>>854x480 (480p)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="bitrate" class="form-label">Bitrate (Mbps)</label>
                                                    <select class="form-select" id="bitrate" name="bitrate">
                                                        <option value="8" <?php echo isset($youtube_data['bitrate']) && $youtube_data['bitrate'] == '8' ? 'selected' : ''; ?>>8 Mbps (High)</option>
                                                        <option value="4" <?php echo isset($youtube_data['bitrate']) && $youtube_data['bitrate'] == '4' ? 'selected' : ''; ?>>4 Mbps (Medium)</option>
                                                        <option value="2" <?php echo isset($youtube_data['bitrate']) && $youtube_data['bitrate'] == '2' ? 'selected' : ''; ?>>2 Mbps (Low)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="fps" class="form-label">Frame Rate</label>
                                                    <select class="form-select" id="fps" name="fps">
                                                        <option value="30" <?php echo isset($youtube_data['fps']) && $youtube_data['fps'] == '30' ? 'selected' : ''; ?>>30 FPS</option>
                                                        <option value="60" <?php echo isset($youtube_data['fps']) && $youtube_data['fps'] == '60' ? 'selected' : ''; ?>>60 FPS</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-key me-2"></i>How to Get Stream Key</h6>
                                                <ol class="small">
                                                    <li>In YouTube Studio, click "Go Live"</li>
                                                    <li>Fill in basic stream info</li>
                                                    <li>Click "Create Stream"</li>
                                                    <li>Copy the "Stream Key"</li>
                                                    <li>Paste it in the field above</li>
                                                </ol>
                                                <div class="code-block mt-3">
                                                    <strong>Stream Key Format:</strong><br>
                                                    xxxx-xxxx-xxxx-xxxx
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='youtube-live-setup.php?step=1'">
                                        <i class="fas fa-arrow-left me-2"></i>Previous
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-arrow-right me-2"></i>Next Step
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif ($step == 3): ?>
                    <!-- Step 3: OBS Setup -->
                    <div class="setup-card card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-video me-2 youtube-red"></i>
                                Step 3: OBS Studio Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <h6><i class="fas fa-download me-2"></i>OBS Studio Setup</h6>
                                            <p>Download and install OBS Studio if you haven't already:</p>
                                            <a href="https://obsproject.com" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-2"></i>Download OBS Studio
                                            </a>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-cog me-2"></i>OBS Stream Settings</h6>
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <ol>
                                                        <li>Open OBS Studio</li>
                                                        <li>Go to <strong>Settings → Stream</strong></li>
                                                        <li>Service: <strong>YouTube</strong></li>
                                                        <li>Server: <strong>YouTube - RTMP</strong></li>
                                                        <li>Stream Key: <strong><?php echo isset($youtube_data['stream_key']) ? htmlspecialchars($youtube_data['stream_key']) : '[Enter from Step 2]'; ?></strong></li>
                                                        <li>Click <strong>Apply</strong> → <strong>OK</strong></li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-tv me-2"></i>Output Settings</h6>
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <ol>
                                                        <li>Go to <strong>Settings → Output</strong></li>
                                                        <li>Output Mode: <strong>Advanced</strong></li>
                                                        <li>Streaming Tab:</li>
                                                        <ul>
                                                            <li>Encoder: <strong>x264</strong> or <strong>NVENC</strong></li>
                                                            <li>Rate Control: <strong>CBR</strong></li>
                                                            <li>Bitrate: <strong><?php echo isset($youtube_data['bitrate']) ? $youtube_data['bitrate'] : '4'; ?>000</strong> kbps</li>
                                                            <li>Keyframe Interval: <strong>2</strong></li>
                                                            <li>Preset: <strong>Veryfast</strong></li>
                                                        </ul>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6><i class="fas fa-play-circle me-2"></i>Ready to Stream?</h6>
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Your YouTube Live stream is now configured! Click "Create Stream" to save it to your system.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><i class="fas fa-rocket me-2"></i>Quick Start Guide</h6>
                                                <ol class="small">
                                                    <li>Configure OBS as shown</li>
                                                    <li>Add sources (camera, mic, etc.)</li>
                                                    <li>Test your setup</li>
                                                    <li>Click "Start Streaming" in OBS</li>
                                                    <li>Monitor on YouTube Studio</li>
                                                    <li>Set status to "Online" when ready</li>
                                                </ol>
                                                
                                                <div class="mt-3">
                                                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Pro Tips</h6>
                                                    <ul class="small">
                                                        <li>Test audio levels first</li>
                                                        <li>Use good lighting</li>
                                                        <li>Stable internet required</li>
                                                        <li>Monitor stream health</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='youtube-live-setup.php?step=2'">
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

                <!-- Help Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>
                            YouTube Live Help Resources
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="feature-icon bg-danger text-white me-3">
                                        <i class="fab fa-youtube"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">YouTube Help Center</h6>
                                        <small class="text-muted">Official YouTube Live documentation</small>
                                    </div>
                                </div>
                                <a href="https://support.google.com/youtube/topic/9257498" target="_blank" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-external-link-alt me-2"></i>Visit Help Center
                                </a>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="feature-icon bg-primary text-white me-3">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">OBS Studio Guide</h6>
                                        <small class="text-muted">Complete OBS setup tutorial</small>
                                    </div>
                                </div>
                                <a href="https://obsproject.com/help" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-2"></i>OBS Documentation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill current datetime for scheduled time
        document.addEventListener('DOMContentLoaded', function() {
            const scheduledTimeInput = document.getElementById('scheduled_time');
            if (scheduledTimeInput && !scheduledTimeInput.value) {
                const now = new Date();
                now.setHours(now.getHours() + 1); // Schedule for 1 hour from now
                const datetime = now.toISOString().slice(0, 16);
                scheduledTimeInput.value = datetime;
            }
        });

        // Copy stream key to clipboard
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.select();
                document.execCommand('copy');
                
                // Show feedback
                const originalText = element.value;
                element.value = 'Copied!';
                setTimeout(() => {
                    element.value = originalText;
                }, 2000);
            }
        }
    </script>
</body>
</html>
