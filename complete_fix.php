<?php
require_once 'config/database.php';

echo "<h2>Complete Fix - All Required Columns</h2>";

// Step 1: Drop and recreate news table with all required columns
echo "<h3>Step 1: Creating Complete News Table</h3>";

$drop_news = "DROP TABLE IF EXISTS news";
mysqli_query($conn, $drop_news);

$create_news = "CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) DEFAULT NULL,
    content LONGTEXT,
    excerpt TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    video_path VARCHAR(500) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    author_id INT DEFAULT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    status ENUM('published','draft','featured','archived') NOT NULL DEFAULT 'published',
    news_type VARCHAR(50) DEFAULT 'article',
    views INT NOT NULL DEFAULT 0,
    likes INT NOT NULL DEFAULT 0,
    shares INT NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_news)) {
    echo "<p style='color:green'>â Complete news table created</p>";
} else {
    echo "<p style='color:red'>â News table error: " . mysqli_error($conn) . "</p>";
}

// Step 2: Create other required tables
echo "<h3>Step 2: Creating Other Tables</h3>";

// Users table
$create_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','reporter','user') NOT NULL DEFAULT 'user',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_users)) {
    echo "<p style='color:green'>â Users table created</p>";
}

// Categories table
$create_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_categories)) {
    echo "<p style='color:green'>â Categories table created</p>";
}

// Post likes table
$create_post_likes = "CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_post_likes)) {
    echo "<p style='color:green'>â Post likes table created</p>";
}

// Comments table
$create_comments = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_comments)) {
    echo "<p style='color:green'>â Comments table created</p>";
}

// Step 3: Add sample data
echo "<h3>Step 3: Adding Sample Data</h3>";

// Add admin user
mysqli_query($conn, "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Admin', 'admin@pklivenews.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
echo "<p style='color:blue'>â Admin user added</p>";

// Add categories
$categories = [
    ['Politics', 'politics'],
    ['Sports', 'sports'],
    ['Technology', 'technology'],
    ['Business', 'business'],
    ['Entertainment', 'entertainment'],
    ['Health', 'health'],
    ['Education', 'education']
];

foreach ($categories as $cat) {
    $name = mysqli_real_escape_string($conn, $cat[0]);
    $slug = mysqli_real_escape_string($conn, $cat[1]);
    mysqli_query($conn, "INSERT IGNORE INTO categories (name, slug) VALUES ('$name', '$slug')");
}
echo "<p style='color:blue'>â Categories added</p>";

// Add sample news with all required fields
$sample_news = [
    [
        'title' => 'Breaking: Major Political Development in Islamabad',
        'content' => 'This is comprehensive sample content for the political development story. It includes all the necessary details about the breaking news event.',
        'category_id' => 1,
        'source_url' => 'https://example.com/political-news',
        'status' => 'published'
    ],
    [
        'title' => 'Sports Update: National Team Wins Championship',
        'content' => 'Detailed coverage of the national team championship victory with player interviews and match analysis.',
        'category_id' => 2,
        'source_url' => 'https://example.com/sports-news',
        'status' => 'published'
    ],
    [
        'title' => 'Technology Breakthrough: New Innovation Announced',
        'content' => 'In-depth report on the latest technology breakthrough and its potential impact on the industry.',
        'category_id' => 3,
        'source_url' => 'https://example.com/tech-news',
        'status' => 'published'
    ],
    [
        'title' => 'Business News: Stock Market Reaches New Heights',
        'content' => 'Analysis of the stock market performance and economic indicators driving the growth.',
        'category_id' => 4,
        'source_url' => 'https://example.com/business-news',
        'status' => 'published'
    ],
    [
        'title' => 'Entertainment: New Movie Release Breaks Records',
        'content' => 'Review and box office analysis of the new movie release that is breaking records.',
        'category_id' => 5,
        'source_url' => 'https://example.com/entertainment-news',
        'status' => 'published'
    ]
];

foreach ($sample_news as $news) {
    $title = mysqli_real_escape_string($conn, $news['title']);
    $content = mysqli_real_escape_string($conn, $news['content']);
    $category_id = $news['category_id'];
    $source_url = mysqli_real_escape_string($conn, $news['source_url']);
    $status = $news['status'];
    
    mysqli_query($conn, "INSERT IGNORE INTO news (title, content, category_id, source_url, status) VALUES 
        ('$title', '$content', $category_id, '$source_url', '$status')");
}
echo "<p style='color:blue'>â Sample news added</p>";

// Step 4: Test the exact query from index.php
echo "<h3>Step 4: Testing Complete Index.php Query</h3>";

$test_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                CASE 
                    WHEN n.image IS NOT NULL AND n.image != '' THEN 3
                    WHEN n.video_url IS NOT NULL AND n.video_url != '' THEN 2
                    WHEN n.video_path IS NOT NULL AND n.video_path != '' THEN 2
                    ELSE 1
                END as media_priority,
                CASE 
                    WHEN n.source_url IS NOT NULL AND n.source_url != '' THEN 'external'
                    ELSE 'internal'
                END as news_type,
                CASE 
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
                    ELSE 'older'
                END as time_status,
                n.news_type as article_type,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count,
                CASE 
                    WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                    WHEN n.source_url LIKE '%arynews.tv%' OR n.source_url LIKE '%arydigital.tv%' THEN 'ARY News'
                    ELSE 'Internal'
                END as source_name
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' 
                ORDER BY n.created_at DESC LIMIT 5";

$test_result = mysqli_query($conn, $test_query);

if ($test_result) {
    echo "<p style='color:green; font-size:20px; font-weight:bold;'>â COMPLETE SUCCESS!</p>";
    echo "<p style='color:green;'>All required columns exist and query works perfectly!</p>";
    
    if (mysqli_num_rows($test_result) > 0) {
        echo "<table border='1' cellpadding='5' style='margin-top:20px;'>";
        echo "<tr style='background:#f0f0f0;'><th>Title</th><th>Category</th><th>Author</th><th>Type</th><th>Comments</th><th>Likes</th></tr>";
        
        while ($row = mysqli_fetch_assoc($test_result)) {
            echo "<tr>";
            echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
            echo "<td>" . ($row['category_name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['author_name'] ?? 'N/A') . "</td>";
            echo "<td>" . $row['news_type'] . "</td>";
            echo "<td>" . $row['comment_count'] . "</td>";
            echo "<td>" . $row['likes_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='margin-top:30px; padding:20px; background:#d4edda; border-radius:5px;'>";
        echo "<h3 style='color:green;'>â INDEX.PHP SHOULD WORK PERFECTLY NOW!</h3>";
        echo "<p style='font-size:18px;'><a href='index.php' style='color:green; font-weight:bold; text-decoration:underline;'>Test Index Page Now</a></p>";
        echo "<p>All missing columns have been created with proper structure.</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color:red; font-size:18px;'>â Query failed: " . mysqli_error($conn) . "</p>";
}
?>
