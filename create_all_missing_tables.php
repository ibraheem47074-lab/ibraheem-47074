<?php
require_once 'config/database.php';

echo "<h2>Create All Missing Tables for Index.php</h2>";

// List of tables needed by index.php
$required_tables = [
    'users' => "CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('admin','editor','reporter','user') NOT NULL DEFAULT 'user',
      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `role` (`role`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'categories' => "CREATE TABLE IF NOT EXISTS `categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`),
      KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'news' => "CREATE TABLE IF NOT EXISTS `news` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(500) NOT NULL,
      `slug` varchar(500) DEFAULT NULL,
      `content` longtext DEFAULT NULL,
      `excerpt` text DEFAULT NULL,
      `image` varchar(500) DEFAULT NULL,
      `video_url` varchar(500) DEFAULT NULL,
      `video_path` varchar(500) DEFAULT NULL,
      `category_id` int(11) DEFAULT NULL,
      `author_id` int(11) DEFAULT NULL,
      `source_url` varchar(500) DEFAULT NULL,
      `status` enum('published','draft','featured','archived') NOT NULL DEFAULT 'published',
      `news_type` varchar(50) DEFAULT 'article',
      `views` int(11) NOT NULL DEFAULT 0,
      `likes` int(11) NOT NULL DEFAULT 0,
      `shares` int(11) NOT NULL DEFAULT 0,
      `published_at` timestamp NULL DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `title` (`title`),
      KEY `slug` (`slug`),
      KEY `category_id` (`category_id`),
      KEY `author_id` (`author_id`),
      KEY `status` (`status`),
      KEY `published_at` (`published_at`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'post_likes' => "CREATE TABLE IF NOT EXISTS `post_likes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `news_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `news_id` (`news_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'comments' => "CREATE TABLE IF NOT EXISTS `comments` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

// Create each table
foreach ($required_tables as $table_name => $create_sql) {
    echo "<h3>Creating $table_name table...</h3>";
    
    if (mysqli_query($conn, $create_sql)) {
        echo "<p style='color:green'>â $table_name table created successfully</p>";
        
        // Add sample data for critical tables
        if ($table_name === 'categories') {
            $sample_categories = "INSERT IGNORE INTO categories (name, slug) VALUES 
                ('Politics', 'politics'),
                ('Sports', 'sports'),
                ('Technology', 'technology'),
                ('Business', 'business'),
                ('Entertainment', 'entertainment')";
            mysqli_query($conn, $sample_categories);
            echo "<p style='color:blue'>â Added sample categories</p>";
        }
        
        if ($table_name === 'users') {
            $sample_admin = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
                ('Admin', 'admin@pklivenews.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')";
            mysqli_query($conn, $sample_admin);
            echo "<p style='color:blue'>â Added admin user</p>";
        }
        
    } else {
        echo "<p style='color:red'>â Error creating $table_name table: " . mysqli_error($conn) . "</p>";
    }
}

// Test the problematic query from index.php
echo "<h3>Testing Index.php Query...</h3>";
$test_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' 
                ORDER BY n.created_at DESC LIMIT 5";

$test_result = mysqli_query($conn, $test_query);
if ($test_result) {
    echo "<p style='color:green'>â Query executed successfully - index.php should work now!</p>";
    
    if (mysqli_num_rows($test_result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Author</th><th>Comments</th><th>Likes</th></tr>";
        
        while ($row = mysqli_fetch_assoc($test_result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . substr($row['title'] ?? 'No title', 0, 30) . "...</td>";
            echo "<td>" . ($row['category_name'] ?? 'No category') . "</td>";
            echo "<td>" . ($row['author_name'] ?? 'No author') . "</td>";
            echo "<td>" . $row['comment_count'] . "</td>";
            echo "<td>" . $row['likes_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>â No news articles found - tables are empty</p>";
    }
} else {
    echo "<p style='color:red'>â Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div class='mt-4' style='font-size:18px;'>";
echo "<a href='index.php' style='color:green; font-weight:bold;'>â Test Index Page Now</a><br><br>";
echo "<a href='check_database_status.php'>Check Database Status</a><br>";
echo "<a href='create_sample_news.php'>Add Sample News</a>";
echo "</div>";
?>
