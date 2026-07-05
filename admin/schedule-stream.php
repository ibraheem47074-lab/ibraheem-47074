<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $stream_url = clean_input($_POST['stream_url']);
    $embed_code = clean_input($_POST['embed_code']);
    $description = clean_input($_POST['description']);
    $schedule_time = $_POST['schedule_time'];
    $duration_hours = (int)$_POST['duration_hours'];
    $auto_start = isset($_POST['auto_start']) ? 1 : 0;
    $thumbnail_path = '';
    
    // Calculate end time
    $end_time = date('Y-m-d H:i:s', strtotime($schedule_time . ' +' . $duration_hours . ' hours'));
    
    if (empty($title) || empty($schedule_time)) {
        $error = 'Title and schedule time are required';
    } else {
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['thumbnail']['size'] <= MAX_FILE_SIZE) {
                    $file_name = uniqid() . '.' . $file_extension;
                    $upload_path = UPLOAD_PATH . 'thumbnails/' . $file_name;
                    
                    if (!file_exists('../' . UPLOAD_PATH . 'thumbnails/')) {
                        mkdir('../' . UPLOAD_PATH . 'thumbnails/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../' . $upload_path)) {
                        $thumbnail_path = $upload_path;
                    }
                }
            }
        }
        
        if (empty($error)) {
            $query = "INSERT INTO live_stream (title, stream_url, embed_code, description, schedule_time, end_time, duration_hours, auto_start, thumbnail, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssssiiis', $title, $stream_url, $embed_code, $description, 
                                  $schedule_time, $end_time, $duration_hours, $auto_start, $thumbnail_path);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Live stream scheduled successfully!";
                $_POST = array();
            } else {
                $error = "Error scheduling live stream: " . mysqli_error($conn);
            }
        }
    }
}

