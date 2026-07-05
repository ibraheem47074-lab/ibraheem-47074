<?php
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Broadcast Tables - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        <h4>Setting Up Broadcast Control Tables</h4>
                    </div>
                    <div class='card-body'>";

// Create broadcast_logs table
$create_logs_query = "CREATE TABLE IF NOT EXISTS broadcast_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    channel_id INT NOT NULL,
    action ENUM('start', 'stop') NOT NULL,
    admin_id INT NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    INDEX (channel_id),
    INDEX (timestamp)
)";

if (mysqli_query($conn, $create_logs_query)) {
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle me-2'></i>
        <strong>broadcast_logs</strong> table created successfully!
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-circle me-2'></i>
        Error creating broadcast_logs table: " . mysqli_error($conn) . "
    </div>";
}

// Create channel_bans table
$create_bans_query = "CREATE TABLE IF NOT EXISTS channel_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    channel_id INT NOT NULL,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    reason TEXT,
    ban_time DATETIME NOT NULL,
    unban_time DATETIME NULL,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    INDEX (channel_id),
    INDEX (user_id),
    INDEX (ban_time)
)";

if (mysqli_query($conn, $create_bans_query)) {
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle me-2'></i>
        <strong>channel_bans</strong> table created successfully!
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-circle me-2'></i>
        Error creating channel_bans table: " . mysqli_error($conn) . "
    </div>";
}

// Add new columns to channels table if they don't exist
$alter_queries = [
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS stream_key VARCHAR(255) NULL",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS stream_title VARCHAR(255) NULL",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS stream_description TEXT NULL",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS start_time DATETIME NULL",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS end_time DATETIME NULL",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS max_viewers INT DEFAULT 1000",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS quality ENUM('480p', '720p', '1080p') DEFAULT '720p'",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS allow_chat TINYINT(1) DEFAULT 1",
    "ALTER TABLE channels ADD COLUMN IF NOT EXISTS record_stream TINYINT(1) DEFAULT 0"
];

foreach ($alter_queries as $query) {
    if (mysqli_query($conn, $query)) {
        echo "<div class='alert alert-info'>
            <i class='fas fa-info-circle me-2'></i>
            Channels table updated successfully!
        </div>";
    }
}

// Create admins table if it doesn't exist
$create_admins_query = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1
)";

if (mysqli_query($conn, $create_admins_query)) {
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle me-2'></i>
        <strong>admins</strong> table created successfully!
    </div>";
    
    // Insert default admin if not exists
    $check_admin = "SELECT id FROM admins WHERE username = 'admin'";
    if (mysqli_num_rows(mysqli_query($conn, $check_admin)) == 0) {
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO admins (username, password, email, full_name, role) VALUES ('admin', ?, 'admin@pklivenews.com', 'System Administrator', 'super_admin')";
        $stmt = mysqli_prepare($conn, $insert_admin);
        mysqli_stmt_bind_param($stmt, "s", $default_password);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-warning'>
                <i class='fas fa-user-shield me-2'></i>
                Default admin account created:<br>
                <strong>Username:</strong> admin<br>
                <strong>Password:</strong> admin123<br>
                <small class='text-muted'>Please change this password after first login!</small>
            </div>";
        }
    }
} else {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-circle me-2'></i>
        Error creating admins table: " . mysqli_error($conn) . "
    </div>";
}

echo "
                    <div class='text-center mt-4'>
                        <a href='broadcast_controls.php' class='btn btn-primary btn-lg'>
                            <i class='fas fa-tachometer-alt me-2'></i>Go to Broadcast Controls
                        </a>
                        <a href='../index.php' class='btn btn-secondary btn-lg ms-2'>
                            <i class='fas fa-home me-2'></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";

mysqli_close($conn);
?>
