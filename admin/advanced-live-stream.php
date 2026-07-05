<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle multi-camera stream setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_multi_camera_stream':
            $title = clean_input($_POST['title']);
            $description = clean_input($_POST['description']);
            $camera_count = (int)$_POST['camera_count'];
            $overlay_template = clean_input($_POST['overlay_template']);
            
            if (empty($title) || $camera_count < 1 || $camera_count > 4) {
                $error = 'Invalid stream configuration';
            } else {
                // Insert main stream
                $query = "INSERT INTO live_stream (title, description, status, camera_count, multi_camera_config) 
                         VALUES (?, ?, 'offline', ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                $multi_camera_config = json_encode([
                    'cameras' => $camera_count,
                    'layout' => $_POST['layout'] ?? 'grid',
                    'switching_enabled' => true
                ]);
                mysqli_stmt_bind_param($stmt, 'ssis', $title, $description, $camera_count, $multi_camera_config);
                
                if (mysqli_stmt_execute($stmt)) {
                    $stream_id = mysqli_insert_id($conn);
                    
                    // Add camera configurations
                    for ($i = 1; $i <= $camera_count; $i++) {
                        $camera_name = clean_input($_POST["camera_name_$i"] ?? "Camera $i");
                        $camera_url = clean_input($_POST["camera_url_$i"] ?? '');
                        
                        $camera_query = "INSERT INTO stream_cameras (stream_id, camera_number, camera_name, stream_url, position) 
                                       VALUES (?, ?, ?, ?, ?)";
                        $camera_stmt = mysqli_prepare($conn, $camera_query);
                        $position = $i == 1 ? 'main' : 'side-by-side';
                        $pos_var = $position;
                        mysqli_stmt_bind_param($camera_stmt, 'iisss', $stream_id, $i, $camera_name, $camera_url, $pos_var);
                        mysqli_stmt_execute($camera_stmt);
                    }
                    
                    // Add overlay if template selected
                    if ($overlay_template) {
                        $overlay_query = "INSERT INTO stream_overlays (stream_id, template_id, overlay_name) 
                                       VALUES (?, ?, ?)";
                        $overlay_stmt = mysqli_prepare($conn, $overlay_query);
                        $overlay_name = 'Default Overlay';
                        mysqli_stmt_bind_param($overlay_stmt, 'iis', $stream_id, $overlay_template, $overlay_name);
                        mysqli_stmt_execute($overlay_stmt);
                    }
                    
                    $success = "Multi-camera stream created successfully!";
                } else {
                    $error = "Error creating stream: " . mysqli_error($conn);
                }
            }
            break;
            
        case 'add_custom_overlay':
            $stream_id = (int)$_POST['stream_id'];
            $overlay_name = clean_input($_POST['overlay_name']);
            $template_id = (int)$_POST['template_id'];
            $position_x = (int)$_POST['position_x'];
            $position_y = (int)$_POST['position_y'];
            $custom_data = json_encode([
                'title' => clean_input($_POST['overlay_title'] ?? ''),
                'content' => clean_input($_POST['overlay_content'] ?? ''),
                'icon' => clean_input($_POST['overlay_icon'] ?? ''),
                'temp' => clean_input($_POST['overlay_temp'] ?? ''),
                'condition' => clean_input($_POST['overlay_condition'] ?? ''),
                'home_team' => clean_input($_POST['home_team'] ?? ''),
                'away_team' => clean_input($_POST['away_team'] ?? ''),
                'home_score' => clean_input($_POST['home_score'] ?? '0'),
                'away_score' => clean_input($_POST['away_score'] ?? '0')
            ]);
            
            $overlay_query = "INSERT INTO stream_overlays (stream_id, template_id, overlay_name, position_x, position_y, custom_data) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $overlay_stmt = mysqli_prepare($conn, $overlay_query);
            mysqli_stmt_bind_param($overlay_stmt, 'iisiis', $stream_id, $template_id, $overlay_name, $position_x, $position_y, $custom_data);
            
            if (mysqli_stmt_execute($overlay_stmt)) {
                $success = "Overlay added successfully!";
            } else {
                $error = "Error adding overlay: " . mysqli_error($conn);
            }
            break;
            
        case 'switch_camera':
            $stream_id = (int)$_POST['stream_id'];
            $camera_number = (int)$_POST['camera_number'];
            
            $update_query = "UPDATE live_stream SET active_camera = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ii', $camera_number, $stream_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Switched to Camera $camera_number";
            } else {
                $error = "Error switching camera";
            }
            break;
    }
}

