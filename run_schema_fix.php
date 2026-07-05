<!DOCTYPE html>
<html>
<head>
    <title>PK Live News - Schema Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>PK Live News - Import Schema Fix</h1>
    
    <?php
    require_once 'config/database.php';
    
    echo "<h2>Fixing database schema issues...</h2>\n";
    
    // Fix 1: Add missing news_type column
    echo "<h3>1. Checking news_type column...</h3>\n";
    $check_news_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
    if (mysqli_num_rows($check_news_type) == 0) {
        echo "<p class='info'>Adding news_type column...</p>\n";
        $add_news_type = "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual' AFTER status";
        if (mysqli_query($conn, $add_news_type)) {
            echo "<p class='success'>news_type column added successfully</p>\n";
        } else {
            echo "<p class='error'>Error adding news_type: " . mysqli_error($conn) . "</p>\n";
        }
    } else {
        echo "<p class='success'>news_type column exists</p>\n";
    }
    
    // Fix 2: Add missing image_type column
    echo "<h3>2. Checking image_type column...</h3>\n";
    $check_image_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_type'");
    if (mysqli_num_rows($check_image_type) == 0) {
        echo "<p class='info'>Adding image_type column...</p>\n";
        $add_image_type = "ALTER TABLE news ADD COLUMN image_type VARCHAR(20) DEFAULT 'external' AFTER image";
        if (mysqli_query($conn, $add_image_type)) {
            echo "<p class='success'>image_type column added successfully</p>\n";
        } else {
            echo "<p class='error'>Error adding image_type: " . mysqli_error($conn) . "</p>\n";
        }
    } else {
        echo "<p class='success'>image_type column exists</p>\n";
    }
    
    // Fix 3: Check news_sources table exists
    echo "<h3>3. Checking news_sources table...</h3>\n";
    $sources_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
    if (mysqli_num_rows($sources_check) == 0) {
        echo "<p class='info'>Creating news_sources table...</p>\n";
        $create_sources = "CREATE TABLE `news_sources` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `url` varchar(500) DEFAULT NULL,
            `rss_url` varchar(500) NOT NULL,
            `type` enum('rss','api','scrape') DEFAULT 'rss',
            `status` enum('active','inactive','error') DEFAULT 'active',
            `last_import` datetime DEFAULT NULL,
            `articles_imported` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `rss_url` (`rss_url`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $create_sources)) {
            echo "<p class='success'>news_sources table created successfully</p>\n";
            
            // Add sample RSS sources
            $sample_sources = [
                ['BBC News', 'https://www.bbc.com', 'http://feeds.bbci.co.uk/news/rss.xml'],
                ['CNN', 'https://www.cnn.com', 'http://rss.cnn.com/rss/edition.rss'],
                ['Reuters', 'https://www.reuters.com', 'https://www.reuters.com/rssFeed/worldNews'],
                ['Google News', 'https://news.google.com', 'https://news.google.com/rss'],
                ['NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml']
            ];
            
            foreach ($sample_sources as $source) {
                $insert_source = "INSERT INTO news_sources (name, url, rss_url, type, status) VALUES (?, ?, ?, 'rss', 'active')";
                $stmt = mysqli_prepare($conn, $insert_source);
                mysqli_stmt_bind_param($stmt, 'sss', $source[0], $source[1], $source[2]);
                mysqli_stmt_execute($stmt);
            }
            echo "<p class='success'>Added 5 sample RSS sources</p>\n";
        } else {
            echo "<p class='error'>Error creating news_sources: " . mysqli_error($conn) . "</p>\n";
        }
    } else {
        echo "<p class='success'>news_sources table exists</p>\n";
        
        // Check if sources exist
        $source_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'");
        $count = mysqli_fetch_assoc($source_count)['count'];
        echo "<p>Active RSS sources: $count</p>\n";
    }
    
    // Fix 4: Create sample articles if none exist
    echo "<h3>4. Checking for existing articles...</h3>\n";
    $article_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $count = mysqli_fetch_assoc($article_count)['count'];
    echo "<p>Current articles: $count</p>\n";
    
    if ($count == 0) {
        echo "<p class='info'>Creating sample articles...</p>\n";
        
        $sample_articles = [
            [
                'title' => 'Breaking: Major Technology Breakthrough Announced',
                'slug' => 'breaking-technology-breakthrough-' . time(),
                'content' => '<p>Scientists have announced a major breakthrough in technology that could change the way we live and work.</p><p>The discovery, made after years of research, promises to revolutionize multiple industries including healthcare, transportation, and communications.</p>',
                'excerpt' => 'Scientists announce major technology breakthrough.',
                'status' => 'published',
                'news_type' => 'manual',
                'image_type' => 'external'
            ],
            [
                'title' => 'Local Sports Team Wins Championship',
                'slug' => 'local-sports-team-wins-' . time(),
                'content' => '<p>The local sports team has won the championship in an exciting match that kept fans on the edge of their seats.</p><p>The victory marks the team\'s first championship win in over a decade.</p>',
                'excerpt' => 'Local team celebrates championship victory.',
                'status' => 'published',
                'news_type' => 'manual',
                'image_type' => 'external'
            ],
            [
                'title' => 'New Business Initiative Boosts Economy',
                'slug' => 'business-initiative-economy-' . time(),
                'content' => '<p>A new business initiative has been launched to boost the local economy and create jobs.</p><p>The program includes tax incentives for small businesses and funding for startup companies.</p>',
                'excerpt' => 'New business initiative promises economic growth.',
                'status' => 'featured',
                'news_type' => 'manual',
                'image_type' => 'external'
            ]
        ];
        
        foreach ($sample_articles as $article) {
            $sql = "INSERT INTO news (title, slug, content, excerpt, status, news_type, image_type, published_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssss', 
                $article['title'], 
                $article['slug'], 
                $article['content'], 
                $article['excerpt'], 
                $article['status'], 
                $article['news_type'],
                $article['image_type']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>Created: " . htmlspecialchars($article['title']) . "</p>\n";
            }
        }
    }
    
    echo "<h2>Schema Fix Complete!</h2>\n";
    echo "<div class='info'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='check_news.php'>Visit check_news.php</a> to verify articles</li>";
    echo "<li><a href='index.php'>Visit index.php</a> to see articles on homepage</li>";
    echo "<li><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron'>Test RSS imports</a></li>";
    echo "</ol>";
    echo "</div>";
    
    mysqli_close($conn);
    ?>
    
</body>
</html>
