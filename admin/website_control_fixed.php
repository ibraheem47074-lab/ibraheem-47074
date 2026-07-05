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

// Handle form submissions (same as original)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // System Maintenance Controls
    if (isset($_POST['maintenance_control'])) {
        $action = clean_input($_POST['maintenance_action']);
        
        switch ($action) {
            case 'enable_maintenance':
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
    'rss_imported' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import'"))['count'],
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
$activity_query = "SELECT 'news' as type, id, title, created_at, status, news_type FROM news 
                   UNION ALL 
                   SELECT 'comment' as type, id, comment as title, created_at, status, 'manual' as news_type FROM comments 
                   ORDER BY created_at DESC LIMIT 10";
$activity_result = mysqli_query($conn, $activity_query);
while ($row = mysqli_fetch_assoc($activity_result)) {
    $recent_activity[] = $row;
}

// Get recent RSS imports for content management
$rss_news_query = "SELECT id, title, created_at, status, news_type FROM news 
                   WHERE news_type = 'rss_import' 
                   ORDER BY created_at DESC LIMIT 20";
$rss_news_result = mysqli_query($conn, $rss_news_query);

// Get draft news for bulk operations (original functionality)
$draft_news_query = "SELECT id, title, created_at FROM news WHERE status = 'draft' ORDER BY created_at DESC LIMIT 20";
$draft_news_result = mysqli_query($conn, $draft_news_query);

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
        .rss-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
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
        .control-btn {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="control-panel">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-cogs me-3"></i>Website Control Center</h1>
                    <p class="mb-0">Complete control over your PK Live News website - system maintenance, content management, and settings.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="system-status status-online"></span>
                    <strong>System Online</strong>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $system_stats['total_news']; ?></h3>
                    <p class="mb-0">Total News Articles</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $system_stats['published_news']; ?></h3>
                    <p class="mb-0">Published Articles</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $system_stats['rss_imported']; ?></h3>
                    <p class="mb-0">RSS Imported</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $system_stats['total_users']; ?></h3>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-4">
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
                            <td>Database Size:</td>
                            <td><strong><?php echo $system_stats['database_size']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>RSS Imported:</td>
                            <td><strong><?php echo $system_stats['rss_imported']; ?> articles</strong></td>
                        </tr>
                    </table>
                </div>

                <!-- Recent Activity -->
                <div class="control-card">
                    <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                    <div class="activity-list" style="max-height: 300px; overflow-y: auto;">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <strong><?php echo htmlspecialchars(substr($activity['title'], 0, 30)) . '...'; ?></strong>
                                        <?php if ($activity['news_type'] === 'rss_import'): ?>
                                            <span class="rss-badge ms-2">RSS</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo ucfirst($activity['type']); ?> • 
                                            <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $activity['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                        <?php echo $activity['status']; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent activity</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Controls -->
            <div class="col-lg-8">
                <!-- RSS Imported Articles Management -->
                <div class="control-card">
                    <h5><i class="fas fa-rss me-2"></i>RSS Imported Articles</h5>
                    <?php if (mysqli_num_rows($rss_news_result) > 0): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="content_control" value="1">
                            <label class="form-label">Recent RSS Imports (<?php echo mysqli_num_rows($rss_news_result); ?> articles):</label>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                        <?php while ($rss = mysqli_fetch_assoc($rss_news_result)): ?>
                                            <div class="d-flex align-items-center justify-content-between py-1">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input bulk-select" type="checkbox" name="selected_news[]" value="<?php echo $rss['id']; ?>" id="rss_<?php echo $rss['id']; ?>">
                                                    <label class="form-check-label" for="rss_<?php echo $rss['id']; ?>">
                                                        <?php echo htmlspecialchars(substr($rss['title'], 0, 60)) . '...'; ?>
                                                        <span class="rss-badge ms-2">RSS</span>
                                                        <small class="text-muted">(<?php echo date('M d, H:i', strtotime($rss['created_at'])); ?>)</small>
                                                    </label>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit-news.php?id=<?php echo $rss['id']; ?>" class="btn btn-outline-primary btn-sm" title="Edit Article">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../news.php?slug=<?php echo urlencode(create_slug($rss['title'])); ?>" class="btn btn-outline-info btn-sm" title="Preview Article" target="_blank">
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
                                        <button type="submit" name="content_action" value="bulk_unpublish" 
                                                class="btn btn-warning btn-sm" onclick="return confirm('Unpublish selected RSS articles?')">
                                            <i class="fas fa-eye-slash me-1"></i>Unpublish Selected
                                        </button>
                                        <button type="submit" name="content_action" value="bulk_delete" 
                                                class="btn btn-danger btn-sm" onclick="return confirm('Delete selected RSS articles permanently?')">
                                            <i class="fas fa-trash me-1"></i>Delete Selected
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllRSS()">
                                            <i class="fas fa-check-square me-1"></i>Select All RSS
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllRSS()">
                                            <i class="fas fa-square me-1"></i>Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No RSS imported articles found. 
                            <a href="../cron_import_news.php?cron_key=pk_live_news_2024_cron" class="alert-link" target="_blank">Run RSS Import</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Draft Articles Management (Original) -->
                <div class="control-card">
                    <h5><i class="fas fa-edit me-2"></i>Draft Articles</h5>
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
                                                        <small class="text-muted">(<?php echo date('M d, Y', strtotime($draft['created_at'])); ?>)</small>
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
                </div>

                <!-- Quick Actions -->
                <div class="control-card">
                    <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="../cron_import_news.php?cron_key=pk_live_news_2024_cron" class="btn btn-success control-btn" target="_blank">
                                    <i class="fas fa-rss me-2"></i>Run RSS Import Now
                                </a>
                                <a href="manage-sources.php" class="btn btn-info control-btn">
                                    <i class="fas fa-list me-2"></i>Manage RSS Sources
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="backup_database.php" class="btn btn-primary control-btn">
                                    <i class="fas fa-download me-2"></i>Backup Database
                                </a>
                                <a href="manage-news.php" class="btn btn-secondary control-btn">
                                    <i class="fas fa-newspaper me-2"></i>Manage All News
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all RSS articles
        function selectAllRSS() {
            document.querySelectorAll('input[type="checkbox"][id^="rss_"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        // Deselect all RSS articles
        function deselectAllRSS() {
            document.querySelectorAll('input[type="checkbox"][id^="rss_"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Select all draft articles
        function selectAllDrafts() {
            document.querySelectorAll('input[type="checkbox"][id^="draft_"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        // Deselect all draft articles
        function deselectAllDrafts() {
            document.querySelectorAll('input[type="checkbox"][id^="draft_"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Auto-refresh page every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
