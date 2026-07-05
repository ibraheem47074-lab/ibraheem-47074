<?php
require_once 'config/database.php';

echo "<h1>User Accounts Database Fix</h1>";

// Check and create missing columns
echo "<h2>Checking Users Table Structure</h2>";

$columns_to_add = [
    'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email",
    'bio' => "ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER phone",
    'image' => "ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER bio",
    'email_verified' => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER image",
    'two_factor_enabled' => "ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER email_verified",
    'email_notifications' => "ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) DEFAULT 1 AFTER two_factor_enabled",
    'push_notifications' => "ALTER TABLE users ADD COLUMN push_notifications TINYINT(1) DEFAULT 0 AFTER email_notifications",
    'newsletter_subscription' => "ALTER TABLE users ADD COLUMN newsletter_subscription TINYINT(1) DEFAULT 1 AFTER push_notifications",
    'profile_public' => "ALTER TABLE users ADD COLUMN profile_public TINYINT(1) DEFAULT 0 AFTER newsletter_subscription",
    'show_activity' => "ALTER TABLE users ADD COLUMN show_activity TINYINT(1) DEFAULT 1 AFTER profile_public",
    'preferred_categories' => "ALTER TABLE users ADD COLUMN preferred_categories TEXT NULL AFTER show_activity",
    'language_preference' => "ALTER TABLE users ADD COLUMN language_preference VARCHAR(10) DEFAULT 'en' AFTER preferred_categories",
    'reset_token' => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER language_preference",
    'reset_token_expires' => "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token",
    'email_verification_token' => "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER reset_token_expires",
    'email_verification_expires' => "ALTER TABLE users ADD COLUMN email_verification_expires DATETIME NULL AFTER email_verification_token",
    'department' => "ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL AFTER email_verification_expires",
    'experience_level' => "ALTER TABLE users ADD COLUMN experience_level VARCHAR(20) DEFAULT 'junior' AFTER department",
    'verification_status' => "ALTER TABLE users ADD COLUMN verification_status ENUM('unverified', 'verified', 'premium') DEFAULT 'unverified' AFTER experience_level",
    'specialization' => "ALTER TABLE users ADD COLUMN specialization VARCHAR(100) NULL AFTER verification_status",
    'skills' => "ALTER TABLE users ADD COLUMN skills TEXT NULL AFTER specialization",
    'profile_views' => "ALTER TABLE users ADD COLUMN profile_views INT DEFAULT 0 AFTER skills",
    'login_count' => "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER profile_views",
    'last_login' => "ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER login_count"
];

// Get existing columns
$existing_columns = [];
$columns_query = "SHOW COLUMNS FROM users";
$columns_result = mysqli_query($conn, $columns_query);

if ($columns_result) {
    while ($column = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = $column['Field'];
    }
}

// Add missing columns
$added_columns = 0;
foreach ($columns_to_add as $column_name => $alter_sql) {
    if (!in_array($column_name, $existing_columns)) {
        echo "<p style='color: orange;'>⚠ Adding column '$column_name'...</p>";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ Column '$column_name' added successfully</p>";
            $added_columns++;
        } else {
            echo "<p style='color: red;'>✗ Error adding column '$column_name': " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Column '$column_name' already exists</p>";
    }
}

// Check and create additional tables
echo "<h2>Checking Additional Tables</h2>";

$tables_to_create = [
    'user_permissions' => "
        CREATE TABLE IF NOT EXISTS `user_permissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `permission` varchar(100) NOT NULL,
            `granted_by` int(11) DEFAULT NULL,
            `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `granted_by` (`granted_by`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'user_ratings' => "
        CREATE TABLE IF NOT EXISTS `user_ratings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `rated_user_id` int(11) NOT NULL,
            `rater_user_id` int(11) NOT NULL,
            `rating` tinyint(1) NOT NULL,
            `review` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `rated_user_id` (`rated_user_id`),
            KEY `rater_user_id` (`rater_user_id`),
            FOREIGN KEY (`rated_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`rater_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_rating` (`rated_user_id`, `rater_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'user_achievements' => "
        CREATE TABLE IF NOT EXISTS `user_achievements` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `achievement_type` varchar(50) NOT NULL,
            `achievement_name` varchar(100) NOT NULL,
            `achievement_description` text DEFAULT NULL,
            `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'activity_log' => "
        CREATE TABLE IF NOT EXISTS `activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `details` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `action` (`action`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'bookmarks' => "
        CREATE TABLE IF NOT EXISTS `bookmarks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `news_id` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `news_id` (`news_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_bookmark` (`user_id`, `news_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'comments' => "
        CREATE TABLE IF NOT EXISTS `comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `news_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `comment` text NOT NULL,
            `status` enum('pending','approved','rejected') DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `news_id` (`news_id`),
            KEY `user_id` (`user_id`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

$created_tables = 0;
foreach ($tables_to_create as $table_name => $create_sql) {
    echo "<p style='color: blue;'>🔍 Checking table '$table_name'...</p>";
    
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
    if (mysqli_num_rows($table_check) == 0) {
        echo "<p style='color: orange;'>⚠ Creating table '$table_name'...</p>";
        if (mysqli_query($conn, $create_sql)) {
            echo "<p style='color: green;'>✓ Table '$table_name' created successfully</p>";
            $created_tables++;
        } else {
            echo "<p style='color: red;'>✗ Error creating table '$table_name': " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Table '$table_name' already exists</p>";
    }
}

// Create missing reset-password.php file
echo "<h2>Creating Missing Files</h2>";

if (!file_exists('reset-password.php')) {
    $reset_password_content = '<?php
require_once \'config/database.php\';

$token = $_GET[\'token\'] ?? \'\';
$error = \'\';
$success = \'\';

if (empty($token)) {
    $error = \'Invalid reset token\';
} else {
    // Check if token is valid
    $query = "SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, \'s\', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
            $password = $_POST[\'password\'];
            $confirm_password = $_POST[\'confirm_password\'];
            
            if (empty($password) || empty($confirm_password)) {
                $error = \'Please fill in all fields\';
            } elseif (strlen($password) < 6) {
                $error = \'Password must be at least 6 characters long\';
            } elseif ($password !== $confirm_password) {
                $error = \'Passwords do not match\';
            } else {
                // Update password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, \'si\', $hashed_password, $user[\'id\']);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success = \'Password reset successfully! You can now login.\';
                    log_activity($user[\'id\'], \'password_reset\', \'Password reset completed\');
                } else {
                    $error = \'Failed to reset password. Please try again.\';
                }
            }
        }
    } else {
        $error = \'Invalid or expired reset token\';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="text-center mb-4">
            <h3><i class="fas fa-newspaper me-2 text-primary"></i>PK Live News</h3>
            <p class="text-muted">Reset Password</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">Enter your new password below.</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               required minlength="6" autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               required minlength="6">
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-reset">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
';
    
    if (file_put_contents('reset-password.php', $reset_password_content)) {
        echo "<p style='color: green;'>✓ Created reset-password.php</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create reset-password.php</p>";
    }
} else {
    echo "<p style='color: green;'>✓ reset-password.php already exists</p>";
}

// Summary
echo "<h2>Summary</h2>";
echo "<p style='color: green;'>✓ Added $added_columns missing columns</p>";
echo "<p style='color: green;'>✓ Created $created_tables missing tables</p>";
echo "<p><a href='test_user_accounts.php'>Run Test Again</a> | <a href='login.php'>Test Login</a> | <a href='signup.php'>Test Signup</a></p>";
?>