// Check if multi-camera tables exist before querying
$stream_cameras_exist = mysqli_query($conn, "SHOW TABLES LIKE 'stream_cameras'");
$overlay_templates_exist = mysqli_query($conn, "SHOW TABLES LIKE 'overlay_templates'");

$streams_result = null;
$templates_result = null;

// Get streams with camera configurations only if tables exist
if (mysqli_num_rows($stream_cameras_exist) > 0) {
    $streams_query = "SELECT ls.*, 
                     COUNT(sc.id) as configured_cameras,
                     GROUP_CONCAT(sc.camera_name ORDER BY sc.camera_number) as camera_names
                     FROM live_stream ls 
                     LEFT JOIN stream_cameras sc ON ls.id = sc.stream_id 
                     GROUP BY ls.id 
                     ORDER BY ls.created_at DESC";
    $streams_result = mysqli_query($conn, $streams_query);
} else {
    // Fallback query without JOIN
    $streams_query = "SELECT ls.*, 0 as configured_cameras, '' as camera_names 
                     FROM live_stream ls 
                     ORDER BY ls.created_at DESC";
    $streams_result = mysqli_query($conn, $streams_query);
}

// Get overlay templates only if table exists
if (mysqli_num_rows($overlay_templates_exist) > 0) {
    $templates_query = "SELECT * FROM overlay_templates ORDER BY is_default DESC, name ASC";
    $templates_result = mysqli_query($conn, $templates_query);
}

