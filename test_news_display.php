<?php
// Simple test page to debug news display issues
require_once 'config/database.php';
require_once 'config/helpers.php';

echo "<h1>PK Live News - Display Test</h1>";

// Test database connection
if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}
echo "<p style='color: green;'>✅ Database connected</p>";

// Test if news table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p style='color: red;'>❌ News table doesn't exist</p>";
    
    // Create news table
    $create_sql = "CREATE TABLE `news` (
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
    
    if (mysqli_query($conn, $create_sql)) {
        echo "<p style='color: green;'>✅ News table created</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create news table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ News table exists</p>";
}

// Check news count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$count = mysqli_fetch_assoc($result)['count'];
echo "<p><strong>Total news articles:</strong> $count</p>";

// Check published count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'");
$published_count = mysqli_fetch_assoc($result)['count'];
echo "<p><strong>Published articles:</strong> $published_count</p>";

// If no published articles, create some
if ($published_count == 0) {
    echo "<p style='color: orange;'>⚠️ No published articles found. Creating sample articles...</p>";
    
    $sample_articles = [
        [
            'title' => 'Breaking: Major Technology Breakthrough Announced Today',
            'slug' => 'breaking-technology-breakthrough-' . time() . '1',
            'content' => '<p>Scientists have announced a major breakthrough in technology that could change the way we live and work. The discovery, made after years of research, promises to revolutionize multiple industries.</p><p>Experts say this development could have far-reaching implications for the future of artificial intelligence and renewable energy.</p>',
            'excerpt' => 'Scientists announce major technology breakthrough with far-reaching implications for AI and renewable energy.',
            'status' => 'featured',
            'is_breaking' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Local Sports Team Wins Championship in Thrilling Final',
            'slug' => 'local-sports-team-wins-' . time() . '2',
            'content' => '<p>The local sports team has won the championship in an exciting match that kept fans on the edge of their seats. The team showed exceptional skill and determination throughout the season.</p><p>Captain John Smith led the team to victory with a stunning performance in the final match, scoring the winning goal in the last minute.</p>',
            'excerpt' => 'Local team celebrates championship victory after thrilling final match.',
            'status' => 'published',
            'is_breaking' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'title' => 'New Business Initiative Boosts Local Economy',
            'slug' => 'business-initiative-economy-' . time() . '3',
            'content' => '<p>A new business initiative has been launched to boost the local economy and create jobs for residents. The program, supported by local government and private investors, aims to stimulate economic growth.</p><p>Business leaders have expressed optimism about the initiative\'s potential to create sustainable employment opportunities.</p>',
            'excerpt' => 'New business initiative promises economic growth and job creation.',
            'status' => 'published',
            'is_breaking' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'title' => 'Entertainment Industry Adapts to Digital Age',
            'slug' => 'entertainment-digital-age-' . time() . '4',
            'content' => '<p>The entertainment industry is rapidly adapting to the digital age with new technologies and platforms emerging. Streaming services and digital content creation are reshaping how we consume entertainment.</p><p>Industry experts discuss the challenges and opportunities presented by this digital transformation.</p>',
            'excerpt' => 'Entertainment industry embraces digital transformation with new technologies.',
            'status' => 'published',
            'is_breaking' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ],
        [
            'title' => 'International Summit Addresses Global Challenges',
            'slug' => 'international-summit-' . time() . '5',
            'content' => '<p>World leaders gathered at an international summit to address pressing global challenges including climate change, economic inequality, and international cooperation.</p><p>The summit resulted in several agreements and commitments to work together on these critical issues.</p>',
            'excerpt' => 'World leaders commit to cooperation on global challenges at international summit.',
            'status' => 'published',
            'is_breaking' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ]
    ];
    
    foreach ($sample_articles as $article) {
        $sql = "INSERT INTO news (title, slug, content, excerpt, status, is_breaking, published_at, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssiss', 
            $article['title'], 
            $article['slug'], 
            $article['content'], 
            $article['excerpt'], 
            $article['status'], 
            $article['is_breaking'],
            $article['published_at']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Created: " . htmlspecialchars($article['title']) . "</p>";
        }
    }
}

