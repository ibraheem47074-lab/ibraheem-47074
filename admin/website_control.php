<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Initialize variables
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // System Maintenance Controls
    if (isset($_POST['maintenance_control'])) {
        $action = clean_input($_POST['maintenance_action']);
        
        switch ($action) {
            case 'enable_maintenance':
                // Create maintenance mode file
                $maintenance_file = '../maintenance.html';
                $maintenance_content = '<!DOCTYPE html>
<html>
<head>
    <title>Site Under Maintenance</title>
    <style>
        body { font-family: Arial; text-align: center; margin-top: 100px; }
        .maintenance { max-width: 600px; margin: 0 auto; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="maintenance">
        <h1>🔧 Site Under Maintenance</h1>
        <div class="spinner"></div>
        <p>We are currently performing scheduled maintenance. Please check back in a few minutes.</p>
        <p>Thank you for your patience!</p>
    </div>
</body>
</html>';
                
                if (file_put_contents($maintenance_file, $maintenance_content)) {
                    $success = "Maintenance mode enabled successfully!";
                } else {
                    $error = "Failed to enable maintenance mode!";
                }
                break;
                
            case 'disable_maintenance':
                if (unlink('../maintenance.html')) {
                    $success = "Maintenance mode disabled successfully!";
                } else {
                    $error = "Failed to disable maintenance mode!";
                }
                break;
                
            case 'clear_cache':
                $cache_dir = '../cache/';
                $cache_cleared = true;
                
                if (is_dir($cache_dir)) {
                    $files = glob($cache_dir . '*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            if (!unlink($file)) {
                                $cache_cleared = false;
                            }
                        }
                    }
                }
                
                if ($cache_cleared) {
                    $success = "Cache cleared successfully!";
                } else {
                    $error = "Some cache files could not be cleared!";
                }
                break;
                
            case 'optimize_database':
                $tables = ['news', 'users', 'categories', 'comments', 'settings'];
                $optimized = 0;
                
                foreach ($tables as $table) {
                    $result = mysqli_query($conn, "OPTIMIZE TABLE $table");
                    if ($result) {
                        $optimized++;
                    }
                }
                
                $success = "Optimized $optimized database tables successfully!";
                break;
        }
    }
    
    // Content Management Controls
    if (isset($_POST['content_control'])) {
        $action = clean_input($_POST['content_action']);
        
        switch ($action) {
            case 'bulk_publish':
                if (isset($_POST['selected_news']) && !empty($_POST['selected_news'])) {
                    $news_ids = array_map('intval', $_POST['selected_news']);
                    $ids_string = implode(',', $news_ids);
                    
                    $query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Published $affected news articles successfully!";
                    } else {
                        $error = "Failed to publish news articles!";
                    }
                }
                break;
                
            case 'bulk_unpublish':
                if (isset($_POST['selected_news']) && !empty($_POST['selected_news'])) {
                    $news_ids = array_map('intval', $_POST['selected_news']);
                    $ids_string = implode(',', $news_ids);
                    
                    $query = "UPDATE news SET status = 'draft' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Unpublished $affected news articles successfully!";
                    } else {
                        $error = "Failed to unpublish news articles!";
                    }
                }
                break;
                
            case 'bulk_delete':
                if (isset($_POST['selected_news']) && !empty($_POST['selected_news'])) {
                    $news_ids = array_map('intval', $_POST['selected_news']);
                    $ids_string = implode(',', $news_ids);
                    
                    // Delete related comments first
                    mysqli_query($conn, "DELETE FROM comments WHERE news_id IN ($ids_string)");
                    // Delete related likes
                    mysqli_query($conn, "DELETE FROM post_likes WHERE news_id IN ($ids_string)");
                    // Delete news articles
                    $query = "DELETE FROM news WHERE id IN ($ids_string)";
                    
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Deleted $affected news articles successfully!";
                    } else {
                        $error = "Failed to delete news articles!";
                    }
                }
                break;
        }
    }
    
    // System Settings Controls
    if (isset($_POST['system_settings'])) {
        $site_status = clean_input($_POST['site_status']);
        $comment_system = isset($_POST['enable_comments']) ? 1 : 0;
        $user_registration = isset($_POST['enable_registration']) ? 1 : 0;
        $email_notifications = isset($_POST['enable_email_notifications']) ? 1 : 0;
        
        // Update settings in database
        $settings = [
            'site_status' => $site_status,
            'enable_comments' => $comment_system,
            'enable_registration' => $user_registration,
            'enable_email_notifications' => $email_notifications
        ];
        
        foreach ($settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "System settings updated successfully!";
    }
}

