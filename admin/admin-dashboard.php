<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Get comprehensive system statistics
$total_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$published_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'"))['count'];
$pending_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'pending'"))['count'];
$draft_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'draft'"))['count'];

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$active_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE status = 'active'"))['count'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM categories"))['count'];
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'];

// Events Statistics
$events_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
$total_events = 0;
$upcoming_events = 0;
$ongoing_events = 0;

if (mysqli_num_rows($events_tables_exist) > 0) {
    $total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'];
    $upcoming_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE status = 'upcoming'"))['count'];
    $ongoing_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE status = 'ongoing'"))['count'];
}

// Affiliate Marketing Statistics
$total_affiliate_products = 0;
$total_affiliate_clicks = 0;
$total_affiliate_conversions = 0;
$total_affiliate_categories = 0;

// Check if affiliate tables exist
$affiliate_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($affiliate_tables_exist) > 0) {
    $total_affiliate_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_products WHERE status = 'active'"))['count'];
    $total_affiliate_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories WHERE status = 'active'"))['count'];
    $total_affiliate_clicks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_clicks"))['count'];
    $total_affiliate_conversions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_clicks WHERE converted = 1"))['count'];
}

// User role distribution
$admin_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'"))['count'];
$editor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'editor'"))['count'];
$reporter_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'reporter'"))['count'];
$author_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'author'"))['count'];
$subscriber_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'subscriber'"))['count'];

// System health checks
$disk_usage = function_exists('disk_free_space') ? round((disk_total_space('.') - disk_free_space('.')) / disk_total_space('.') * 100, 2) : 0;
$memory_usage = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
$database_size = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'"))['size'] ?? 0;

// Recent activity
$recent_news_query = "SELECT n.*, c.name as category_name, u.name as author_name 
                     FROM news n 
                     LEFT JOIN categories c ON n.category_id = c.id 
                     LEFT JOIN users u ON n.author_id = u.id 
                     ORDER BY n.created_at DESC LIMIT 5";
$recent_news = mysqli_query($conn, $recent_news_query);

$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $recent_users_query);

// Live stream status
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream ORDER BY id DESC LIMIT 1"));

// Recent events (if events table exists)
$recent_events = [];
if (mysqli_num_rows($events_tables_exist) > 0) {
    $recent_events_query = "SELECT * FROM events ORDER BY created_at DESC LIMIT 5";
    $recent_events_result = mysqli_query($conn, $recent_events_query);
    while ($event = mysqli_fetch_assoc($recent_events_result)) {
        $recent_events[] = $event;
    }
}

// System logs (if exist)
$logs_exist = file_exists('../logs/cron_import.log');
$recent_log_entries = [];
if ($logs_exist) {
    $log_content = file_get_contents('../logs/cron_import.log');
    $log_lines = array_reverse(array_slice(explode("\n", $log_content), -10));
    foreach ($log_lines as $line) {
        if (!empty(trim($line))) {
            $recent_log_entries[] = htmlspecialchars(substr($line, 0, 100));
        }
    }
}

// Database tables info
$tables_query = "SELECT table_name, table_rows, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size 
                 FROM information_schema.tables 
                 WHERE table_schema = '" . DB_NAME . "' 
                 ORDER BY data_length + index_length DESC 
                 LIMIT 10";