// Test the exact same query as index.php
echo "<h2>Testing Index.php Query</h2>";

$per_page = 15;
$offset = 0;

$latest_query = "SELECT n.*, c.name as category_name, u.name as author_name
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' AND n.published_at <= NOW() 
                ORDER BY n.published_at DESC LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $latest_query);
mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
mysqli_stmt_execute($stmt);
$latest_result = mysqli_stmt_get_result($stmt);

if ($latest_result) {
    echo "<p style='color: green;'>✅ Query executed successfully</p>";
    echo "<p><strong>Results found:</strong> " . mysqli_num_rows($latest_result) . "</p>";
    
    if (mysqli_num_rows($latest_result) > 0) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>News Articles Found:</h3>";
        
        while ($news = mysqli_fetch_assoc($latest_result)) {
            echo "<div style='border: 1px solid #eee; padding: 10px; margin: 10px 0; border-radius: 3px; background: #f9f9f9;'>";
            echo "<h4>" . htmlspecialchars($news['title']) . "</h4>";
            echo "<p><strong>Status:</strong> " . $news['status'] . "</p>";
            echo "<p><strong>Published:</strong> " . $news['published_at'] . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($news['category_name'] ?? 'No Category') . "</p>";
            echo "<p><strong>Author:</strong> " . htmlspecialchars($news['author_name'] ?? 'No Author') . "</p>";
            echo "<p><strong>Excerpt:</strong> " . htmlspecialchars(substr($news['excerpt'] ?? '', 0, 150)) . "...</p>";
            echo "<p><strong>Slug:</strong> " . $news['slug'] . "</p>";
            echo "<a href='news.php?slug=" . $news['slug'] . "' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>View Article</a>";
            echo "</div>";
        }
        
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No results found from query</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Query failed: " . mysqli_error($conn) . "</p>";
}

// Test featured news query
echo "<h2>Testing Featured News Query</h2>";

$featured_query = "SELECT n.*, c.name as category_name, u.name as author_name
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  LEFT JOIN users u ON n.author_id = u.id 
                  WHERE n.status = 'featured' AND n.published_at <= NOW() 
                  ORDER BY n.published_at DESC LIMIT 3";

$featured_result = mysqli_query($conn, $featured_query);

if ($featured_result) {
    echo "<p><strong>Featured articles found:</strong> " . mysqli_num_rows($featured_result) . "</p>";
    
    if (mysqli_num_rows($featured_result) > 0) {
        while ($news = mysqli_fetch_assoc($featured_result)) {
            echo "<div style='border: 1px solid #ffc107; padding: 10px; margin: 10px 0; border-radius: 3px; background: #fff3cd;'>";
            echo "<h4>⭐ " . htmlspecialchars($news['title']) . "</h4>";
            echo "<p><strong>Status:</strong> " . $news['status'] . "</p>";
            echo "<a href='news.php?slug=" . $news['slug'] . "' style='background: #ffc107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>View Featured</a>";
            echo "</div>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Featured query failed: " . mysqli_error($conn) . "</p>";
}

// Test functions
echo "<h2>Testing Helper Functions</h2>";

$test_news = [
    'title' => 'Test Article Title',
    'slug' => 'test-article',
    'excerpt' => 'This is a test excerpt',
    'status' => 'published'
];

echo "<p><strong>get_news_title():</strong> " . get_news_title($test_news) . "</p>";
echo "<p><strong>format_news_date():</strong> " . format_news_date(date('Y-m-d H:i:s')) . "</p>";
echo "<p><strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<div style='margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 5px;'>";
echo "<h3>Summary</h3>";
echo "<p>If you can see articles above, then the database and queries are working correctly.</p>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Visit Homepage</a></p>";
echo "<p><a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Visit Admin Panel</a></p>";
echo "</div>";

mysqli_close($conn);
?>
