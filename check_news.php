<?php
// Simple diagnostic script
echo "<h2>PK Live News - Post Display Diagnostic</h2>";

// Try to connect to database
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'pk_live_news';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if news table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p style='color: red;'>❌ News table does not exist</p>";
    
    // Create news table
    $sql = "CREATE TABLE `news` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(500) NOT NULL,
        `slug` varchar(500) NOT NULL,
        `content` text NOT NULL,
        `excerpt` text,
        `image` varchar(500),
        `video_url` varchar(500),
        `category_id` int(11) DEFAULT NULL,
        `author_id` int(11) DEFAULT NULL,
        `status` enum('draft','published','featured','archived') DEFAULT 'draft',
        `is_breaking` tinyint(1) DEFAULT 0,
        `views` int(11) DEFAULT 0,
        `likes_count` int(11) DEFAULT 0,
        `comment_count` int(11) DEFAULT 0,
        `published_at` datetime DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `status` (`status`),
        KEY `published_at` (`published_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ News table created</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating news table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ News table exists</p>";
}

// Check news count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$count = mysqli_fetch_assoc($result)['count'];
echo "<p><strong>Total news articles:</strong> $count</p>";

// Check published news count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'");
$published_count = mysqli_fetch_assoc($result)['count'];
echo "<p><strong>Published news articles:</strong> $published_count</p>";

if ($published_count == 0) {
    echo "<p style='color: orange;'>⚠️ No published news found. Creating sample articles...</p>";
    
    // Create sample articles
    $sample_articles = [
        [
            'title' => 'Breaking: Major Technology Breakthrough Announced',
            'slug' => 'breaking-technology-breakthrough-' . time(),
            'content' => '<p>Scientists have announced a major breakthrough in technology that could change the way we live and work.</p>',
            'excerpt' => 'Scientists announce major technology breakthrough.',
            'status' => 'published',
            'is_breaking' => 1
        ],
        [
            'title' => 'Local Sports Team Wins Championship',
            'slug' => 'local-sports-team-wins-' . time(),
            'content' => '<p>The local sports team has won the championship in an exciting match.</p>',
            'excerpt' => 'Local team celebrates championship victory.',
            'status' => 'published',
            'is_breaking' => 0
        ],
        [
            'title' => 'New Business Initiative Boosts Economy',
            'slug' => 'business-initiative-economy-' . time(),
            'content' => '<p>A new business initiative has been launched to boost the local economy.</p>',
            'excerpt' => 'New business initiative promises economic growth.',
            'status' => 'featured',
            'is_breaking' => 0
        ]
    ];
    
    foreach ($sample_articles as $article) {
        $sql = "INSERT INTO news (title, slug, content, excerpt, status, is_breaking, published_at, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssii', 
            $article['title'], 
            $article['slug'], 
            $article['content'], 
            $article['excerpt'], 
            $article['status'], 
            $article['is_breaking']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Created: " . htmlspecialchars($article['title']) . "</p>";
        }
    }
}

// Test the same query as index.php
echo "<h3>Testing Index Query</h3>";
$test_query = "SELECT n.*, c.name as category_name, u.name as author_name
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              LEFT JOIN users u ON n.author_id = u.id 
              WHERE n.status = 'published' AND n.published_at <= NOW() 
              ORDER BY n.published_at DESC LIMIT 5";

$result = mysqli_query($conn, $test_query);
if ($result) {
    echo "<p style='color: green;'>✅ Query executed successfully</p>";
    echo "<p><strong>Results found:</strong> " . mysqli_num_rows($result) . "</p>";
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #f0f0f0;'><th>Title</th><th>Status</th><th>Published At</th><th>Category</th><th>Author</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td><strong style='color: green;'>" . $row['status'] . "</strong></td>";
            echo "<td>" . $row['published_at'] . "</td>";
            echo "<td>" . htmlspecialchars($row['category_name'] ?? 'No Category') . "</td>";
            echo "<td>" . htmlspecialchars($row['author_name'] ?? 'No Author') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>❌ Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Current Server Time</h3>";
echo "<p>" . date('Y-m-d H:i:s') . "</p>";

echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li><a href='index.php'>Visit Homepage</a> to check if posts are now showing</li>";
echo "<li><a href='admin/'>Visit Admin Panel</a> to manage news (admin@pklivenews.com / admin123)</li>";
echo "<li>If posts still don't show, check the error logs in XAMPP</li>";
echo "</ol>";
echo "</div>";

mysqli_close($conn);
?>
