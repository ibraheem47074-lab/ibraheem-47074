<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

// Handle live update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_live') {
        $title = clean_input($_POST['title']);
        $event_type = clean_input($_POST['event_type']);
        $description = clean_input($_POST['description']);
        
        // Create live blog entry
        $query = "INSERT INTO live_stream (title, stream_url, embed_code, status, description, created_at) 
                  VALUES (?, 'live_report', ?, 'online', ?, NOW())";
        
        $embed_code = json_encode([
            'reporter_id' => $reporter_id,
            'event_type' => $event_type,
            'description' => $description
        ]);
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $title, $embed_code, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            $live_id = mysqli_insert_id($conn);
            header("Location: live_reporter.php?live_id=$live_id");
            exit();
        }
    }
    
    if ($_POST['action'] === 'add_update') {
        $live_id = (int)$_POST['live_id'];
        $update_text = clean_input($_POST['update_text']);
        $timestamp = date('Y-m-d H:i:s');
        
        // Get existing live stream
        $live_query = "SELECT * FROM live_stream WHERE id = $live_id";
        $live_result = mysqli_query($conn, $live_query);
        $live_stream = mysqli_fetch_assoc($live_result);
        
        if ($live_stream) {
            $embed_code = json_decode($live_stream['embed_code'], true);
            $embed_code['updates'][] = [
                'text' => $update_text,
                'timestamp' => $timestamp,
                'reporter' => $_SESSION['user_name']
            ];
            
            $new_embed_code = json_encode($embed_code);
            
            $update_query = "UPDATE live_stream SET embed_code = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'si', $new_embed_code, $live_id);
            mysqli_stmt_execute($stmt);
        }
        
        header("Location: live_reporter.php?live_id=$live_id");
        exit();
    }
    
    if ($_POST['action'] === 'end_live') {
        $live_id = (int)$_POST['live_id'];
        
        $update_query = "UPDATE live_stream SET status = 'offline', end_time = NOW() WHERE id = $live_id";
        mysqli_query($conn, $update_query);
        
        header("Location: live_reporter.php");
        exit();
    }
}

// Get current live session
$live_id = $_GET['live_id'] ?? 0;
$current_live = null;

if ($live_id) {
    $live_query = "SELECT * FROM live_stream WHERE id = $live_id";
    $live_result = mysqli_query($conn, $live_query);
    $current_live = mysqli_fetch_assoc($live_result);
}

