<?php
/**
 * Fix database schema issues preventing RSS imports
 */
require_once 'config/database.php';

echo "=== PK Live News - Import Schema Fix ===\n\n";

// Fix 1: Add missing news_type column
echo "1. Checking news_type column...\n";
$check_news_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($check_news_type) == 0) {
    echo "   Adding news_type column...\n";
    $add_news_type = "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual' AFTER status";
    if (mysqli_query($conn, $add_news_type)) {
        echo "   news_type column added successfully\n";
    } else {
        echo "   Error adding news_type: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "   news_type column exists\n";
}

// Fix 2: Add missing image_type column
echo "\n2. Checking image_type column...\n";
$check_image_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_type'");
if (mysqli_num_rows($check_image_type) == 0) {
    echo "   Adding image_type column...\n";
    $add_image_type = "ALTER TABLE news ADD COLUMN image_type VARCHAR(20) DEFAULT 'external' AFTER image";
    if (mysqli_query($conn, $add_image_type)) {
        echo "   image_type column added successfully\n";
    } else {
        echo "   Error adding image_type: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "   image_type column exists\n";
}

// Fix 3: Check news_sources table exists
echo "\n3. Checking news_sources table...\n";
$sources_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($sources_check) == 0) {
    echo "   Creating news_sources table...\n";
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
        echo "   news_sources table created successfully\n";
        
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
        echo "   Added 5 sample RSS sources\n";
    } else {
        echo "   Error creating news_sources: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "   news_sources table exists\n";
    
    // Check if sources exist
    $source_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'");
    $count = mysqli_fetch_assoc($source_count)['count'];
    echo "   Active RSS sources: $count\n";
}

// Fix 4: Create sample articles if none exist
echo "\n4. Checking for existing articles...\n";
$article_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$count = mysqli_fetch_assoc($article_count)['count'];
echo "   Current articles: $count\n";

if ($count == 0) {
    echo "   Creating sample articles...\n";
    
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
            echo "   Created: " . htmlspecialchars($article['title']) . "\n";
        }
    }
}

echo "\n=== Schema Fix Complete ===\n";
echo "Next steps:\n";
echo "1. Visit check_news.php to verify articles\n";
echo "2. Visit index.php to see articles on homepage\n";
echo "3. Run cron_import_news.php to test RSS imports\n";

mysqli_close($conn);
?>