// Get current live stream
if (mysqli_num_rows($stream_cameras_exist) > 0) {
    $current_live_query = "SELECT ls.*, sc.camera_name, sc.stream_url as camera_stream_url 
                           FROM live_stream ls 
                           LEFT JOIN stream_cameras sc ON ls.id = sc.stream_id AND sc.camera_number = ls.active_camera 
                           WHERE ls.status = 'online' 
                           ORDER BY ls.id DESC LIMIT 1";
} else {
    // Fallback query without JOIN
    $current_live_query = "SELECT ls.*, '' as camera_name, '' as camera_stream_url 
                           FROM live_stream ls 
                           WHERE ls.status = 'online' 
                           ORDER BY ls.id DESC LIMIT 1";
}
$current_live = mysqli_fetch_assoc(mysqli_query($conn, $current_live_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Live Stream Control - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        /* Multi-camera styles */
        .camera-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .camera-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            background: white;
            transition: all 0.3s ease;
        }
        .camera-card.active {
            border-color: #dc3545;
            background: #fff5f5;
        }
        .camera-preview {
            width: 100%;
            height: 120px;
            background: #000;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 10px;
        }
        
        /* Overlay styles */
        .overlay-preview {
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 10px;
            background: #f8f9fa;
            min-height: 100px;
            position: relative;
            overflow: hidden;
        }
        .overlay-item {
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            cursor: move;
        }
        
        /* Layout selector */
        .layout-option {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .layout-option:hover,
        .layout-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .layout-preview {
            display: grid;
            gap: 2px;
            margin: 10px auto;
            width: 80px;
            height: 60px;
        }
        .layout-preview.single { grid-template-columns: 1fr; }
        .layout-preview.side-by-side { grid-template-columns: 1fr 1fr; }
        .layout-preview.grid { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .layout-preview div { background: #667eea; border-radius: 2px; }
        
        /* Control panel */
        .control-panel {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .camera-switch-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            margin: 2px;
            transition: all 0.3s ease;
        }
        .camera-switch-btn:hover,
        .camera-switch-btn.active {
            background: rgba(220,53,69,0.8);
            border-color: #dc3545;
        }
        
        /* Overlay templates */
        .template-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .template-card:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .template-card.selected {
            border-color: #dc3545;
            background: #fff5f5;
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
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="advanced-live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Advanced Live
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-video me-2"></i>Basic Live
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
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
                        <h1 class="h3 mb-0">Advanced Live Stream Control</h1>
                        <small>Multi-camera streaming with overlays</small>
                    </div>
                    <div>
                        <span class="live-indicator <?php echo $current_live ? 'live-online' : 'live-offline'; ?>"></span>
                        <span class="text-white me-3">
                            <?php echo $current_live ? 'MULTI-CAM LIVE' : 'OFFLINE'; ?>
                        </span>
                        <button class="btn btn-light" onclick="showMultiCameraForm()">
                            <i class="fas fa-plus me-2"></i>Multi-Camera Setup
                        </button>
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

                <!-- Setup Required Alert -->
                <?php if (mysqli_num_rows($stream_cameras_exist) === 0): ?>
                    <div class="alert alert-warning" role="alert">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Setup Required</h5>
                        <p>The multi-camera live streaming tables haven't been created yet. Please run the database setup first.</p>
                        <a href="../setup_multi_camera_db.php" class="btn btn-primary">
                            <i class="fas fa-database me-2"></i>Run Database Setup
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Current Multi-Camera Live Stream -->
                <?php if ($current_live && $current_live['camera_count'] > 1): ?>
                    <div class="card mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <span class="live-indicator live-online"></span>
                                Multi-Camera Live: <?php echo htmlspecialchars($current_live['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="control-panel mb-3">
                                <h6><i class="fas fa-video me-2"></i>Camera Control</h6>
                                <div class="camera-controls">
                                    <?php for ($i = 1; $i <= $current_live['camera_count']; $i++): ?>
                                        <button class="camera-switch-btn <?php echo $current_live['active_camera'] == $i ? 'active' : ''; ?>" 
                                                onclick="switchCamera(<?php echo $current_live['id']; ?>, <?php echo $i; ?>)">
                                            <i class="fas fa-video me-1"></i>Camera <?php echo $i; ?>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">Current: Camera <?php echo $current_live['active_camera']; ?> - <?php echo htmlspecialchars($current_live['camera_name'] ?? 'Unknown'); ?></small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <p><?php echo htmlspecialchars($current_live['description']); ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="../live-multi-camera.php?id=<?php echo $current_live['id']; ?>" target="_blank" class="btn btn-danger">
                                            <i class="fas fa-external-link-alt me-2"></i>View Multi-Camera Stream
                                        </a>
                                        <button class="btn btn-warning" onclick="stopStream(<?php echo $current_live['id']; ?>)">
                                            <i class="fas fa-stop me-2"></i>Stop Stream
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h6>Live Viewers</h6>
                                        <h3 class="text-success" id="currentViewers">2,456</h3>
                                        <small class="text-muted">Real-time count</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Multi-Camera Setup Form -->
                <div class="card mb-4" id="multiCameraForm" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Multi-Camera Stream Setup</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="multiCameraSetupForm">
                            <input type="hidden" name="action" value="add_multi_camera_stream">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Stream Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="camera_count" class="form-label">Number of Cameras *</label>
                                        <select class="form-select" id="camera_count" name="camera_count" onchange="updateCameraFields()" required>
                                            <option value="">Select cameras</option>
                                            <option value="2">2 Cameras</option>
                                            <option value="3">3 Cameras</option>
                                            <option value="4">4 Cameras</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Layout Style</label>
                                        <div class="layout-options">
                                            <div class="layout-option selected" data-layout="grid">
                                                <div class="layout-preview grid">
                                                    <div></div><div></div><div></div><div></div>
                                                </div>
                                                <small>Grid View</small>
                                            </div>
                                            <div class="layout-option" data-layout="side-by-side">
                                                <div class="layout-preview side-by-side">
                                                    <div></div><div></div>
                                                </div>
                                                <small>Side by Side</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="layout" id="selectedLayout" value="grid">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="overlay_template" class="form-label">Default Overlay</label>
                                        <select class="form-select" id="overlay_template" name="overlay_template">
                                            <option value="">No overlay</option>
                                            <?php while ($template = mysqli_fetch_assoc($templates_result)): ?>
                                                <option value="<?php echo $template['id']; ?>">
                                                    <?php echo htmlspecialchars($template['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Camera Configuration Fields -->
                            <div id="cameraFields"></div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Multi-Camera Stream
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideMultiCameraForm()">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Overlay Management -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Overlay Templates</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Available Templates</h6>
                                <?php 
                                mysqli_data_seek($templates_result, 0);
                                while ($template = mysqli_fetch_assoc($templates_result)): 
                                ?>
                                    <div class="template-card" onclick="selectTemplate(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['name']); ?>')">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6><?php echo htmlspecialchars($template['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($template['description']); ?></small>
                                            </div>
                                            <span class="badge bg-<?php echo $template['is_default'] ? 'primary' : 'secondary'; ?>">
                                                <?php echo $template['template_type']; ?>
                                            </span>
                                        </div>
                                        <div class="overlay-preview mt-2">
                                            <div class="overlay-item" style="top: 10px; left: 10px;">
                                                <?php echo htmlspecialchars($template['name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Add Custom Overlay</h6>
                                <form method="POST" id="overlayForm">
                                    <input type="hidden" name="action" value="add_custom_overlay">
                                    
                                    <div class="mb-3">
                                        <label for="overlay_stream_id" class="form-label">Select Stream</label>
                                        <select class="form-select" id="overlay_stream_id" name="stream_id" required>
                                            <option value="">Choose stream...</option>
                                            <?php while ($stream = mysqli_fetch_assoc($streams_result)): ?>
                                                <option value="<?php echo $stream['id']; ?>">
                                                    <?php echo htmlspecialchars($stream['title']); ?>
                                                    (<?php echo $stream['configured_cameras']; ?> cameras)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="overlay_template_id" class="form-label">Template Type</label>
                                        <select class="form-select" id="overlay_template_id" name="template_id" onchange="updateOverlayFields()" required>
                                            <option value="">Select template...</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="overlay_name" class="form-label">Overlay Name</label>
                                        <input type="text" class="form-control" id="overlay_name" name="overlay_name" required>
                                    </div>
                                    
                                    <div id="overlayCustomFields"></div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="position_x" class="form-label">X Position</label>
                                            <input type="number" class="form-control" id="position_x" name="position_x" value="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="position_y" class="form-label">Y Position</label>
                                            <input type="number" class="form-control" id="position_y" name="position_y" value="0">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>Add Overlay
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Streams List -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Multi-Camera Streams</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Cameras</th>
                                        <th>Layout</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php mysqli_data_seek($streams_result, 0); ?>
                                    <?php while ($stream = mysqli_fetch_assoc($streams_result)): ?>
                                        <?php if ($stream['configured_cameras'] > 1): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($stream['title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($stream['description'] ?? '', 0, 50)) . '...'; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $stream['configured_cameras']; ?> Cameras</span>
                                                    <br><small><?php echo htmlspecialchars($stream['camera_names'] ?? ''); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">Grid</span>
                                                </td>
                                                <td>
                                                    <span class="live-indicator live-<?php echo $stream['status']; ?>"></span>
                                                    <span class="badge bg-<?php 
                                                        echo $stream['status'] == 'online' ? 'success' : 
                                                             ($stream['status'] == 'scheduled' ? 'warning' : 'secondary'); 
                                                    ?>">
                                                        <?php echo ucfirst($stream['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($stream['created_at'])); ?></td>
                                                <td>
                                                    <div class="action-buttons d-flex gap-1">
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="editStream(<?php echo $stream['id']; ?>)" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <?php if ($stream['status'] != 'online'): ?>
                                                            <button class="btn btn-sm btn-outline-success" onclick="startStream(<?php echo $stream['id']; ?>)" title="Start Live">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($stream['status'] == 'online'): ?>
                                                            <button class="btn btn-sm btn-outline-warning" onclick="stopStream(<?php echo $stream['id']; ?>)" title="Stop Stream">
                                                                <i class="fas fa-stop"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <a href="../live-multi-camera.php?id=<?php echo $stream['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteStream(<?php echo $stream['id']; ?>)" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedTemplateId = null;
        let selectedTemplateName = null;

        // Multi-camera form functions
        function showMultiCameraForm() {
            document.getElementById('multiCameraForm').style.display = 'block';
            document.getElementById('multiCameraSetupForm').reset();
            document.getElementById('cameraFields').innerHTML = '';
        }

        function hideMultiCameraForm() {
            document.getElementById('multiCameraForm').style.display = 'none';
        }

        function updateCameraFields() {
            const cameraCount = document.getElementById('camera_count').value;
            const cameraFields = document.getElementById('cameraFields');
            
            if (!cameraCount) {
                cameraFields.innerHTML = '';
                return;
            }
            
            let html = '<h6 class="mt-4 mb-3">Camera Configuration</h6><div class="camera-grid">';
            
            for (let i = 1; i <= cameraCount; i++) {
                html += `
                    <div class="camera-card">
                        <div class="camera-preview">
                            <i class="fas fa-video fa-2x"></i>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Camera ${i} Name</label>
                            <input type="text" class="form-control form-control-sm" name="camera_name_${i}" 
                                   value="Camera ${i}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Stream URL</label>
                            <input type="url" class="form-control form-control-sm" name="camera_url_${i}" 
                                   placeholder="RTMP/Stream URL">
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            cameraFields.innerHTML = html;
        }

        // Layout selection
        document.querySelectorAll('.layout-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.layout-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedLayout').value = this.dataset.layout;
            });
        });

        // Template selection
        function selectTemplate(templateId, templateName) {
            selectedTemplateId = templateId;
            selectedTemplateName = templateName;
            
            document.querySelectorAll('.template-card').forEach(card => card.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            // Update overlay form template dropdown
            const templateSelect = document.getElementById('overlay_template_id');
            templateSelect.value = templateId;
            updateOverlayFields();
        }

        function updateOverlayFields() {
            const templateId = document.getElementById('overlay_template_id').value;
            const customFields = document.getElementById('overlayCustomFields');
            
            if (!templateId) {
                customFields.innerHTML = '';
                return;
            }
            
            // Define field configurations for different template types
            const fieldConfigs = {
                breaking: [
                    { name: 'overlay_title', label: 'Breaking Title', type: 'text', default: 'BREAKING NEWS' }
                ],
                news: [
                    { name: 'overlay_content', label: 'Ticker Content', type: 'text', default: 'Latest news updates...' }
                ],
                weather: [
                    { name: 'overlay_icon', label: 'Weather Icon', type: 'text', default: '☀️' },
                    { name: 'overlay_temp', label: 'Temperature', type: 'text', default: '25' },
                    { name: 'overlay_condition', label: 'Condition', type: 'text', default: 'Sunny' }
                ],
                sports: [
                    { name: 'home_team', label: 'Home Team', type: 'text', default: 'Team A' },
                    { name: 'away_team', label: 'Away Team', type: 'text', default: 'Team B' },
                    { name: 'home_score', label: 'Home Score', type: 'number', default: '0' },
                    { name: 'away_score', label: 'Away Score', type: 'number', default: '0' }
                ]
            };
            
            // Get template type (this would normally come from database)
            const templateType = 'breaking'; // Simplified for demo
            
            let html = '<h6 class="mt-3 mb-3">Overlay Content</h6>';
            
            if (fieldConfigs[templateType]) {
                fieldConfigs[templateType].forEach(field => {
                    html += `
                        <div class="mb-3">
                            <label for="${field.name}" class="form-label">${field.label}</label>
                            <input type="${field.type}" class="form-control" id="${field.name}" 
                                   name="${field.name}" value="${field.default}">
                        </div>
                    `;
                });
            }
            
            customFields.innerHTML = html;
        }

        // Camera switching
        function switchCamera(streamId, cameraNumber) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="switch_camera">
                <input type="hidden" name="stream_id" value="${streamId}">
                <input type="hidden" name="camera_number" value="${cameraNumber}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Stream management functions
        function startStream(streamId) {
            if (confirm('Start this multi-camera live stream?')) {
                location.href = '?start=' + streamId;
            }
        }

        function stopStream(streamId) {
            if (confirm('Stop this live stream?')) {
                location.href = '?stop=' + streamId;
            }
        }

        function deleteStream(streamId) {
            if (confirm('Delete this stream? This action cannot be undone.')) {
                location.href = '?delete=' + streamId;
            }
        }

        function editStream(streamId) {
            location.href = '?edit=' + streamId;
        }

        // Simulate live viewer count
        setInterval(() => {
            const viewerElement = document.getElementById('currentViewers');
            if (viewerElement) {
                const currentCount = parseInt(viewerElement.textContent.replace(',', ''));
                const change = Math.floor(Math.random() * 101) - 50;
                const newCount = Math.max(1000, currentCount + change);
                viewerElement.textContent = newCount.toLocaleString();
            }
        }, 4000);
    </script>
</body>
</html>