// Get system statistics
$system_stats = [
    'total_news' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'],
    'published_news' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'"))['count'],
    'draft_news' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'draft'"))['count'],
    'total_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'total_comments' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'],
    'pending_comments' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending'"))['count'],
    'database_size' => get_database_size($conn),
    'disk_usage' => get_disk_usage('../'),
    'memory_usage' => memory_get_usage(true),
    'php_version' => PHP_VERSION,
    'mysql_version' => mysqli_get_server_info($conn),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
];

// Get recent activity
$recent_activity = [];
$activity_query = "SELECT 'news' as type, id, title COLLATE utf8mb4_unicode_ci as title, created_at, status COLLATE utf8mb4_unicode_ci as status FROM news 
                   UNION ALL 
                   SELECT 'comment' as type, id, comment COLLATE utf8mb4_unicode_ci as title, created_at, status COLLATE utf8mb4_unicode_ci as status FROM comments 
                   ORDER BY created_at DESC LIMIT 10";
$activity_result = mysqli_query($conn, $activity_query);
while ($row = mysqli_fetch_assoc($activity_result)) {
    $recent_activity[] = $row;
}

// Get draft news for bulk operations
$draft_news_query = "SELECT id, title, created_at FROM news WHERE status = 'draft' ORDER BY created_at DESC LIMIT 20";
$draft_news_result = mysqli_query($conn, $draft_news_query);

