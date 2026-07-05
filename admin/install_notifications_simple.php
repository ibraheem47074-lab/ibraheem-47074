<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Create notifications table if it doesn't exist
$create_notifications_table = "
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  KEY `type` (`type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create notification_settings table if it doesn't exist
$create_notification_settings_table = "
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `news_notifications` tinyint(1) DEFAULT 1,
  `event_notifications` tinyint(1) DEFAULT 1,
  `system_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create notification_queue table for scheduled notifications
$create_notification_queue_table = "
CREATE TABLE IF NOT EXISTS `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `scheduled_at` (`scheduled_at`),
  KEY `status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    // Create tables
    mysqli_query($conn, $create_notifications_table);
    mysqli_query($conn, $create_notification_settings_table);
    mysqli_query($conn, $create_notification_queue_table);
    
    // Initialize notification settings for all existing users
    $init_settings = "INSERT IGNORE INTO notification_settings (user_id) SELECT id FROM users";
    mysqli_query($conn, $init_settings);
    
    // Create some sample notifications
    $sample_notifications = [
        [
            'user_id' => 1,
            'title' => 'Welcome to PK Live News Notifications!',
            'message' => 'The notification system has been successfully installed. You will now receive important updates about news, events, and system activities.',
            'type' => 'success',
            'url' => 'admin-dashboard.php'
        ],
        [
            'user_id' => 1,
            'title' => 'Notification System Features',
            'message' => 'You can manage your notification preferences, view history, and send custom notifications to users from the admin panel.',
            'type' => 'info',
            'url' => 'admin/manage-notifications.php'
        ]
    ];
    
    foreach ($sample_notifications as $notification) {
        $insert_query = "INSERT INTO notifications (user_id, title, message, type, url) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'issss', 
            $notification['user_id'], 
            $notification['title'], 
            $notification['message'], 
            $notification['type'], 
            $notification['url']
        );
        mysqli_stmt_execute($stmt);
    }
    
    $success = "Notification system installed successfully! Tables created and sample notifications added.";
    
} catch (Exception $e) {
    $error = "Error installing notification system: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Notifications - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .install-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Install Notification System</h1>
                        <small>PK Live News - Notification Management</small>
                    </div>
                    <div>
                        <a href="admin-dashboard.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Installation Result -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card install-card">
                            <div class="card-body p-5">
                                <?php if ($success): ?>
                                    <div class="text-center success-animation">
                                        <div class="feature-icon bg-success text-white mx-auto mb-4">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <h2 class="text-success mb-3">Installation Successful!</h2>
                                        <p class="lead mb-4"><?php echo $success; ?></p>
                                        
                                        <div class="row text-start mt-5">
                                            <div class="col-md-6 mb-3">
                                                <h5><i class="fas fa-database me-2 text-primary"></i>Tables Created</h5>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>notifications</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>notification_settings</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>notification_queue</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <h5><i class="fas fa-cog me-2 text-info"></i>Features Enabled</h5>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>User notifications</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Email notifications</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Push notifications</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Scheduled notifications</li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="manage-notifications.php" class="btn btn-primary btn-lg">
                                                <i class="fas fa-bell me-2"></i>Manage Notifications
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-outline-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="feature-icon bg-danger text-white mx-auto mb-4">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <h2 class="text-danger mb-3">Installation Failed</h2>
                                        <p class="lead mb-4"><?php echo $error; ?></p>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                                                <i class="fas fa-arrow-left me-2"></i>Go Back
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Next Steps</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-cogs me-2 text-primary"></i>Configuration</h6>
                                        <p class="small text-muted">Configure notification settings, email templates, and push notification settings from the admin panel.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-users me-2 text-success"></i>User Management</h6>
                                        <p class="small text-muted">Users can now manage their notification preferences from their profile settings.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
