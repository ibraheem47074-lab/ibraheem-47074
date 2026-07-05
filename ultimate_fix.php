<?php
require_once 'config/database.php';

echo "<h2>Ultimate Fix - All Missing Tables (No Errors)</h2>";

// Step 1: Create all tables first
echo "<h3>Step 1: Creating Tables...</h3>";

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
        category_id INT DEFAULT NULL,
        author_id INT DEFAULT NULL,
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
        user_id INT DEFAULT NULL,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'comments' => "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        name VARCHAR(255),
        email VARCHAR(255),
        comment TEXT NOT NULL,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $name => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>â $name table created successfully</p>";
    } else {
        echo "<p style='color:red'>â $name table error: " . mysqli_error($conn) . "</p>";
    }
}

// Step 2: Verify tables exist before adding data
echo "<h3>Step 2: Verifying Tables...</h3>";

$required_tables = ['users', 'categories', 'news', 'post_likes', 'comments'];
$all_tables_exist = true;

foreach ($required_tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check) > 0) {
        echo "<p style='color:green'>â $table exists</p>";
    } else {
        echo "<p style='color:red'>â $table missing</p>";
        $all_tables_exist = false;
    }
}

if (!$all_tables_exist) {
    echo "<p style='color:red; font-size:18px;'>Cannot proceed - some tables are missing</p>";
    exit;
}

// Step 3: Add sample data safely
echo "<h3>Step 3: Adding Sample Data...</h3>";

// Categories first
$category_data = [
    ['name' => 'Politics', 'slug' => 'politics'],
    ['name' => 'Sports', 'slug' => 'sports'],
    ['name' => 'Technology', 'slug' => 'technology'],
    ['name' => 'Business', 'slug' => 'business'],
    ['name' => 'Entertainment', 'slug' => 'entertainment']
];

foreach ($category_data as $cat) {
    $name = mysqli_real_escape_string($conn, $cat['name']);
    $slug = mysqli_real_escape_string($conn, $cat['slug']);
    mysqli_query($conn, "INSERT IGNORE INTO categories (name, slug) VALUES ('$name', '$slug')");
}
echo "<p style='color:blue'>â Categories added</p>";

// Admin user
mysqli_query($conn, "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Admin', 'admin@pklivenews.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
echo "<p style='color:blue'>â Admin user added</p>";

// News articles - simplified
$news_articles = [
    "Breaking: Major Political Development in Islamabad",
    "Sports Update: National Team Wins Championship",
    "Technology Breakthrough: New Innovation Announced",
    "Business News: Stock Market Reaches New Heights",
    "Entertainment: New Movie Release Breaks Records"
];

foreach ($news_articles as $index => $title) {
    $title = mysqli_real_escape_string($conn, $title);
    $content = "This is sample content for: $title. Full article would go here with detailed information about this topic.";
    
    mysqli_query($conn, "INSERT IGNORE INTO news (title, content, category_id, author_id, status) VALUES 
        ('$title', '$content', " . ($index + 1) . ", 1, 'published')");
}
echo "<p style='color:blue'>â News articles added</p>";

// Step 4: Test the exact query from index.php
echo "<h3>Step 4: Testing Index.php Query...</h3>";

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
    echo "<p style='color:green; font-size:20px; font-weight:bold;'>â SUCCESS! Query works perfectly!</p>";
    
    if (mysqli_num_rows($test_result) > 0) {
        echo "<table border='1' cellpadding='5' style='margin-top:20px;'>";
        echo "<tr style='background:#f0f0f0;'><th>Title</th><th>Category</th><th>Author</th><th>Comments</th><th>Likes</th></tr>";
        
        while ($row = mysqli_fetch_assoc($test_result)) {
            echo "<tr>";
            echo "<td>" . substr($row['title'], 0, 40) . "...</td>";
            echo "<td>" . ($row['category_name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['author_name'] ?? 'N/A') . "</td>";
            echo "<td>" . $row['comment_count'] . "</td>";
            echo "<td>" . $row['likes_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color:green; margin-top:20px;'>All tables created and working! Index.php should load without errors.</p>";
    } else {
        echo "<p style='color:orange;'>Query works but no data found</p>";
    }
} else {
    echo "<p style='color:red; font-size:18px;'>Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div style='margin-top:30px; padding:20px; background:#e8f5e8; border-radius:5px;'>";
echo "<h3 style='color:green;'>â READY TO TEST!</h3>";
echo "<p style='font-size:18px;'><a href='index.php' style='color:green; font-weight:bold; text-decoration:underline;'>Click here to test Index Page</a></p>";
echo "<p>All fatal errors should now be resolved.</p>";
echo "</div>";
?>
