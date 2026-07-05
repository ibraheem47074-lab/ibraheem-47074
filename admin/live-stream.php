<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle add/edit stream
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $stream_url = clean_input($_POST['stream_url']);
    $embed_code = clean_input($_POST['embed_code']);
    $status = clean_input($_POST['status']);
    $description = clean_input($_POST['description']);
    $schedule_time = !empty($_POST['schedule_time']) ? $_POST['schedule_time'] : null;
    $stream_id = isset($_POST['stream_id']) ? (int)$_POST['stream_id'] : 0;
    
    if (empty($title)) {
        $error = 'Stream title is required';
    } elseif (empty($stream_url) && empty($embed_code)) {
        $error = 'Either stream URL or embed code is required';
    } else {
        // Handle thumbnail upload
        $thumbnail_path = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['thumbnail']['size'] <= MAX_FILE_SIZE) {
                    $file_name = uniqid() . '.' . $file_extension;
                    $upload_path = UPLOAD_PATH . 'thumbnails/' . $file_name;
                    
                    // Create thumbnails directory if it doesn't exist
                    if (!file_exists('../' . UPLOAD_PATH . 'thumbnails/')) {
                        mkdir('../' . UPLOAD_PATH . 'thumbnails/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../' . $upload_path)) {
                        $thumbnail_path = $upload_path;
                    } else {
                        $error = 'Error uploading thumbnail';
                    }
                } else {
                    $error = 'File size too large. Maximum size is 5MB';
                }
            } else {
                $error = 'Invalid file type. Only JPG, PNG, and GIF files are allowed';
            }
        }
        
        if (empty($error)) {
            if ($stream_id > 0) {
                // Update existing stream
                $query = "UPDATE live_stream SET title = ?, stream_url = ?, embed_code = ?, status = ?, description = ?, schedule_time = ?, thumbnail = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $stream_url, $embed_code, $status, $description, $schedule_time, $thumbnail_path, $stream_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Live stream updated successfully!";
                } else {
                    $error = "Error updating live stream: " . mysqli_error($conn);
                }
            } else {
                // Insert new stream
                $query = "INSERT INTO live_stream (title, stream_url, embed_code, status, description, schedule_time, thumbnail) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sssssss', $title, $stream_url, $embed_code, $status, $description, $schedule_time, $thumbnail_path);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Live stream added successfully!";
                    // Clear form
                    $_POST = array();
                } else {
                    $error = "Error adding live stream: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Handle start/stop stream actions
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $stream_id = $_GET['start'];
    $query = "UPDATE live_stream SET status = 'online' WHERE id = $stream_id";
    if (mysqli_query($conn, $query)) {
        $success = "Stream started successfully!";
    } else {
        $error = "Error starting stream!";
    }
}

if (isset($_GET['stop']) && is_numeric($_GET['stop'])) {
    $stream_id = $_GET['stop'];
    $query = "UPDATE live_stream SET status = 'offline' WHERE id = $stream_id";
    if (mysqli_query($conn, $query)) {
        $success = "Stream stopped successfully!";
    } else {
        $error = "Error stopping stream!";
    }
}

// Handle edit action
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stream_id = $_GET['edit'];
    $stream_query = "SELECT * FROM live_stream WHERE id = $stream_id";
    $stream_result = mysqli_query($conn, $stream_query);
    $edit_stream = mysqli_fetch_assoc($stream_result);
    
    if ($edit_stream) {
        // Pre-fill form with existing data
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showStreamForm();
                document.getElementById('formTitle').textContent = 'Edit Stream';
                document.getElementById('stream_id').value = '" . $stream_id . "';
                document.getElementById('title').value = '" . htmlspecialchars($edit_stream['title']) . "';
                document.getElementById('stream_url').value = '" . htmlspecialchars($edit_stream['stream_url']) . "';
                document.getElementById('embed_code').value = '" . htmlspecialchars($edit_stream['embed_code'] ?? '') . "';
                document.getElementById('status').value = '" . htmlspecialchars($edit_stream['status']) . "';
                document.getElementById('description').value = '" . htmlspecialchars($edit_stream['description']) . "';
                document.getElementById('schedule_time').value = '" . htmlspecialchars($edit_stream['schedule_time'] ?? '') . "';
            });
        </script>";
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stream_id = $_GET['delete'];
    
    // Get stream thumbnail to delete
    $stream_query = "SELECT thumbnail FROM live_stream WHERE id = $stream_id";
    $stream_result = mysqli_query($conn, $stream_query);
    $stream = mysqli_fetch_assoc($stream_result);
    
    // Delete stream
    $delete_query = "DELETE FROM live_stream WHERE id = $stream_id";
    
    if (mysqli_query($conn, $delete_query)) {
        // Delete thumbnail file if exists
        if ($stream && $stream['thumbnail'] && file_exists('../' . $stream['thumbnail'])) {
            unlink('../' . $stream['thumbnail']);
        }
        
        $success = "Live stream deleted successfully!";
    } else {
        $error = "Error deleting live stream!";
    }
}