// Get scheduled streams
$scheduled_query = "SELECT * FROM live_stream WHERE status = 'scheduled' ORDER BY schedule_time ASC";
$scheduled_result = mysqli_query($conn, $scheduled_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Live Stream - PK Live News Admin</title>
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
        .schedule-card {
            border-left: 4px solid #667eea;
        }
        .time-indicator {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .countdown-timer {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 10px;
            border-radius: 10px;
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
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="schedule-stream.php">
                                <i class="fas fa-calendar-alt me-2"></i>Schedule Stream
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
                        <h1 class="h3 mb-0">Schedule Live Stream</h1>
                        <small>Set up automatic streaming at specific times</small>
                    </div>
                    <div>
                        <a href="live-stream.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Streams
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

                <!-- Schedule Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Schedule New Stream
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Stream Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="stream_url" class="form-label">Stream URL</label>
                                        <input type="url" class="form-control" id="stream_url" name="stream_url" 
                                               value="<?php echo isset($_POST['stream_url']) ? htmlspecialchars($_POST['stream_url']) : ''; ?>" 
                                               placeholder="YouTube Live URL or RTMP stream">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="embed_code" class="form-label">Embed Code</label>
                                        <textarea class="form-control" id="embed_code" name="embed_code" rows="3" 
                                                  placeholder="HTML embed code (optional)"><?php echo isset($_POST['embed_code']) ? htmlspecialchars($_POST['embed_code']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="schedule_time" class="form-label">Schedule Time *</label>
                                        <input type="datetime-local" class="form-control" id="schedule_time" name="schedule_time" 
                                               value="<?php echo isset($_POST['schedule_time']) ? htmlspecialchars($_POST['schedule_time']) : ''; ?>" 
                                               required>
                                        <small class="text-muted">When the stream should automatically start</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="duration_hours" class="form-label">Duration (Hours)</label>
                                        <input type="number" class="form-control" id="duration_hours" name="duration_hours" 
                                               value="<?php echo isset($_POST['duration_hours']) ? htmlspecialchars($_POST['duration_hours']) : '2'; ?>" 
                                               min="1" max="24">
                                        <small class="text-muted">How long the stream should run</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="thumbnail" class="form-label">Thumbnail Image</label>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewThumbnail(event)">
                                        <img id="thumbnailPreview" class="img-fluid mt-2" style="max-width: 200px; display: none;">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auto_start" name="auto_start" 
                                                   <?php echo isset($_POST['auto_start']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="auto_start">
                                                Auto-start at scheduled time
                                            </label>
                                        </div>
                                        <small class="text-muted">Stream will automatically go live at scheduled time</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Describe this scheduled stream"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Stream
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-times me-2"></i>Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Scheduled Streams -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Scheduled Streams
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($scheduled_result) > 0): ?>
                            <div class="row">
                                <?php while ($stream = mysqli_fetch_assoc($scheduled_result)): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card schedule-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($stream['title']); ?></h6>
                                                    <span class="badge bg-warning">Scheduled</span>
                                                </div>
                                                
                                                <div class="time-indicator mb-2">
                                                    <i class="fas fa-clock me-2"></i>
                                                    <?php echo date('M d, Y - h:i A', strtotime($stream['schedule_time'])); ?>
                                                </div>
                                                
                                                <div class="countdown-timer mb-2" id="countdown-<?php echo $stream['id']; ?>">
                                                    Loading countdown...
                                                </div>
                                                
                                                <p class="card-text small">
                                                    <?php echo htmlspecialchars(substr($stream['description'], 0, 100)) . '...'; ?>
                                                </p>
                                                
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editStream(<?php echo $stream['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="startNow(<?php echo $stream['id']; ?>)">
                                                        <i class="fas fa-play"></i> Start Now
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelStream(<?php echo $stream['id']; ?>)">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No scheduled streams</h5>
                                <p class="text-muted">Schedule your first live stream above</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Reset form
        function resetForm() {
            document.querySelector('form').reset();
            document.getElementById('thumbnailPreview').style.display = 'none';
        }

        // Countdown timers for scheduled streams
        function updateCountdowns() {
            <?php 
            mysqli_data_seek($scheduled_result, 0);
            while ($stream = mysqli_fetch_assoc($scheduled_result)): 
            ?>
                const streamTime<?php echo $stream['id']; ?> = new Date('<?php echo $stream['schedule_time']; ?>').getTime();
                const now = new Date().getTime();
                const distance<?php echo $stream['id']; ?> = streamTime<?php echo $stream['id']; ?> - now;
                
                const countdownEl<?php echo $stream['id']; ?> = document.getElementById('countdown-<?php echo $stream['id']; ?>');
                
                if (distance<?php echo $stream['id']; ?> < 0) {
                    countdownEl<?php echo $stream['id']; ?>.innerHTML = '<i class="fas fa-play me-2"></i>Should be LIVE NOW!';
                    countdownEl<?php echo $stream['id']; ?>.style.background = 'linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%)';
                } else {
                    const days<?php echo $stream['id']; ?> = Math.floor(distance<?php echo $stream['id']; ?> / (1000 * 60 * 60 * 24));
                    const hours<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60)) / 1000);
                    
                    countdownEl<?php echo $stream['id']; ?>.innerHTML = 
                        '<i class="fas fa-clock me-2"></i>' + 
                        days<?php echo $stream['id']; ?> + 'd ' + 
                        hours<?php echo $stream['id']; ?> + 'h ' + 
                        minutes<?php echo $stream['id']; ?> + 'm ' + 
                        seconds<?php echo $stream['id']; ?> + 's';
                }
            <?php endwhile; ?>
        }

        // Update countdowns every second
        setInterval(updateCountdowns, 1000);
        
        // Initial update
        updateCountdowns();

        // Stream actions
        function startNow(streamId) {
            if (confirm('Start this stream now?')) {
                location.href = 'live-stream.php?start_now=' + streamId;
            }
        }

        function cancelStream(streamId) {
            if (confirm('Cancel this scheduled stream?')) {
                location.href = 'live-stream.php?cancel=' + streamId;
            }
        }

        function editStream(streamId) {
            location.href = 'live-stream.php?edit=' + streamId;
        }

        // Auto-refresh for live streams
        setInterval(() => {
            // Check if any scheduled streams should start automatically
            fetch('check-scheduled-streams.php')
                .then(response => response.json())
                .then(data => {
                    if (data.started_streams && data.started_streams.length > 0) {
                        // Refresh page to show newly started streams
                        location.reload();
                    }
                })
                .catch(error => console.log('Error checking scheduled streams:', error));
        }, 30000); // Check every 30 seconds
    </script>
</body>
</html>
