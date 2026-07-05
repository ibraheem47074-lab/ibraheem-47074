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

echo "<h2>Creating Missing Tables</h2>";

// Create comments table
$create_comments_sql = "
CREATE TABLE IF NOT EXISTS `comments` (
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
    echo "<p style='color: green;'>Comments table created successfully or already exists</p>";
} else {
    echo "<p style='color: red;'>Error creating comments table: " . $conn->error . "</p>";
}

// Create post_likes table
$create_post_likes_sql = "
CREATE TABLE IF NOT EXISTS `post_likes` (
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
    echo "<p style='color: green;'>Post likes table created successfully or already exists</p>";
} else {
    echo "<p style='color: red;'>Error creating post likes table: " . $conn->error . "</p>";
}

// Test the index.php queries
echo "<h2>Testing Index.php Queries</h2>";
$test_query = "SELECT n.*, c.name as category_name, u.name as author_name,
              (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
              (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              LEFT JOIN users u ON n.author_id = u.id 
              WHERE n.status = 'featured' AND n.published_at <= NOW() 
              ORDER BY n.published_at DESC LIMIT 3";

$result = $conn->query($test_query);
if ($result) {
    echo "<p style='color: green;'>Index.php query test successful! The error should be fixed.</p>";
} else {
    echo "<p style='color: red;'>Query test failed: " . $conn->error . "</p>";
}

echo "<p><a href='index.php'>Go to Index Page</a></p>";
$conn->close();
?>
