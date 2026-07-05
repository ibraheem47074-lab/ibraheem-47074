<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Get dashboard statistics
$total_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM categories"))['count'];
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'];

// Get role applications statistics
$role_applications_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'role_applications'")) > 0;
$total_role_applications = $role_applications_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM role_applications"))['count'] : 0;
$pending_role_applications = $role_applications_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM role_applications WHERE status = 'pending'"))['count'] : 0;

// Check if additional features are installed and get their stats
$tags_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'tags'")) > 0;
$bookmarks_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")) > 0;
$analytics_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'news_analytics'")) > 0;
$notifications_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'notifications'")) > 0;

// Get additional stats if tables exist
$total_tags = $tags_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tags"))['count'] : 0;
$total_bookmarks = $bookmarks_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookmarks"))['count'] : 0;
$total_analytics_views = $analytics_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news_analytics"))['total'] : 0;
$total_notifications = $notifications_table_exists ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications WHERE is_read = FALSE"))['count'] : 0;

// Get recent news
$recent_news_query = "SELECT n.*, c.name as category_name, u.name as author_name 
                     FROM news n 
                     LEFT JOIN categories c ON n.category_id = c.id 
                     LEFT JOIN users u ON n.author_id = u.id 
                     ORDER BY n.created_at DESC LIMIT 5";
$recent_news = mysqli_query($conn, $recent_news_query);

// Get live stream status
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream ORDER BY id DESC LIMIT 1"));

// Get popular news (most viewed)
$popular_news_query = "SELECT n.*, c.name as category_name 
                      FROM news n 
                      LEFT JOIN categories c ON n.category_id = c.id 
                      WHERE n.status = 'published' 
                      ORDER BY n.views DESC LIMIT 5";
$popular_news = mysqli_query($conn, $popular_news_query);

// Get recent comments
$recent_comments_query = "SELECT cm.*, n.title as news_title 
                         FROM comments cm 
                         LEFT JOIN news n ON cm.news_id = n.id 
                         ORDER BY cm.created_at DESC LIMIT 5";