// Get my live sessions
$my_live_sessions_query = "SELECT * FROM live_stream WHERE embed_code LIKE '%\"reporter_id\":\"$reporter_id\"%' ORDER BY created_at DESC";
$my_live_sessions = mysqli_query($conn, $my_live_sessions_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Reporting - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .reporter-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .reporter-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .reporter-sidebar .nav-link:hover,
        .reporter-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .reporter-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .live-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #ff0000;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .live-blog-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .live-update {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
        }
        .live-update-time {
            color: #dc3545;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .start-live-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #28a745;
        }
        .live-session-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .live-session-card:hover {
            transform: translateY(-2px);
        }
        .status-online {
            color: #28a745;
            font-weight: bold;
        }
        .status-offline {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block reporter-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Reporter Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_news.php">
                                <i class="fas fa-newspaper me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link bg-danger text-white" href="breaking_news_reporter.php">
                                <i class="fas fa-bolt me-2"></i>Breaking News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="live_reporter.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 reporter-main-content">
                <!-- Header -->
                <div class="reporter-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                        </h1>
                        <small>Provide real-time updates on ongoing events</small>
                    </div>
                    <div>
                        <?php if ($current_live && $current_live['status'] === 'online'): ?>
                            <span class="live-indicator"></span>
                            <span class="status-online">LIVE NOW</span>
                        <?php else: ?>
                            <span class="status-offline">OFFLINE</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($current_live && $current_live['status'] === 'online'): ?>
                    <!-- Active Live Session -->
                    <div class="live-blog-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3>
                                    <span class="live-indicator"></span>
                                    <?php echo htmlspecialchars($current_live['title']); ?>
                                </h3>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($current_live['description']); ?></p>
                            </div>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="end_live">
                                <input type="hidden" name="live_id" value="<?php echo $current_live['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('End live session?')">
                                    <i class="fas fa-stop me-2"></i>End Live Session
                                </button>
                            </form>
                        </div>

                        <!-- Add Update Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Add Live Update</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_update">
                                    <input type="hidden" name="live_id" value="<?php echo $current_live['id']; ?>">
                                    <div class="mb-3">
                                        <textarea name="update_text" class="form-control" rows="3" 
                                                  placeholder="What's happening now? Provide latest update..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-2"></i>Post Update
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Live Updates -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Live Updates Timeline</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $embed_code = json_decode($current_live['embed_code'], true);
                                $updates = $embed_code['updates'] ?? [];
                                
                                if (!empty($updates)): ?>
                                    <?php foreach (array_reverse($updates) as $update): ?>
                                        <div class="live-update">
                                            <div class="live-update-time mb-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('h:i A', strtotime($update['timestamp'])); ?> - 
                                                <?php echo date('M d, Y', strtotime($update['timestamp'])); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong><?php echo htmlspecialchars($update['reporter']); ?></strong>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($update['text'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-comments fa-3x mb-3"></i>
                                        <p>No updates yet. Start posting updates above.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Start New Live Session -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="start-live-form">
                                <h4 class="mb-4">
                                    <i class="fas fa-play-circle text-success me-2"></i>Start Live Session
                                </h4>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="start_live">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Event Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control form-control-lg" 
                                               placeholder="E.g., Election Results 2024 - Live Updates" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Event Type</label>
                                        <select name="event_type" class="form-select" required>
                                            <option value="">Select Event Type</option>
                                            <option value="election">Election/Politics</option>
                                            <option value="sports">Sports Match</option>
                                            <option value="breaking">Breaking News</option>
                                            <option value="conference">Press Conference</option>
                                            <option value="disaster">Natural Disaster</option>
                                            <option value="ceremony">Award Ceremony</option>
                                            <option value="protest">Protest/Demonstration</option>
                                            <option value="other">Other Event</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="Brief description of the event..."></textarea>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-broadcast-tower me-2"></i>START LIVE SESSION
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Recent Live Sessions -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">My Recent Live Sessions</h5>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (mysqli_num_rows($my_live_sessions) > 0): ?>
                                        <?php while ($session = mysqli_fetch_assoc($my_live_sessions)): ?>
                                            <div class="live-session-card">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0">
                                                        <a href="live_reporter.php?live_id=<?php echo $session['id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($session['title']); ?>
                                                        </a>
                                                    </h6>
                                                    <span class="badge <?php echo $session['status'] === 'online' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo strtoupper($session['status']); ?>
                                                    </span>
                                                </div>
                                                
                                                <p class="text-muted small mb-2">
                                                    <?php echo htmlspecialchars(substr($session['description'], 0, 100)) . '...'; ?>
                                                </p>
                                                
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Started: <?php echo date('M d, Y h:i A', strtotime($session['created_at'])); ?>
                                                    <?php if ($session['end_time']): ?>
                                                        <br><i class="fas fa-stop me-1"></i>
                                                        Ended: <?php echo date('M d, Y h:i A', strtotime($session['end_time'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($session['status'] === 'online'): ?>
                                                    <div class="mt-2">
                                                        <a href="live_reporter.php?live_id=<?php echo $session['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-play me-1"></i>Continue Session
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No live sessions yet</h5>
                                            <p class="text-muted">Start your first live reporting session</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Reporting Guidelines -->
                    <div class="card mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Live Reporting Guidelines
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-check text-success me-2"></i>Best for live reporting:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-angle-right me-2"></i>Election day coverage</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Sports matches and tournaments</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Breaking news situations</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Press conferences</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Court verdicts</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Tips for effective live coverage:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-angle-right me-2"></i>Post frequent, short updates</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Include timestamps for context</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Verify information before posting</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Engage with audience questions</li>
                                        <li><i class="fas fa-angle-right me-2"></i>Provide context and background</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($current_live && $current_live['status'] === 'online'): ?>
        <script>
            // Auto-refresh live updates every 30 seconds
            setInterval(function() {
                window.location.reload();
            }, 30000);
        </script>
    <?php endif; ?>
</body>
</html>
