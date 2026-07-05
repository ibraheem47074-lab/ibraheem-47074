<?php
/**
 * Analytics Tables Installer
 * Creates user_activity and news_analytics tables
 */

require_once 'config/database.php';

echo "<h2>Installing Analytics Tables...</h2>";
echo "<meta charset=\"UTF-8\">";

// Create user_activity table
$sql1 = "CREATE TABLE IF NOT EXISTS `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'view',
  `ip_address` varchar(45) DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql1)) {
    echo "<p style='color: green;'>✓ user_activity table created</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Create news_analytics table
$sql2 = "CREATE TABLE IF NOT EXISTS `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `unique_views` int(11) DEFAULT 0,
  `total_views` int(11) DEFAULT 0,
  `avg_time_on_page` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_date` (`news_id`, `date`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql2)) {
    echo "<p style='color: green;'>✓ news_analytics table created</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

echo "<hr><h3 style='color: green;'>✓ Installation Complete!</h3>";
echo "<p>You can now <a href='admin/website-performance.php'>view Website Performance</a>.</p>";
echo "<p><strong>Delete this file after installation.</strong></p>";
