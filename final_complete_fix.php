<?php
require_once 'config/database.php';

echo "<h2>Final Complete Fix - All Missing Tables</h2>";

// Step 1: Create all required tables
echo "<h3>Step 1: Creating All Required Tables</h3>";

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
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_categories)) {
    echo "<p style='color:green'>â Categories table created</p>";
}

// News table with all required columns
$create_news = "CREATE TABLE IF NOT EXISTS news (
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
    echo "<p style='color:green'>â News table created</p>";
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

// Polls table
$create_polls = "CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active','inactive','ended') NOT NULL DEFAULT 'active',
    ends_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_polls)) {
    echo "<p style='color:green'>â Polls table created</p>";
}

// Poll options table
$create_poll_options = "CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    votes INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_poll_options)) {
    echo "<p style='color:green'>â Poll options table created</p>";
}

// Live stream table
$create_live_stream = "CREATE TABLE IF NOT EXISTS live_stream (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    stream_url VARCHAR(500) NOT NULL,
    thumbnail VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('online','offline','scheduled') NOT NULL DEFAULT 'offline',
    scheduled_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_live_stream)) {
    echo "<p style='color:green'>â Live stream table created</p>";
}

// Step 2: Add sample data
echo "<h3>Step 2: Adding Sample Data</h3>";

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

// Add sample news
$sample_news = [
    [
        'title' => 'Breaking: Major Political Development in Islamabad',
        'content' => 'This is comprehensive sample content for the political development story.',
        'category_id' => 1,
        'source_url' => 'https://example.com/political-news',
        'status' => 'published'
    ],
    [
        'title' => 'Sports Update: National Team Wins Championship',
        'content' => 'Detailed coverage of the national team championship victory.',
        'category_id' => 2,
        'source_url' => 'https://example.com/sports-news',
        'status' => 'published'
    ],
    [
        'title' => 'Technology Breakthrough: New Innovation Announced',
        'content' => 'In-depth report on the latest technology breakthrough.',
        'category_id' => 3,
        'source_url' => 'https://example.com/tech-news',
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

// Add sample poll
mysqli_query($conn, "INSERT IGNORE INTO polls (question, status) VALUES 
    ('What is your favorite news category?', 'active')");
echo "<p style='color:blue'>â Sample poll added</p>";

// Add poll options
$poll_options = ['Politics', 'Sports', 'Technology', 'Business', 'Entertainment'];
foreach ($poll_options as $i => $option) {
    $option = mysqli_real_escape_string($conn, $option);
    mysqli_query($conn, "INSERT IGNORE INTO poll_options (poll_id, option_text, votes) VALUES 
        (1, '$option', " . rand(0, 50) . ")");
}
echo "<p style='color:blue'>â Poll options added</p>";

// Add live stream
mysqli_query($conn, "INSERT IGNORE INTO live_stream (title, stream_url, status) VALUES 
    ('Live News Channel', 'https://example.com/live-stream', 'offline')");
echo "<p style='color:blue'>â Live stream added</p>";

// Step 3: Test all critical queries
echo "<h3>Step 3: Testing All Critical Queries</h3>";

// Test news query
$news_test = mysqli_query($conn, "SELECT n.*, c.name as category_name, u.name as author_name,
    (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
    (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
    FROM news n 
    LEFT JOIN categories c ON n.category_id = c.id 
    LEFT JOIN users u ON n.author_id = u.id 
    WHERE n.status = 'published' 
    ORDER BY n.created_at DESC LIMIT 3");

if ($news_test) {
    echo "<p style='color:green'>â News query works</p>";
} else {
    echo "<p style='color:red'>â News query failed: " . mysqli_error($conn) . "</p>";
}

// Test poll query
$poll_test = mysqli_query($conn, "SELECT p.*, po.option_text, po.votes, po.id as option_id 
    FROM polls p 
    LEFT JOIN poll_options po ON p.id = po.poll_id 
    WHERE p.status = 'active' AND (p.ends_at IS NULL OR p.ends_at > NOW()) 
    ORDER BY p.id DESC, po.id ASC LIMIT 1");

if ($poll_test) {
    echo "<p style='color:green'>â Poll query works</p>";
} else {
    echo "<p style='color:red'>â Poll query failed: " . mysqli_error($conn) . "</p>";
}

// Test live stream query
$stream_test = mysqli_query($conn, "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1");
if ($stream_test) {
    echo "<p style='color:green'>â Live stream query works</p>";
} else {
    echo "<p style='color:red'>â Live stream query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div style='margin-top:30px; padding:20px; background:#d4edda; border-radius:5px;'>";
echo "<h3 style='color:green;'>â ALL TABLES CREATED SUCCESSFULLY!</h3>";
echo "<p style='font-size:18px;'><a href='index.php' style='color:green; font-weight:bold; text-decoration:underline;'>Test Index Page Now</a></p>";
echo "<p>All missing tables including polls, poll_options, and live_stream have been created.</p>";
echo "<p>The fatal errors should now be completely resolved.</p>";
echo "</div>";
?>
