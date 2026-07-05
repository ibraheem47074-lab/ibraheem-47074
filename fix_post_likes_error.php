<?php
require_once 'config/database.php';

echo "<h2>Fix Post Likes Table Error</h2>";

// Step 1: Check if post_likes table exists
echo "<h3>Step 1: Check post_likes table</h3>";
$check_query = "SHOW TABLES LIKE 'post_likes'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color:green'>â post_likes table exists</p>";
} else {
    echo "<p style='color:red'>â post_likes table missing - creating now...</p>";
    
    $create_query = "CREATE TABLE `post_likes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `news_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `news_id` (`news_id`),
      KEY `user_id` (`user_id`),
      KEY `idx_news_id` (`news_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $create_query)) {
        echo "<p style='color:green'>â post_likes table created successfully</p>";
    } else {
        echo "<p style='color:red'>â Error creating table: " . mysqli_error($conn) . "</p>";
    }
}

// Step 2: Test the problematic query from index.php
echo "<h3>Step 2: Test Problematic Query</h3>";
$test_query = "SELECT n.*, 
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                FROM news n LIMIT 3";

$test_result = mysqli_query($conn, $test_query);
if ($test_result) {
    echo "<p style='color:green'>â Query executed successfully</p>";
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

// Step 3: Check if comments table exists (also referenced in the query)
echo "<h3>Step 3: Check comments table</h3>";
$check_comments = "SHOW TABLES LIKE 'comments'";
$comments_result = mysqli_query($conn, $check_comments);

if (mysqli_num_rows($comments_result) > 0) {
    echo "<p style='color:green'>â comments table exists</p>";
} else {
    echo "<p style='color:red'>â comments table missing - creating now...</p>";
    
    $create_comments = "CREATE TABLE `comments` (
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
}

// Step 4: Test index.php functionality
echo "<h3>Step 4: Final Test</h3>";
$final_test = "SELECT n.*, c.name as category_name, u.name as author_name,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' 
                ORDER BY n.created_at DESC LIMIT 5";

$final_result = mysqli_query($conn, $final_test);
if ($final_result) {
    echo "<p style='color:green'>â Full index.php query test passed!</p>";
    echo "<p style='color:blue'>â The post_likes error should now be fixed.</p>";
} else {
    echo "<p style='color:red'>â Full query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div class='mt-4'>";
echo "<a href='index.php' class='btn btn-primary'>Test Index Page</a> | ";
echo "<a href='create_post_likes_table.php' class='btn btn-secondary'>Recreate Table</a> | ";
echo "<a href='check_database_status.php' class='btn btn-info'>Database Status</a>";
echo "</div>";
?>
