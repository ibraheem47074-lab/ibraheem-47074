<?php
// Create database script for PK Live News

echo "<h2>Creating Database...</h2>";

// Connect to MySQL without specifying database
$conn = mysqli_connect('localhost', 'root', '');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS pk_live_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✓ Database 'pk_live_news' created successfully or already exists.</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating database: " . mysqli_error($conn) . "</p>";
}

// Create users table if not exists
mysqli_select_db($conn, 'pk_live_news');

$users_sql = "
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','editor','reporter') DEFAULT 'reporter',
    `status` enum('active','blocked') DEFAULT 'active',
    `bio` text DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (mysqli_query($conn, $users_sql)) {
    echo "<p style='color: green;'>✓ Users table created.</p>";
    
    // Insert default admin user if not exists
    $check_admin = "SELECT id FROM users WHERE email = 'admin@pklivenews.com'";
    $result = mysqli_query($conn, $check_admin);
    
    if (mysqli_num_rows($result) == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) VALUES ('Admin', 'admin@pklivenews.com', '$admin_password', 'admin', 'active')";
        if (mysqli_query($conn, $insert_admin)) {
            echo "<p style='color: green;'>✓ Default admin user created (email: admin@pklivenews.com, password: admin123).</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ Admin user already exists.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Error creating users table: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>Database Creation Complete!</h3>";
echo "<p><a href='setup_database.php'>Click here to run the full setup script</a></p>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>
