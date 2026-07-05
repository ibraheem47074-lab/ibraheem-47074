<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle notification operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notification'])) {
    $title = clean_input($_POST['title']);
    $message = clean_input($_POST['message']);
    $type = clean_input($_POST['type']);
    $user_id = (int)$_POST['user_id']; // Target specific user or NULL for all
    
    if (!empty($title) && !empty($message)) {
        // If user_id is 0 (All Users), set to NULL to send to all users
        $user_id = $user_id === 0 ? null : $user_id;
        
        $insert = "INSERT INTO notifications (user_id, type, title, message) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        
        // Use different binding types based on whether user_id is null
        if ($user_id === null) {
            mysqli_stmt_bind_param($stmt, 'ssss', $user_id, $type, $title, $message);
        } else {
            mysqli_stmt_bind_param($stmt, 'isss', $user_id, $type, $title, $message);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Notification created successfully!";
        } else {
            $error = "Error creating notification: " . mysqli_error($conn);
        }
    } else {
        $error = "Title and message are required fields";
    }
}

// Handle delete notification
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notification_id = $_GET['delete'];
    $delete = "DELETE FROM notifications WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, 'i', $notification_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Notification deleted successfully!";
    } else {
        $error = "Error deleting notification!";
    }
}

// Handle mark as read/unread
if (isset($_GET['toggle']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notification_id = $_GET['id'];
    $new_status = $_GET['toggle'] === 'read' ? 1 : 0;
    
    $update = "UPDATE notifications SET is_read = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, 'ii', $new_status, $notification_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Notification status updated!";
    } else {
        $error = "Error updating notification status!";
    }
}

// Check if notifications table exists
$table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'notifications'")) > 0;

// Get notifications (only if table exists)
$notifications_result = null;
if ($table_exists) {
    $notifications_query = "SELECT n.*, 
                         CASE 
                             WHEN n.user_id IS NULL THEN 'System'
                             ELSE u.name 
                         END as user_name 
                         FROM notifications n 
                         LEFT JOIN users u ON n.user_id = u.id 
                         ORDER BY n.created_at DESC";
    $notifications_result = mysqli_query($conn, $notifications_query);
}

// Get statistics
$stats = [];
if ($table_exists) {
    $stats_query = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread,
        COUNT(CASE WHEN is_read = 1 THEN 1 END) as read_notifications,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today
        FROM notifications";
    $stats = mysqli_query($conn, $stats_query)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications - PK Live News Admin</title>
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
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: bold;
        }
        .notification-item {
            border-left: 4px solid #dee2e6;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        .notification-item.news {
            border-left-color: #007bff;
        }
        .notification-item.comment {
            border-left-color: #17a2b8;
        }
        .notification-item.system {
            border-left-color: #ffc107;
        }
        .notification-item.reminder {
            border-left-color: #6f42c1;
        }
        .notification-item.promotion {
            border-left-color: #e83e8c;
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
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-tags.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage-notifications.php">
                                <i class="fas fa-bell me-2"></i>Notifications
                                <span class="notification-badge">
                                    <?php echo $stats['unread'] ?? 0; ?>
                                </span>
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
                        <h1 class="h3 mb-0">Manage Notifications</h1>
                        <small>Create and manage system notifications</small>
                    </div>
                    <div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                            <i class="fas fa-plus me-2"></i>Add Notification
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h4>
                                        <small>Total Notifications</small>
                                    </div>
                                    <i class="fas fa-bell fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['unread'] ?? 0; ?></h4>
                                        <small>Unread</small>
                                    </div>
                                    <i class="fas fa-envelope fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['read_notifications'] ?? 0; ?></h4>
                                        <small>Read</small>
                                    </div>
                                    <i class="fas fa-envelope-open fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['today'] ?? 0; ?></h4>
                                        <small>Today</small>
                                    </div>
                                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <?php if ($table_exists): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bell me-2"></i>Notifications</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($notifications_result && mysqli_num_rows($notifications_result) > 0): ?>
                                <?php while ($notification = mysqli_fetch_assoc($notifications_result)): ?>
                                    <div class="notification-item <?php echo $notification['type']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="mb-0 me-3"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <span class="badge bg-<?php echo $notification['type'] === 'news' ? 'primary' : ($notification['type'] === 'comment' ? 'info' : ($notification['type'] === 'system' ? 'warning' : 'secondary')); ?>">
                                                        <?php echo ucfirst($notification['type']); ?>
                                                    </span>
                                                    <span class="badge bg-<?php echo $notification['is_read'] ? 'success' : 'danger'; ?> ms-2">
                                                        <?php echo $notification['is_read'] ? 'Read' : 'Unread'; ?>
                                                    </span>
                                                </div>
                                                <p class="text-muted mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?php echo htmlspecialchars($notification['user_name'] ?? 'System'); ?>
                                                        <i class="fas fa-clock ms-3 me-1"></i>
                                                        <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                                        <?php if (is_null($notification['user_id'])): ?>
                                                            <span class="badge bg-info ms-2">All Users</span>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <div class="btn-group" role="group">
                                                    <?php if ($notification['is_read'] == 0): ?>
                                                        <a href="?toggle=read&id=<?php echo $notification['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success" title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="?toggle=unread&id=<?php echo $notification['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning" title="Mark as Unread">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?delete=<?php echo $notification['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this notification?')"
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No notifications found</h5>
                                    <p class="text-muted">Create your first notification to get started!</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                                        <i class="fas fa-plus me-2"></i>Create Notification
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Installation Required -->
                    <div class="card border-warning">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h4 class="text-warning">Notifications Table Not Found</h4>
                            <p class="text-muted mb-4">
                                The notifications table needs to be installed before you can manage notifications.
                                This table is required for the notification system functionality.
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="install_notifications_simple.php" class="btn btn-warning">
                                    <i class="fas fa-database me-2"></i>
                                    Install Notifications Table
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add Notification Modal -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_notification" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Target User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="0">All Users</option>
                                <?php
                                $users_query = "SELECT id, name FROM users WHERE status = 'active' ORDER BY name ASC";
                                $users_result = mysqli_query($conn, $users_query);
                                while ($user = mysqli_fetch_assoc($users_result)):
                                ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="news">News</option>
                                <option value="comment">Comment</option>
                                <option value="system">System</option>
                                <option value="reminder">Reminder</option>
                                <option value="promotion">Promotion</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-bell me-2"></i>Create Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh notifications
        setInterval(function() {
            // You could implement an AJAX call here to refresh notifications
            console.log('Checking for new notifications...');
        }, 30000); // Check every 30 seconds
        
        // Real-time notification counter update
        function updateNotificationCount() {
            fetch('api/notification-count.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => console.error('Error updating notification count:', error));
        }
        
        // Update notification count every minute
        setInterval(updateNotificationCount, 60000);
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