$recent_comments = mysqli_query($conn, $recent_comments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PK Live News Admin</title>
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
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics-dashboard.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="performance-analytics.php">
                                <i class="fas fa-chart-bar me-2"></i>Performance Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sentiment-dashboard.php">
                                <i class="fas fa-brain me-2"></i>Sentiment Analysis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../architecture.php">
                                <i class="fas fa-sitemap me-2"></i>System Architecture
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link bg-danger text-white" href="breaking-news-alerts.php">
                                <i class="fas fa-bell me-2"></i>Breaking News Alerts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_role_applications.php">
                                <i class="fas fa-user-tie me-2"></i>Role Applications
                                <?php if ($pending_role_applications > 0): ?>
                                    <span class="badge bg-danger ms-auto"><?php echo $pending_role_applications; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_permissions.php">
                                <i class="fas fa-users-cog me-2"></i>Permissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-events.php">
                                <i class="fas fa-calendar-alt me-2"></i>Manage Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="website_control.php">
                                <i class="fas fa-cogs me-2"></i>Website Control
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
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="live-indicator <?php echo $live_stream && $live_stream['status'] == 'online' ? 'live-online' : 'live-offline'; ?>"></span>
                        <span class="me-3">Live Stream: <?php echo $live_stream && $live_stream['status'] == 'online' ? 'ON AIR' : 'OFFLINE'; ?></span>
                                                <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $total_news; ?></h3>
                                <p class="text-muted mb-0">Total News Articles</p>
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
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-categories.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-info text-white">
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
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3><?php echo $total_comments; ?></h3>
                                <p class="text-muted mb-0">Comments</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="site-settings.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-secondary text-white">
                                    <i class="fas fa-sliders-h"></i>
                                </div>
                                <h3><i class="fas fa-cog"></i></h3>
                                <p class="text-muted mb-0">Site Settings</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="backup_database.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-dark text-white">
                                    <i class="fas fa-database"></i>
                                </div>
                                <h3><i class="fas fa-download"></i></h3>
                                <p class="text-muted mb-0">Database Backup</p>
                            </div>
                        </a>
                    </div>
                    <?php if ($role_applications_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage_role_applications.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <h3><?php echo $total_role_applications; ?></h3>
                                <p class="text-muted mb-0">Role Applications
                                    <?php if ($pending_role_applications > 0): ?>
                                        <span class="badge bg-danger ms-1"><?php echo $pending_role_applications; ?> pending</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="website_control.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-gradient" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h3><i class="fas fa-rocket"></i></h3>
                                <p class="text-muted mb-0">Website Control</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Additional Features Stats -->
                <?php if ($tags_table_exists || $bookmarks_table_exists || $analytics_table_exists || $notifications_table_exists): ?>
                <div class="row mb-4">
                    <h5 class="mb-3"><i class="fas fa-puzzle-piece me-2"></i>Additional Features</h5>
                    
                    <?php if ($tags_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-tags.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-info text-white">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <h3><?php echo $total_tags; ?></h3>
                                <p class="text-muted mb-0">Tags</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($bookmarks_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-bookmarks.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-secondary text-white">
                                    <i class="fas fa-bookmark"></i>
                                </div>
                                <h3><?php echo $total_bookmarks; ?></h3>
                                <p class="text-muted mb-0">Bookmarks</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($analytics_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="analytics.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3><?php echo number_format($total_analytics_views ?: 0 ?? 0); ?></h3>
                                <p class="text-muted mb-0">Analytics Views</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($notifications_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-notifications.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h3><?php echo $total_notifications; ?></h3>
                                <p class="text-muted mb-0">Unread Notifications</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Install Features Banner -->
                <?php if (!$tags_table_exists || !$bookmarks_table_exists || !$analytics_table_exists || !$notifications_table_exists): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-rocket me-2"></i>Enhance Your System!</h5>
                            <p class="mb-3">Install additional features to improve user engagement and analytics:</p>
                            <div class="row">
                                <?php if (!$tags_table_exists): ?>
                                <div class="col-md-3 mb-2">
                                    <a href="install_tags_simple.php" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-tags me-1"></i>Install Tags
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!$bookmarks_table_exists): ?>
                                <div class="col-md-3 mb-2">
                                    <a href="install_bookmarks_simple.php" class="btn btn-outline-warning btn-sm w-100">
                                        <i class="fas fa-bookmark me-1"></i>Install Bookmarks
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!$analytics_table_exists): ?>
                                <div class="col-md-3 mb-2">
                                    <a href="install_analytics_simple.php" class="btn btn-outline-success btn-sm w-100">
                                        <i class="fas fa-chart-line me-1"></i>Install Analytics
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!$notifications_table_exists): ?>
                                <div class="col-md-3 mb-2">
                                    <a href="install_notifications_simple.php" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-bell me-1"></i>Install Notifications
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <a href="install_all_features.php" class="btn btn-primary">
                                    <i class="fas fa-cogs me-2"></i>Feature Installation Center
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Recent News -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Recent News</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
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
                                                            <?php echo htmlspecialchars(substr($news['title'] ?? '', 0, 50)) . '...'; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($news['category_name'] ?? ''); ?></span>
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

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Popular News -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-fire me-2"></i>Popular News</h6>
                            </div>
                            <div class="card-body">
                                <?php while ($popular = mysqli_fetch_assoc($popular_news)): ?>
                                    <div class="d-flex mb-3">
                                        <div class="flex-grow-1">
                                            <a href="../news.php?slug=<?php echo $popular['slug']; ?>" class="text-decoration-none">
                                                <h6 class="mb-1"><?php echo htmlspecialchars(substr($popular['title'] ?? '', 0, 40)) . '...'; ?></h6>
                                            </a>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($popular['category_name'] ?? ''); ?> • 
                                                <?php echo $popular['views']; ?> views
                                            </small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- Recent Comments -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Comments</h6>
                            </div>
                            <div class="card-body">
                                <?php while ($comment = mysqli_fetch_assoc($recent_comments)): ?>
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($comment['name'] ?? ''); ?></strong>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1 small"><?php echo htmlspecialchars(substr($comment['comment'] ?? '', 0, 80)) . '...'; ?></p>
                                        <small class="text-muted">On: <?php echo htmlspecialchars(substr($comment['news_title'] ?? '', 0, 30)) . '...'; ?></small>
                                    </div>
                                <?php endwhile; ?>
                                <div class="text-center mt-3">
                                    <a href="manage-comments.php" class="btn btn-sm btn-outline-primary">Manage Comments</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
