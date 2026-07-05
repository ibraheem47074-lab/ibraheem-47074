<?php
require_once 'config/database.php';

echo "<h2>Simple Fix - Create Tables Step by Step</h2>";

// Step 1: Create basic tables without complex columns
echo "<h3>Step 1: Creating Basic Tables</h3>";

// Users table
$sql1 = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','reporter','user') DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql1)) {
    echo "<p style='color:green'>â Users table created</p>";
} else {
    echo "<p style='color:red'>â Users table error: " . mysqli_error($conn) . "</p>";
}

// Categories table
$sql2 = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql2)) {
    echo "<p style='color:green'>â Categories table created</p>";
} else {
    echo "<p style='color:red'>â Categories table error: " . mysqli_error($conn) . "</p>";
}

// News table - simplified
$sql3 = "CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content LONGTEXT,
    status ENUM('published','draft','featured','archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql3)) {
    echo "<p style='color:green'>â News table created</p>";
} else {
    echo "<p style='color:red'>â News table error: " . mysqli_error($conn) . "</p>";
}

// Post likes table
$sql4 = "CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql4)) {
    echo "<p style='color:green'>â Post likes table created</p>";
} else {
    echo "<p style='color:red'>â Post likes table error: " . mysqli_error($conn) . "</p>";
}

// Comments table
$sql5 = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql5)) {
    echo "<p style='color:green'>â Comments table created</p>";
} else {
    echo "<p style='color:red'>â Comments table error: " . mysqli_error($conn) . "</p>";
}

// Step 2: Add basic sample data
echo "<h3>Step 2: Adding Sample Data</h3>";

// Add admin user
$result1 = mysqli_query($conn, "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Admin', 'admin@pklivenews.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
if ($result1) {
    echo "<p style='color:blue'>â Admin user added</p>";
}

// Add categories
$result2 = mysqli_query($conn, "INSERT IGNORE INTO categories (name) VALUES 
    ('Politics'), ('Sports'), ('Technology'), ('Business'), ('Entertainment')");
if ($result2) {
    echo "<p style='color:blue'>â Categories added</p>";
}

// Add sample news
$result3 = mysqli_query($conn, "INSERT IGNORE INTO news (title, content, status) VALUES 
    ('Breaking News: Major Development', 'This is sample content for the breaking news article.', 'published'),
    ('Sports Update: Team Wins', 'Sample content for sports update article.', 'published'),
    ('Technology News: New Device', 'Sample content for technology news article.', 'published'),
    ('Business Report: Market Update', 'Sample content for business report article.', 'published'),
    ('Entertainment: Movie Release', 'Sample content for entertainment article.', 'published')");
if ($result3) {
    echo "<p style='color:blue'>â Sample news added</p>";
}

// Step 3: Test simplified query
echo "<h3>Step 3: Testing Simplified Query</h3>";

$simple_query = "SELECT n.*, 
    (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
    (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
    FROM news n 
    WHERE n.status = 'published' 
    ORDER BY n.created_at DESC LIMIT 5";

$test_result = mysqli_query($conn, $simple_query);

if ($test_result) {
    echo "<p style='color:green; font-size:18px; font-weight:bold;'>â SUCCESS! Simplified query works!</p>";
    
    if (mysqli_num_rows($test_result) > 0) {
        echo "<table border='1' cellpadding='5' style='margin-top:20px;'>";
        echo "<tr style='background:#f0f0f0;'><th>Title</th><th>Comments</th><th>Likes</th></tr>";
        
        while ($row = mysqli_fetch_assoc($test_result)) {
            echo "<tr>";
            echo "<td>" . substr($row['title'], 0, 40) . "...</td>";
            echo "<td>" . $row['comment_count'] . "</td>";
            echo "<td>" . $row['likes_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color:green; margin-top:20px;'>Basic functionality working! Index.php should load without fatal errors.</p>";
    }
} else {
    echo "<p style='color:red'>â Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<div style='margin-top:30px; padding:20px; background:#e8f5e8; border-radius:5px;'>";
echo "<h3 style='color:green;'>â BASIC FIX COMPLETE!</h3>";
echo "<p style='font-size:18px;'><a href='index.php' style='color:green; font-weight:bold; text-decoration:underline;'>Test Index Page Now</a></p>";
echo "<p>This creates minimal tables to stop the fatal errors.</p>";
echo "</div>";
?>
