<?php
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Analytics Installation - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>PK Live News - Analytics Installation</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Installing Analytics Tables...</h2>";

// Function to execute SQL and show result
function executeSQL($sql, $successMessage, $errorMessage) {
    global $conn;
    try {
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ " . $successMessage . "</p>";
            return true;
        } else {
            echo "<p class='error'>✗ " . $errorMessage . ": " . mysqli_error($conn) . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ " . $errorMessage . ": " . $e->getMessage() . "</p>";
        return false;
    }
}

// Create news_analytics table
$news_analytics_sql = "
CREATE TABLE IF NOT EXISTS `news_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `views` int(11) DEFAULT 0,
    `unique_visitors` int(11) DEFAULT 0,
    `avg_read_time` int(11) DEFAULT 0,
    `date` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_news_date` (`news_id`,`date`),
    KEY `idx_analytics_news` (`news_id`),
    KEY `idx_analytics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($news_analytics_sql, "News analytics table created", "Error creating news_analytics table");

// Create user_analytics table
$user_analytics_sql = "
CREATE TABLE IF NOT EXISTS `user_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `page_url` varchar(500) DEFAULT NULL,
    `referrer` varchar(500) DEFAULT NULL,
    `time_on_page` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_analytics_user` (`user_id`),
    KEY `idx_user_analytics_session` (`session_id`),
    KEY `idx_user_analytics_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($user_analytics_sql, "User analytics table created", "Error creating user_analytics table");

// Create page_views table
$page_views_sql = "
CREATE TABLE IF NOT EXISTS `page_views` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_type` enum('home','category','article','search','other') DEFAULT 'other',
    `page_id` int(11) DEFAULT NULL,
    `page_url` varchar(500) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_page_views_type` (`page_type`),
    KEY `idx_page_views_date` (`created_at`),
    KEY `idx_page_views_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($page_views_sql, "Page views table created", "Error creating page_views table");

// Add views column to news table if it doesn't exist
$check_views = "SHOW COLUMNS FROM news LIKE 'views'";
$result = mysqli_query($conn, $check_views);
if (mysqli_num_rows($result) == 0) {
    $add_views_sql = "ALTER TABLE news ADD COLUMN `views` int(11) DEFAULT 0 AFTER `breaking_news`";
    executeSQL($add_views_sql, "Views column added to news table", "Error adding views column");
} else {
    echo "<p class='info'>ℹ Views column already exists in news table</p>";
}

// Insert sample analytics data
$check_analytics = "SELECT id FROM news_analytics LIMIT 1";
$result = mysqli_query($conn, $check_analytics);
if (mysqli_num_rows($result) == 0) {
    // Get some news articles
    $news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 5";
    $news_result = mysqli_query($conn, $news_query);
    
    while ($news = mysqli_fetch_assoc($news_result)) {
        $sample_analytics_sql = "
        INSERT INTO news_analytics (news_id, views, unique_visitors, avg_read_time, date) VALUES
        (" . $news['id'] . ", " . rand(50, 500) . ", " . rand(20, 200) . ", " . rand(60, 300) . ", CURDATE() - INTERVAL " . rand(0, 7) . " DAY)
        ";
        executeSQL($sample_analytics_sql, "Sample analytics data inserted for article " . $news['id'], "Error inserting sample analytics data");
    }
}

echo "<h3 class='success'>✅ Analytics Installation Complete!</h3>";
echo "<div class='alert alert-success'>
    <h4>Installation Completed Successfully!</h4>
    <p>All analytics tables have been created and sample data has been inserted.</p>
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ul>
        <li><a href='analytics-simple.php' class='btn btn-primary'>View Analytics Dashboard</a></li>
        <li><a href='dashboard.php' class='btn btn-secondary'>Go to Admin Dashboard</a></li>
        <li><a href='../index.php' class='btn btn-info'>View Website</a></li>
    </ul>
</div>";

echo "</div></div></div></body></html>";
?>
