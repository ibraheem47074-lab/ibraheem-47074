<?php
// Check if additional features are installed and get their stats
$tags_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'tags'")) > 0;
$bookmarks_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")) > 0;
$analytics_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'news_analytics'")) > 0;
$notifications_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'notifications'")) > 0;
$criteria_tables_exist = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'deployment_criteria'")) > 0;
$polls_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'polls'")) > 0;
$ads_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'")) > 0;
$users_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'users'")) > 0;
$comments_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'comments'")) > 0;
$categories_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'categories'")) > 0;
$editions_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'editions'")) > 0;
$weather_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'weather_data'")) > 0;
$rss_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'rss_feeds'")) > 0;
$ai_images_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'ai_images'")) > 0;
$traffic_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'traffic_analysis'")) > 0;
$events_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'events'")) > 0;
?>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
            <small>Admin Panel</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-news.php' ? 'active' : ''; ?>" href="manage-news.php">
                    <i class="fas fa-newspaper me-2"></i>Manage News
                </a>
            </li>
            
            <?php if ($tags_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-tags.php' ? 'active' : ''; ?>" href="manage-tags.php">
                    <i class="fas fa-tags me-2"></i>Manage Tags
                </a>
            </li>
           <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-categories.php' ? 'active' : ''; ?>" href="manage-categories.php">
                    <i class="fas fa-folder me-2"></i>Categories
                </a>
            </li>
            <?php if ($bookmarks_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-bookmarks.php' ? 'active' : ''; ?>" href="manage-bookmarks.php">
                    <i class="fas fa-bookmark me-2"></i>Bookmarks
                </a>
            </li>
            <?php endif; ?>
            <?php if ($analytics_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                    <i class="fas fa-chart-line me-2"></i>Analytics
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'sentiment-dashboard.php' ? 'active' : ''; ?>" href="sentiment-dashboard.php">
                    <i class="fas fa-brain me-2"></i>Sentiment Analysis
                </a>
            </li>
            <?php if ($notifications_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-notifications.php' ? 'active' : ''; ?>" href="manage-notifications.php">
                    <i class="fas fa-bell me-2"></i>Notifications
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'live-stream.php' ? 'active' : ''; ?>" href="live-stream.php">
                    <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                </a>
            </li>
            <?php if ($events_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-events.php' ? 'active' : ''; ?>" href="manage-events.php">
                    <i class="fas fa-calendar-alt me-2"></i>Events
                </a>
            </li>
            <?php endif; ?>
            
           
            <!-- Advertisement Management -->
            <?php if ($ads_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-ads.php' ? 'active' : ''; ?>" href="manage-ads.php">
                    <i class="fas fa-ad me-2"></i>Advertisements
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Content Management Section -->
            <?php if ($rss_table_exists): ?>
            <li class="nav-item mt-3">
                <h6 class="px-3 text-muted small">CONTENT MANAGEMENT</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-rss.php' ? 'active' : ''; ?>" href="manage-rss.php">
                    <i class="fas fa-rss me-2"></i>RSS Feeds
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($ai_images_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-ai-images.php' ? 'active' : ''; ?>" href="manage-ai-images.php">
                    <i class="fas fa-robot me-2"></i>AI Images
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($weather_table_exists): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'weather-settings.php' ? 'active' : ''; ?>" href="weather-settings.php">
                    <i class="fas fa-cloud-sun me-2"></i>Weather Settings
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Analytics Section -->
            <?php if ($traffic_table_exists): ?>
            <li class="nav-item mt-3">
                <h6 class="px-3 text-muted small">ANALYTICS</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'traffic-analysis.php' ? 'active' : ''; ?>" href="traffic-analysis.php">
                    <i class="fas fa-chart-bar me-2"></i>Traffic Analysis
                </a>
            </li>
            <?php endif; ?>
            
            <!-- User Management Section -->
            <?php if ($users_table_exists): ?>
            <li class="nav-item mt-3">
                <h6 class="px-3 text-muted small">USER MANAGEMENT</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage-users.php' ? 'active' : ''; ?>" href="manage-users.php">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
