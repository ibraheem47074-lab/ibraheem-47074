<?php
// Quick database fix
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) die("Database connection failed");

// Check if news table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
if (mysqli_num_rows($result) == 0) {
    // Create news table
    $sql = "CREATE TABLE `news` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `content` text DEFAULT NULL,
        `excerpt` varchar(500) DEFAULT NULL,
        `image` varchar(500) DEFAULT NULL,
        `category_id` int(11) DEFAULT NULL,
        `author_id` int(11) DEFAULT NULL,
        `status` enum('published','draft','pending') DEFAULT 'draft',
        `is_breaking` tinyint(1) DEFAULT 0,
        `source_url` varchar(500) DEFAULT NULL,
        `published_at` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✓ News table created</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Create categories table if not exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
if (mysqli_num_rows($result) == 0) {
    $sql = "CREATE TABLE `categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `slug` varchar(100) NOT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    mysqli_query($conn, $sql);
    echo "<p style='color:green'>✓ Categories table created</p>";
}

// Insert sample data
mysqli_query($conn, "INSERT IGNORE INTO categories (name, slug) VALUES 
    ('Politics', 'politics'),
    ('Sports', 'sports'),
    ('Technology', 'technology')");

mysqli_query($conn, "INSERT IGNORE INTO news (title, slug, content, status, published_at) VALUES 
    ('Welcome to PK Live News', 'welcome', 'This is a sample news article.', 'published', NOW())");

echo "<p style='color:green'><strong>Database fixed!</strong></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