$tables_info = mysqli_query($conn, $tables_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .system-health {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
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
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .role-admin { background-color: #dc3545; color: white; }
        .role-editor { background-color: #4834d4; color: white; }
        .role-reporter { background-color: #f39c12; color: white; }
        .role-author { background-color: #00d2d3; color: white; }
        .role-subscriber { background-color: #6c5ce7; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-crown me-2"></i>PK-Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Manage Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream-control.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream Control
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-events.php">
                                <i class="fas fa-calendar-alt me-2"></i>Manage Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="system-settings.php">
                                <i class="fas fa-cogs me-2"></i>System Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics-dashboard.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../adsense_check_web.php" target="_blank">
                                <i class="fab fa-google me-2"></i>AdSense Check
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="database-backup.php">
                                <i class="fas fa-database me-2"></i>Database Backup
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="system-logs.php">
                                <i class="fas fa-file-alt me-2"></i>System Logs
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
                        <h1 class="h3 mb-0">Admin Dashboard</h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="live-indicator <?php echo $live_stream && $live_stream['status'] == 'online' ? 'live-online' : 'live-offline'; ?>"></span>
                        <span class="me-3">Live Stream: <?php echo $live_stream && $live_stream['status'] == 'online' ? 'ON AIR' : 'OFFLINE'; ?></span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="system-settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- System Overview Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $total_news; ?></h3>
                                <p class="text-muted mb-0">Total Articles</p>
                                <small class="text-success"><?php echo $published_news; ?> published</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-users.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3><?php echo $total_users; ?></h3>
                                <p class="text-muted mb-0">Total Users</p>
                                <small class="text-info"><?php echo $active_users; ?> active</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-categories.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <h3><?php echo $total_categories; ?></h3>
                                <p class="text-muted mb-0">Categories</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-comments.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-info text-white">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3><?php echo $total_comments; ?></h3>
                                <p class="text-muted mb-0">Comments</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Events Statistics -->
                <?php if (mysqli_num_rows($events_tables_exist) > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Events Management</h5>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="manage-events.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h3><?php echo $total_events; ?></h3>
                                <p class="text-muted mb-0">Total Events</p>
                                <small class="text-info"><?php echo $upcoming_events; ?> upcoming</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="manage-events.php?status=upcoming" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $upcoming_events; ?></h3>
                                <p class="text-muted mb-0">Upcoming</p>
                                <small class="text-muted">Scheduled events</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="manage-events.php?status=ongoing" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <h3><?php echo $ongoing_events; ?></h3>
                                <p class="text-muted mb-0">Ongoing</p>
                                <small class="text-muted">Live events</small>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Affiliate Marketing Statistics -->
                <?php if (mysqli_num_rows($affiliate_tables_exist) > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Affiliate Marketing</h5>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-affiliate-products.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-box"></i>
                                </div>
                                <h3><?php echo $total_affiliate_products; ?></h3>
                                <p class="text-muted mb-0">Active Products</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-affiliate-categories.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <h3><?php echo $total_affiliate_categories; ?></h3>
                                <p class="text-muted mb-0">Product Categories</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="affiliate-analytics.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                                <h3><?php echo $total_affiliate_clicks; ?></h3>
                                <p class="text-muted mb-0">Total Clicks</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="affiliate-analytics.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3><?php echo $total_affiliate_conversions; ?></h3>
                                <p class="text-muted mb-0">Conversions</p>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- System Health & User Distribution -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4 class="text-primary"><?php echo $disk_usage; ?>%</h4>
                                            <p class="text-muted">Disk Usage</p>
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $disk_usage > 80 ? 'bg-danger' : ($disk_usage > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                                     style="width: <?php echo $disk_usage; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4 class="text-info"><?php echo $memory_usage; ?> MB</h4>
                                            <p class="text-muted">Memory Usage</p>
                                            <small class="text-muted">Current PHP memory</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4 class="text-warning"><?php echo $database_size; ?> MB</h4>
                                            <p class="text-muted">Database Size</p>
                                            <small class="text-muted">Total data size</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userDistributionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                                                        <div class="col-md-2 mb-2">
                                        <a href="manage-users.php" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-users me-1"></i>Manage Users
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="manage-channels.php" class="btn btn-danger btn-sm w-100">
                                            <i class="fas fa-tv me-1"></i>Manage Channels
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="live-stream-control.php" class="btn btn-danger btn-sm w-100">
                                            <i class="fas fa-broadcast-tower me-1"></i>Live Stream
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="manage-events.php" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-calendar-alt me-1"></i>Manage Events
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="system-settings.php" class="btn btn-warning btn-sm w-100">
                                            <i class="fas fa-cogs me-1"></i>Settings
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="database-backup.php" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-database me-1"></i>Backup DB
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="system-logs.php" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-file-alt me-1"></i>View Logs
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent News -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Recent News</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($news = mysqli_fetch_assoc($recent_news)): ?>
                                                <tr>
                                                    <td>
                                                        <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars(substr($news['title'], 0, 40)) . '...'; ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($news['author_name'] ?? ''); ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = $news['status'] == 'published' ? 'bg-success' : 
                                                                       ($news['status'] == 'draft' ? 'bg-warning' : 'bg-info');
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo ucfirst($news['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="manage-news.php" class="btn btn-primary">View All News</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Users -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Users</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                                            <?php echo ucfirst($user['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                            <?php echo ucfirst($user['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="manage-users.php" class="btn btn-primary">View All Users</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Events -->
                <?php if (!empty($recent_events)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Recent Events</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Event</th>
                                                <th>Date</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_events as $event): ?>
                                                <tr>
                                                    <td>
                                                        <a href="manage-events.php?edit=<?php echo $event['id']; ?>" class="text-decoration-none">
                                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                            <?php if (!empty($event['description'])): ?>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 50)) . '...'; ?></small>
                                                            <?php endif; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                                        <?php if ($event['event_time']): ?>
                                                            <br><small class="text-muted"><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($event['location'])): ?>
                                                            <?php echo htmlspecialchars($event['location']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Online</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_colors = [
                                                            'upcoming' => 'primary',
                                                            'ongoing' => 'success',
                                                            'completed' => 'secondary',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $status_color = $status_colors[$event['status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_color; ?>">
                                                            <?php echo ucfirst($event['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo ucfirst($event['type']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="manage-events.php" class="btn btn-success">View All Events</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Database Tables Overview -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Tables Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Table Name</th>
                                                <th>Rows</th>
                                                <th>Size (MB)</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($table = mysqli_fetch_assoc($tables_info)): ?>
                                                <tr>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($table['table_name']); ?></code>
                                                    </td>
                                                    <td><?php echo number_format($table['table_rows'] ?? 0); ?></td>
                                                    <td><?php echo $table['size']; ?></td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Healthy
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
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
        // User Distribution Chart
        const ctx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Admin', 'Editor', 'Reporter', 'Author', 'Subscriber'],
                datasets: [{
                    data: [<?php echo $admin_count; ?>, <?php echo $editor_count; ?>, <?php echo $reporter_count; ?>, <?php echo $author_count; ?>, <?php echo $subscriber_count; ?>],
                    backgroundColor: ['#dc3545', '#4834d4', '#f39c12', '#00d2d3', '#6c5ce7']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
