<?php
require_once 'config/database.php';

echo "<h2>Emergency Fix - Create All Missing Tables (Fixed)</h2>";

// Critical tables needed for index.php
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','editor','reporter','user') DEFAULT 'user',
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'categories' => "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'news' => "CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(500) NOT NULL,
        slug VARCHAR(500) DEFAULT NULL,
        content LONGTEXT,
        image VARCHAR(500),
        video_url VARCHAR(500),
        video_path VARCHAR(500),
        category_id INT,
        author_id INT,
        source_url VARCHAR(500),
        status ENUM('published','draft','featured','archived') DEFAULT 'published',
        news_type VARCHAR(50) DEFAULT 'article',
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        shares INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'post_likes' => "CREATE TABLE IF NOT EXISTS post_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'comments' => "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT,
        name VARCHAR(255),
        email VARCHAR(255),
        comment TEXT NOT NULL,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

echo "<h3>Creating Tables...</h3>";
foreach ($tables as $name => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>â $name table created</p>";
    } else {
        echo "<p style='color:red'>â $name table error: " . mysqli_error($conn) . "</p>";
    }
}

// Add sample data
echo "<h3>Adding Sample Data...</h3>";

// Sample categories - simplified without slug requirement
$categories = [
    ["Politics", "politics"],
    ["Sports", "sports"],
    ["Technology", "technology"],
    ["Business", "business"],
    ["Entertainment", "entertainment"],
    ["Health", "health"],
    ["Education", "education"]
];

foreach ($categories as $cat) {
    $name = $cat[0];
    $slug = $cat[1];
    mysqli_query($conn, "INSERT IGNORE INTO categories (name, slug) VALUES ('$name', '$slug')");
}
echo "<p style='color:blue'>â Sample categories added</p>";

// Sample admin user
mysqli_query($conn, "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Admin', 'admin@pklivenews.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
echo "<p style='color:blue'>â Admin user added</p>";

// Sample news - simplified without slug complications
$sample_news = [
    ["Breaking: Major Political Development in Islamabad", 1],
    ["Sports Update: National Team Wins Championship", 2],
    ["Technology Breakthrough: New Innovation Announced", 3],
    ["Business News: Stock Market Reaches New Heights", 4],
    ["Entertainment: New Movie Release Breaks Records", 5]
];

foreach ($sample_news as $news) {
    $title = mysqli_real_escape_string($conn, $news[0]);
    $category_id = $news[1];
    $content = "This is sample content for: $title. Full article would go here with detailed information.";
    
    mysqli_query($conn, "INSERT IGNORE INTO news (title, content, category_id, author_id, status) VALUES 
        ('$title', '$content', $category_id, 1, 'published')");
}
echo "<p style='color:blue'>â Sample news articles added</p>";

// Test query
echo "<h3>Testing Query...</h3>";
$test = mysqli_query($conn, "SELECT n.*, c.name as category_name, u.name as author_name,
    (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
    (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
    FROM news n 
    LEFT JOIN categories c ON n.category_id = c.id 
    LEFT JOIN users u ON n.author_id = u.id 
    WHERE n.status = 'published' 
    ORDER BY n.created_at DESC LIMIT 3");

if ($test && mysqli_num_rows($test) > 0) {
    echo "<p style='color:green; font-size:18px; font-weight:bold;'>â SUCCESS! Query works perfectly!</p>";
    echo "<p style='color:green'>Index.php should now work without any errors.</p>";
    
    // Show sample results
    echo "<table border='1' cellpadding='5' style='margin-top:20px;'>";
    echo "<tr><th>Title</th><th>Category</th><th>Author</th><th>Comments</th><th>Likes</th></tr>";
    while ($row = mysqli_fetch_assoc($test)) {
        echo "<tr>";
        echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
        echo "<td>" . ($row['category_name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['author_name'] ?? 'N/A') . "</td>";
        echo "<td>" . $row['comment_count'] . "</td>";
        echo "<td>" . $row['likes_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>â Query still has issues: " . mysqli_error($conn) . "</p>";
}

echo "<div style='margin-top:30px; font-size:20px;'>";
echo "<a href='index.php' style='color:green; font-weight:bold; text-decoration:underline;'>â TEST INDEX PAGE NOW</a>";
echo "</div>";
?>
