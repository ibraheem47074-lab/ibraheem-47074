<?php
require_once 'config/database.php';

echo "<h2>Creating Missing Tables</h2>";

// Create post_likes table
echo "<h3>Creating post_likes table...</h3>";
$create_post_likes = "CREATE TABLE IF NOT EXISTS `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_post_likes)) {
    echo "<p style='color:green'>â post_likes table created successfully</p>";
} else {
    echo "<p style='color:red'>â Error creating post_likes table: " . mysqli_error($conn) . "</p>";
}

// Create comments table if it doesn't exist
echo "<h3>Creating comments table...</h3>";
$create_comments = "CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_comments)) {
    echo "<p style='color:green'>â comments table created successfully</p>";
} else {
    echo "<p style='color:red'>â Error creating comments table: " . mysqli_error($conn) . "</p>";
}

// Test the problematic query from index.php
echo "<h3>Testing index.php query...</h3>";
$test_query = "SELECT n.*, 
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                FROM news n LIMIT 3";

$test_result = mysqli_query($conn, $test_query);
if ($test_result) {
    echo "<p style='color:green'>â Query executed successfully - error should be fixed!</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Title</th><th>Comments</th><th>Likes</th></tr>";
    
    while ($row = mysqli_fetch_assoc($test_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'] ?? 'No title', 0, 40) . "...</td>";
        echo "<td>" . $row['comment_count'] . "</td>";
        echo "<td>" . $row['likes_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>â Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div class='mt-4'>";
echo "<a href='index.php' class='btn btn-primary'>Test Index Page</a> | ";
echo "<a href='check_database_status.php' class='btn btn-info'>Database Status</a>";
echo "</div>";
?>