// Check if settings table exists, create it if not
$settings_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($settings_check) == 0) {
    $create_settings_sql = "CREATE TABLE `settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `description` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_settings_sql);
    
    // Insert default settings
    $default_settings = [
        'site_name' => 'PK Live News',
        'site_description' => 'Latest news from Pakistan',
        'site_keywords' => 'news, pakistan, breaking news',
        'contact_email' => 'contact@pklivenews.com',
        'facebook_url' => 'https://facebook.com/pklivenews',
        'twitter_url' => 'https://twitter.com/pklivenews',
        'youtube_url' => 'https://youtube.com/pklivenews',
        'instagram_url' => 'https://instagram.com/pklivenews',
        'enable_comments' => '1',
        'enable_rss' => '1',
        'enable_weather' => '1',
        'enable_live_tv' => '1',
        'news_per_page' => '10',
        'enable_ads' => '1',
        'maintenance_mode' => '0'
    ];
    
    foreach ($default_settings as $key => $value) {
        $stmt = mysqli_prepare($conn, "INSERT INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        $description = ucfirst(str_replace('_', ' ', $key));
        mysqli_stmt_bind_param($stmt, "sss", $key, $value, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Get current system settings
$settings_query = "SELECT setting_key, setting_value FROM settings";
$settings_result = mysqli_query($conn, $settings_query);
$current_settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Helper functions
function get_database_size($conn) {
    $result = mysqli_query($conn, "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = DATABASE()");
    $size = mysqli_fetch_assoc($result)['size'];
    return format_bytes($size);
}

function get_disk_usage($path) {
    $total_space = disk_total_space($path);
    $free_space = disk_free_space($path);
    $used_space = $total_space - $free_space;
    
    return [
        'total' => format_bytes($total_space),
        'used' => format_bytes($used_space),
        'free' => format_bytes($free_space),
        'percentage' => round(($used_space / $total_space) * 100, 2)
    ];
}

function format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Helper functions are already included in config/database.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Control Center - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .control-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .control-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        .control-card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .system-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; }
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
        }
        .control-btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }
        .bulk-select {
            cursor: pointer;
        }
        .maintenance-mode {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Control Panel Header -->
        <div class="control-panel">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-cogs me-3"></i>Website Control Center</h1>
                    <p class="lead mb-0">Complete control over your website functionality, content, and system settings</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <span class="system-status status-online"></span>
                        <span class="me-3">System Online</span>
                        <button class="btn btn-light btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- System Overview -->
            <div class="col-lg-4">
                <div class="control-card">
                    <h5><i class="fas fa-chart-pie me-2"></i>System Overview</h5>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="stat-card">
                                <h3><?php echo $system_stats['total_news']; ?></h3>
                                <small>Total Articles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <h3><?php echo $system_stats['total_users']; ?></h3>
                                <small>Users</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <h3><?php echo $system_stats['total_comments']; ?></h3>
                                <small>Comments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <h3><?php echo $system_stats['published_news']; ?></h3>
                                <small>Published</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Server Information -->
                <div class="control-card">
                    <h5><i class="fas fa-server me-2"></i>Server Information</h5>
                    <table class="table table-sm">
                        <tr>
                            <td>PHP Version:</td>
                            <td><strong><?php echo $system_stats['php_version']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>MySQL Version:</td>
                            <td><strong><?php echo $system_stats['mysql_version']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Server:</td>
                            <td><strong><?php echo $system_stats['server_software']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Database Size:</td>
                            <td><strong><?php echo $system_stats['database_size']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Memory Usage:</td>
                            <td><strong><?php echo format_bytes($system_stats['memory_usage']); ?></strong></td>
                        </tr>
                    </table>
                </div>

                <!-- Disk Usage -->
                <div class="control-card">
                    <h5><i class="fas fa-hdd me-2"></i>Disk Usage</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Used: <?php echo $system_stats['disk_usage']['used']; ?></span>
                            <span><?php echo $system_stats['disk_usage']['percentage']; ?>%</span>
                        </div>
                        <div class="progress progress-bar-custom">
                            <div class="progress-bar bg-<?php echo $system_stats['disk_usage']['percentage'] > 80 ? 'danger' : 'primary'; ?>" 
                                 style="width: <?php echo $system_stats['disk_usage']['percentage']; ?>%"></div>
                        </div>
                        <small class="text-muted">Total: <?php echo $system_stats['disk_usage']['total']; ?> | Free: <?php echo $system_stats['disk_usage']['free']; ?></small>
                    </div>
                </div>
            </div>

            <!-- Main Controls -->
            <div class="col-lg-8">
                <!-- System Maintenance Controls -->
                <div class="control-card">
                    <h5><i class="fas fa-tools me-2"></i>System Maintenance</h5>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="maintenance_control" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maintenance Actions:</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="maintenance_action" value="enable_maintenance" 
                                            class="btn btn-warning control-btn" onclick="return confirm('Enable maintenance mode? This will make your site inaccessible to visitors.')">
                                        <i class="fas fa-wrench me-2"></i>Enable Maintenance Mode
                                    </button>
                                    <button type="submit" name="maintenance_action" value="disable_maintenance" 
                                            class="btn btn-success control-btn">
                                        <i class="fas fa-play me-2"></i>Disable Maintenance Mode
                                    </button>
                                    <button type="submit" name="maintenance_action" value="clear_cache" 
                                            class="btn btn-info control-btn">
                                        <i class="fas fa-broom me-2"></i>Clear Cache
                                    </button>
                                    <button type="submit" name="maintenance_action" value="optimize_database" 
                                            class="btn btn-primary control-btn">
                                        <i class="fas fa-database me-2"></i>Optimize Database
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quick Actions:</label>
                                <div class="d-grid gap-2">
                                    <a href="backup_database.php" class="btn btn-outline-primary control-btn">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </a>
                                    <a href="manage-news.php" class="btn btn-outline-info control-btn">
                                        <i class="fas fa-newspaper me-2"></i>Manage All News
                                    </a>
                                    <a href="settings.php" class="btn btn-outline-secondary control-btn">
                                        <i class="fas fa-cog me-2"></i>Advanced Settings
                                    </a>
                                    <a href="../index.php" target="_blank" class="btn btn-outline-success control-btn">
                                        <i class="fas fa-external-link-alt me-2"></i>View Website
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Content Management Controls -->
                <div class="control-card">
                    <h5><i class="fas fa-newspaper me-2"></i>Content Management</h5>
                    
                    <?php if (mysqli_num_rows($draft_news_result) > 0): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="content_control" value="1">
                            <label class="form-label">Draft Articles (<?php echo mysqli_num_rows($draft_news_result); ?> available):</label>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        <?php while ($draft = mysqli_fetch_assoc($draft_news_result)): ?>
                                            <div class="d-flex align-items-center justify-content-between py-1">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input bulk-select" type="checkbox" name="selected_news[]" value="<?php echo $draft['id']; ?>" id="draft_<?php echo $draft['id']; ?>">
                                                    <label class="form-check-label" for="draft_<?php echo $draft['id']; ?>">
                                                        <?php echo htmlspecialchars(substr($draft['title'], 0, 50)) . '...'; ?>
                                                        <small class="text-muted">(<?php 
                                                    $created_at = $draft['created_at'] ?? '';
                                                    if (empty($created_at) || $created_at === '0000-00-00 00:00:00' || strtotime($created_at) === false) {
                                                        echo 'Invalid Date';
                                                    } else {
                                                        echo date('M d, Y', strtotime($created_at));
                                                    }
                                                    ?>)</small>
                                                    </label>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit-news.php?id=<?php echo $draft['id']; ?>" class="btn btn-outline-primary btn-sm" title="Edit Article">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../news.php?slug=<?php echo urlencode(create_slug($draft['title'])); ?>" class="btn btn-outline-info btn-sm" title="Preview Article" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bulk Actions:</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="content_action" value="bulk_publish" 
                                                class="btn btn-success btn-sm" onclick="return confirm('Publish selected articles?')">
                                            <i class="fas fa-check me-1"></i>Publish Selected
                                        </button>
                                        <button type="submit" name="content_action" value="bulk_delete" 
                                                class="btn btn-danger btn-sm" onclick="return confirm('Delete selected articles permanently?')">
                                            <i class="fas fa-trash me-1"></i>Delete Selected
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllDrafts()">
                                            <i class="fas fa-check-square me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllDrafts()">
                                            <i class="fas fa-square me-1"></i>Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No draft articles found.
                        </div>
                    <?php endif; ?>

                    <!-- RSS Import Controls -->
                    <div class="mt-4 pt-4 border-top">
                        <h6><i class="fas fa-rss me-2"></i>RSS News Import</h6>
                        <p class="text-muted small">Import news articles from RSS feeds and manage automated content.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-grid gap-2">
                                    <a href="../test_rss_draft_import.php" class="btn btn-outline-warning btn-sm" target="_blank">
                                        <i class="fas fa-flask me-1"></i>Test RSS Import
                                    </a>
                                    <small class="text-muted">Test RSS feed functionality and import settings</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid gap-2">
                                    <a href="../cron_import_news.php" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-clock me-1"></i>Run Cron Import
                                    </a>
                                    <small class="text-muted">Execute automated news import from RSS sources</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="alert alert-light">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>RSS Import Status:</strong> 
                                <?php
                                // Check if there are any RSS sources configured
                                try {
                                    // First check if table exists
                                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
                                    if (mysqli_num_rows($table_check) == 0) {
                                        echo '<span class="text-warning">News sources table not found</span>';
                                    } else {
                                        $rss_sources_query = "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'";
                                        $rss_sources_result = mysqli_query($conn, $rss_sources_query);
                                        if ($rss_sources_result) {
                                            $rss_sources_count = mysqli_fetch_assoc($rss_sources_result)['count'];
                                            
                                            if ($rss_sources_count > 0) {
                                                echo "<span class='badge bg-success'>{$rss_sources_count} Active Sources</span>";
                                            } else {
                                                echo "<span class='badge bg-warning'>No Active Sources</span>";
                                            }
                                        } else {
                                            echo '<span class="text-danger">Query failed</span>';
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo '<span class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="control-card">
                    <h5><i class="fas fa-sliders-h me-2"></i>System Settings</h5>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="system_settings" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Site Status:</label>
                                    <select name="site_status" class="form-select">
                                        <option value="online" <?php echo ($current_settings['site_status'] ?? 'online') === 'online' ? 'selected' : ''; ?>>Online</option>
                                        <option value="offline" <?php echo ($current_settings['site_status'] ?? 'online') === 'offline' ? 'selected' : ''; ?>>Offline</option>
                                        <option value="maintenance" <?php echo ($current_settings['site_status'] ?? 'online') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_comments" id="enable_comments" 
                                           <?php echo ($current_settings['enable_comments'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_comments">
                                        Enable Comment System
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_registration" id="enable_registration" 
                                           <?php echo ($current_settings['enable_registration'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_registration">
                                        Enable User Registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_email_notifications" id="enable_email_notifications" 
                                           <?php echo ($current_settings['enable_email_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_email_notifications">
                                        Enable Email Notifications
                                    </label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Auto-refresh Interval (seconds):</label>
                                    <input type="number" name="auto_refresh_interval" class="form-control" 
                                           value="<?php echo $current_settings['auto_refresh_interval'] ?? 30; ?>" min="10" max="300">
                                </div>
                                <button type="submit" class="btn btn-primary control-btn w-100">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Recent Activity -->
                <div class="control-card">
                    <h5><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    <div class="mt-3">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-<?php echo $activity['type'] === 'news' ? 'newspaper' : 'comment'; ?> me-2"></i>
                                            <strong><?php echo htmlspecialchars(substr($activity['title'], 0, 40)) . '...'; ?></strong>
                                            <span class="badge bg-<?php echo $activity['type'] === 'news' ? 'primary' : 'info'; ?> ms-2">
                                                <?php echo ucfirst($activity['type']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?php 
                                            $created_at = $activity['created_at'] ?? '';
                                            if (empty($created_at) || $created_at === '0000-00-00 00:00:00' || strtotime($created_at) === false) {
                                                echo 'Invalid Date';
                                            } else {
                                                echo date('M d, H:i', strtotime($created_at));
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent activity found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh functionality
        let refreshInterval;
        
        function startAutoRefresh() {
            const interval = <?php echo $current_settings['auto_refresh_interval'] ?? 30; ?> * 1000;
            refreshInterval = setInterval(() => {
                location.reload();
            }, interval);
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Start auto-refresh on page load
        startAutoRefresh();
        
        // Stop auto-refresh when user is interacting
        document.addEventListener('click', stopAutoRefresh);
        document.addEventListener('keypress', stopAutoRefresh);
        
        // Checkbox select all functionality
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.bulk-select');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }
        
        // Select all draft articles
        function selectAllDrafts() {
            const checkboxes = document.querySelectorAll('.bulk-select');
            checkboxes.forEach(cb => cb.checked = true);
        }
        
        // Deselect all draft articles
        function deselectAllDrafts() {
            const checkboxes = document.querySelectorAll('.bulk-select');
            checkboxes.forEach(cb => cb.checked = false);
        }
        
        // Real-time status updates
        function updateSystemStatus() {
            fetch('api/system_status.php')
                .then(response => response.json())
                .then(data => {
                    // Update system status indicators
                    console.log('System status updated:', data);
                })
                .catch(error => console.error('Error updating system status:', error));
        }
        
        // Update system status every 30 seconds
        setInterval(updateSystemStatus, 30000);
    </script>
</body>
</html>
