<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'pk_live_news';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create comments table
$create_comments_sql = "
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('approved','pending','rejected') DEFAULT 'pending',
  `parent_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_news` (`news_id`),
  KEY `idx_comments_user` (`user_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_news_status` (`news_id`,`status`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create_comments_sql)) {
    echo "Comments table created successfully\n";
} else {
    echo "Error creating comments table: " . $conn->error . "\n";
}

// Check if post_likes table exists, create if not
$check_post_likes = "SHOW TABLES LIKE 'post_likes'";
$result = $conn->query($check_post_likes);

if ($result->num_rows == 0) {
    $create_post_likes_sql = "
    CREATE TABLE `post_likes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `news_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_post_likes_news` (`news_id`),
      KEY `idx_post_likes_user` (`user_id`),
      UNIQUE KEY `unique_like` (`news_id`, `user_id`, `ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_post_likes_sql)) {
        echo "Post likes table created successfully\n";
    } else {
        echo "Error creating post likes table: " . $conn->error . "\n";
    }
} else {
    echo "Post likes table already exists\n";
}

$conn->close();
?>