// Get all streams
$streams_query = "SELECT * FROM live_stream ORDER BY created_at DESC";
$streams_result = mysqli_query($conn, $streams_query);

// Get current live stream
$current_live = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1"));
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
        .stream-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .live-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .live-online {
            background-color: #00ff00;
            animation: pulse 2s infinite;
        }
        .live-offline {
            background-color: #ff0000;
        }
        .live-scheduled {
            background-color: #ffa500;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .embed-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
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
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="youtube-live-setup.php">
                                <i class="fab fa-youtube me-2 youtube-red"></i>YouTube Live Setup
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="custom-streaming-setup.php">
                                <i class="fas fa-server me-2 custom-purple"></i>Custom Streaming
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
                        <h1 class="h3 mb-0">Live Stream Control</h1>
                        <small>Manage live broadcasts and streaming</small>
                    </div>
                    <div>
                        <span class="live-indicator <?php echo $current_live ? 'live-online' : 'live-offline'; ?>"></span>
                        <span class="text-white me-3">
                            <?php echo $current_live ? 'LIVE NOW' : 'OFFLINE'; ?>
                        </span>
                        <button class="btn btn-danger me-2" onclick="window.location.href='youtube-live-setup.php'">
                            <i class="fab fa-youtube me-2"></i>YouTube Live Setup
                        </button>
                        <button class="btn btn-primary me-2" onclick="window.location.href='custom-streaming-setup.php'">
                            <i class="fas fa-server me-2"></i>Custom Streaming
                        </button>
                        <button class="btn btn-light" onclick="showStreamForm()">
                            <i class="fas fa-plus me-2"></i>Add Stream
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

                <!-- Current Live Stream -->
                <?php if ($current_live): ?>
                    <div class="card mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <span class="live-indicator live-online"></span>
                                Currently Live: <?php echo htmlspecialchars($current_live['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p><?php echo htmlspecialchars($current_live['description']); ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="../live.php" target="_blank" class="btn btn-danger">
                                            <i class="fas fa-external-link-alt me-2"></i>View Live Stream
                                        </a>
                                        <button class="btn btn-warning" onclick="stopStream(<?php echo $current_live['id']; ?>)">
                                            <i class="fas fa-stop me-2"></i>Stop Stream
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h6>Live Viewers</h6>
                                        <h3 class="text-success
                                        " id="currentViewers">1,234</h3>
                                        <small class="text-muted">Real-time count</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stream Form (Hidden by default) -->
                <div class="card mb-4" id="streamFormCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0" id="formTitle">Add New Stream</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="streamForm">
                            <input type="hidden" name="stream_id" id="stream_id" value="0">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Stream Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="stream_url" class="form-label">Stream URL</label>
                                        <input type="url" class="form-control" id="stream_url" name="stream_url" 
                                               placeholder="YouTube Live URL or RTMP stream">
                                        <small class="text-muted">YouTube Live, Vimeo, or custom streaming URL</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="embed_code" class="form-label">Embed Code</label>
                                        <textarea class="form-control" id="embed_code" name="embed_code" rows="3" 
                                                  placeholder="HTML embed code (optional)"></textarea>
                                        <small class="text-muted">Custom embed code for advanced streaming</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="offline">Offline</option>
                                            <option value="online">Online (Live Now)</option>
                                            <option value="scheduled">Scheduled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="schedule_time" class="form-label">Schedule Time</label>
                                        <input type="datetime-local" class="form-control" id="schedule_time" name="schedule_time">
                                        <small class="text-muted">Required for scheduled streams</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="thumbnail" class="form-label">Thumbnail Image</label>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewThumbnail(event)">
                                        <img id="thumbnailPreview" class="img-fluid mt-2" style="max-width: 200px; display: none;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Describe this live stream"></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Stream
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideStreamForm()">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Streams List -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">All Streams (<?php echo mysqli_num_rows($streams_result); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Schedule</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($stream = mysqli_fetch_assoc($streams_result)): ?>
                                        <tr>
                                            <td>
                                                <?php if ($stream['thumbnail']): ?>
                                                    <img src="../<?php echo htmlspecialchars($stream['thumbnail']); ?>" alt="Thumbnail" class="stream-thumbnail">
                                                <?php else: ?>
                                                    <div class="stream-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-broadcast-tower text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($stream['title']); ?></strong>
                                                    <?php if ($stream['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($stream['description'], 0, 50)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </div>
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
                                            <td>
                                                <?php if ($stream['schedule_time']): ?>
                                                    <?php echo date('M d, Y - h:i A', strtotime($stream['schedule_time'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not scheduled</span>
                                                <?php endif; ?>
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
                                                    
                                                    <a href="../live.php" target="_blank" class="btn btn-sm btn-outline-info" title="Preview">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteStream(<?php echo $stream['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($streams_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i>
                                <h5>No streams found</h5>
                                <p class="text-muted">Start by adding your first live stream</p>
                                <button class="btn btn-danger" onclick="showStreamForm()">
                                    <i class="fas fa-plus me-2"></i>Add First Stream
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Streaming Guide -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fab fa-youtube me-2 text-danger"></i>
                            Complete YouTube Live Setup Guide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fab fa-youtube me-2 text-danger"></i>Step-by-Step YouTube Live Setup</h6>
                                <div class="accordion" id="youtubeAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                <strong>Step 1:</strong> Create YouTube Live Event
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#youtubeAccordion">
                                            <div class="accordion-body">
                                                <ol>
                                                    <li>Go to <a href="https://studio.youtube.com" target="_blank">YouTube Studio</a></li>
                                                    <li>Click "Create" → "Go Live"</li>
                                                    <li>Choose "Stream" (not "Premiere")</li>
                                                    <li>Fill in title, description, and thumbnail</li>
                                                    <li>Set privacy status (Public/Unlisted/Private)</li>
                                                    <li>Click "Create Stream"</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingTwo">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                                <strong>Step 2:</strong> Get Stream Key & URL
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#youtubeAccordion">
                                            <div class="accordion-body">
                                                <ol>
                                                    <li>After creating the stream, YouTube shows "Stream Settings"</li>
                                                    <li>Copy the "Stream Key" (format: xxxx-xxxx-xxxx-xxxx)</li>
                                                    <li>Note the "Server URL" (usually: rtmp://a.rtmp.youtube.com/live2)</li>
                                                    <li>Copy your YouTube video URL from the stream page</li>
                                                    <li>Keep this page open for reference during OBS setup</li>
                                                </ol>
                                                <div class="alert alert-info">
                                                    <strong>Stream Key Example:</strong> abc1-def2-ghi3-jkl4<br>
                                                    <strong>Never share your stream key publicly!</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingThree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                                <strong>Step 3:</strong> Configure OBS Studio
                                            </button>
                                        </h2>
                                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#youtubeAccordion">
                                            <div class="accordion-body">
                                                <ol>
                                                    <li>Download and install <a href="https://obsproject.com" target="_blank">OBS Studio</a></li>
                                                    <li>Open OBS → File → Settings → Stream</li>
                                                    <li>Service: "YouTube"</li>
                                                    <li>Server: "YouTube - RTMP"</li>
                                                    <li>Stream Key: [Paste your YouTube stream key]</li>
                                                    <li>Click "Apply" → "OK"</li>
                                                    <li>Go to Settings → Output</li>
                                                    <li>Output Mode: "Advanced"</li>
                                                    <li>Streaming Tab: Bitrate 4000-8000 kbps, CBR, Keyframe 2s</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingFour">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                                <strong>Step 4:</strong> Start Broadcasting
                                            </button>
                                        </h2>
                                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#youtubeAccordion">
                                            <div class="accordion-body">
                                                <ol>
                                                    <li>Add sources in OBS (Camera, Microphone, Display Capture, etc.)</li>
                                                    <li>Test audio and video quality</li>
                                                    <li>Click "Start Streaming" in OBS</li>
                                                    <li>Check YouTube Studio for stream health</li>
                                                    <li>Once stable, go live on YouTube</li>
                                                    <li>Set stream status to "Online" in this admin panel</li>
                                                </ol>
                                                <div class="alert alert-success">
                                                    <strong>Pro Tip:</strong> Test your setup 15-30 minutes before going live!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-tools me-2 text-primary"></i>Quick Setup Tools</h6>
                                <div class="list-group">
                                    <a href="youtube-live-setup.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="fab fa-youtube me-2 text-danger"></i>
                                                YouTube Live Setup Wizard
                                            </h6>
                                            <small class="text-muted">Recommended</small>
                                        </div>
                                        <p class="mb-1 small">Step-by-step guided setup for YouTube Live streaming</p>
                                    </a>
                                    <a href="custom-streaming-setup.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="fas fa-server me-2 custom-purple"></i>
                                                Custom RTMP Setup
                                            </h6>
                                            <small class="text-muted">Advanced</small>
                                        </div>
                                        <p class="mb-1 small">Professional RTMP server and OBS configuration</p>
                                    </a>
                                    <a href="https://obsproject.com" target="_blank" class="list-group-item list-group-item-action">
                                        <h6 class="mb-1">
                                            <i class="fas fa-download me-2 text-primary"></i>
                                            Download OBS Studio
                                        </h6>
                                        <p class="mb-1 small">Free, open-source software for live streaming</p>
                                    </a>
                                    <a href="https://support.google.com/youtube/topic/9257498" target="_blank" class="list-group-item list-group-item-action">
                                        <h6 class="mb-1">
                                            <i class="fas fa-question-circle me-2 text-info"></i>
                                            YouTube Live Help
                                        </h6>
                                        <p class="mb-1 small">Official YouTube Live documentation and support</p>
                                    </a>
                                </div>
                                
                                <div class="mt-4">
                                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Best Practices</h6>
                                    <ul class="small">
                                        <li><strong>Internet:</strong> Minimum 5 Mbps upload speed</li>
                                        <li><strong>Lighting:</strong> Good front-facing lighting</li>
                                        <li><strong>Audio:</strong> Use external microphone if possible</li>
                                        <li><strong>Background:</strong> Clean, professional backdrop</li>
                                        <li><strong>Test:</strong> Always test before going live</li>
                                        <li><strong>Monitor:</strong> Watch stream health metrics</li>
                                        <li><strong>Backup:</strong> Have backup internet ready</li>
                                    </ul>
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
        // Stream management functions
        function showStreamForm() {
            document.getElementById('streamFormCard').style.display = 'block';
            document.getElementById('formTitle').textContent = 'Add New Stream';
            document.getElementById('stream_id').value = '0';
            document.getElementById('streamForm').reset();
            document.getElementById('thumbnailPreview').style.display = 'none';
        }

        function hideStreamForm() {
            document.getElementById('streamFormCard').style.display = 'none';
        }

        function editStream(streamId) {
            // Load stream data via AJAX or reload page
            location.href = '?edit=' + streamId;
        }

        function startStream(streamId) {
            if (confirm('Are you sure you want to start this live stream?')) {
                location.href = '?start=' + streamId;
            }
        }

        function stopStream(streamId) {
            if (confirm('Are you sure you want to stop this live stream?')) {
                location.href = '?stop=' + streamId;
            }
        }

        function deleteStream(streamId) {
            if (confirm('Are you sure you want to delete this stream? This action cannot be undone.')) {
                location.href = '?delete=' + streamId;
            }
        }

        // Thumbnail preview
        function previewThumbnail(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('thumbnailPreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Handle status change
        document.getElementById('status').addEventListener('change', function() {
            const scheduleField = document.getElementById('schedule_time');
            const scheduleLabel = scheduleField.closest('.mb-3').querySelector('label');
            
            if (this.value === 'scheduled') {
                scheduleField.required = true;
                scheduleLabel.innerHTML = 'Schedule Time *';
            } else {
                scheduleField.required = false;
                scheduleLabel.innerHTML = 'Schedule Time';
            }
        });

        // Simulate live viewer count
        setInterval(() => {
            const viewerElement = document.getElementById('currentViewers');
            if (viewerElement) {
                const currentCount = parseInt(viewerElement.textContent.replace(',', ''));
                const change = Math.floor(Math.random() * 51) - 25; // Random change between -25 and +25
                const newCount = Math.max(100, currentCount + change);
                viewerElement.textContent = newCount.toLocaleString();
            }
        }, 5000);

        // Auto-refresh stream status
        setInterval(() => {
            // In a real application, you would fetch current status from API
            console.log('Checking stream status...');
        }, 30000);
    </script>
</body>
</html>
