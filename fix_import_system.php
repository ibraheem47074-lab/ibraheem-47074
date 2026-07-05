<?php
/**
 * Complete Fix for RSS Import System
 */
require_once 'config/database.php';

echo "<h2>PK Live News - Complete Import System Fix</h2>";

// Step 1: Fix database schema
echo "<h3>Step 1: Fixing Database Schema</h3>";

// Add news_type column
$check_news_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($check_news_type) == 0) {
    $add_news_type = "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual' AFTER status";
    if (mysqli_query($conn, $add_news_type)) {
        echo "<p class='success'>✓ Added news_type column</p>";
    } else {
        echo "<p class='error'>✗ Error adding news_type: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ news_type column exists</p>";
}

// Add image_type column
$check_image_type = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_type'");
if (mysqli_num_rows($check_image_type) == 0) {
    $add_image_type = "ALTER TABLE news ADD COLUMN image_type VARCHAR(20) DEFAULT 'external' AFTER image";
    if (mysqli_query($conn, $add_image_type)) {
        echo "<p class='success'>✓ Added image_type column</p>";
    } else {
        echo "<p class='error'>✗ Error adding image_type: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ image_type column exists</p>";
}

// Add source_url column if missing
$check_source_url = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
if (mysqli_num_rows($check_source_url) == 0) {
    $add_source_url = "ALTER TABLE news ADD COLUMN source_url VARCHAR(500) DEFAULT NULL AFTER video_url";
    if (mysqli_query($conn, $add_source_url)) {
        echo "<p class='success'>✓ Added source_url column</p>";
    } else {
        echo "<p class='error'>✗ Error adding source_url: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ source_url column exists</p>";
}

// Step 2: Create news_sources table
echo "<h3>Step 2: Setting up RSS Sources</h3>";
$sources_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($sources_check) == 0) {
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
        echo "<p class='success'>✓ Created news_sources table</p>";
        
        // Add working RSS sources
        $working_sources = [
            ['Google News', 'https://news.google.com', 'https://news.google.com/rss'],
            ['NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml'],
            ['BBC News', 'https://www.bbc.com', 'http://feeds.bbci.co.uk/news/rss.xml'],
            ['Reuters', 'https://www.reuters.com', 'https://www.reuters.com/rssFeed/worldNews'],
            ['CNN', 'https://www.cnn.com', 'http://rss.cnn.com/rss/edition.rss']
        ];
        
        foreach ($working_sources as $source) {
            $insert_source = "INSERT INTO news_sources (name, url, rss_url, type, status) VALUES (?, ?, ?, 'rss', 'active')";
            $stmt = mysqli_prepare($conn, $insert_source);
            mysqli_stmt_bind_param($stmt, 'sss', $source[0], $source[1], $source[2]);
            mysqli_stmt_execute($stmt);
        }
        echo "<p class='success'>✓ Added 5 RSS sources</p>";
    } else {
        echo "<p class='error'>✗ Error creating news_sources: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ news_sources table exists</p>";
    $source_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'");
    $count = mysqli_fetch_assoc($source_count)['count'];
    echo "<p>Active RSS sources: $count</p>";
}

// Step 3: Create sample articles if database is empty
echo "<h3>Step 3: Creating Sample Articles</h3>";
$article_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$count = mysqli_fetch_assoc($article_count)['count'];

if ($count == 0) {
    $sample_articles = [
        [
            'title' => 'Breaking: Major Technology Breakthrough Announced',
            'slug' => 'breaking-technology-breakthrough-' . time(),
            'content' => '<p>Scientists have announced a major breakthrough in technology that could change the way we live and work.</p><p>The discovery, made after years of research, promises to revolutionize multiple industries including healthcare, transportation, and communications.</p>',
            'excerpt' => 'Scientists announce major technology breakthrough.',
            'status' => 'published',
            'news_type' => 'manual',
            'image_type' => 'external',
            'source_url' => 'https://pklivenews.com'
        ],
        [
            'title' => 'Local Sports Team Wins Championship',
            'slug' => 'local-sports-team-wins-' . time(),
            'content' => '<p>The local sports team has won the championship in an exciting match that kept fans on the edge of their seats.</p><p>The victory marks the team\'s first championship win in over a decade, bringing joy to thousands of supporters.</p>',
            'excerpt' => 'Local team celebrates championship victory.',
            'status' => 'published',
            'news_type' => 'manual',
            'image_type' => 'external',
            'source_url' => 'https://pklivenews.com'
        ],
        [
            'title' => 'New Business Initiative Boosts Economy',
            'slug' => 'business-initiative-economy-' . time(),
            'content' => '<p>A new business initiative has been launched to boost the local economy and create jobs.</p><p>The program includes tax incentives for small businesses and funding for startup companies, expected to create hundreds of new jobs in the coming year.</p>',
            'excerpt' => 'New business initiative promises economic growth.',
            'status' => 'featured',
            'news_type' => 'manual',
            'image_type' => 'external',
            'source_url' => 'https://pklivenews.com'
        ],
        [
            'title' => 'Community Comes Together for Environmental Project',
            'slug' => 'community-environmental-project-' . time(),
            'content' => '<p>Local residents have joined forces for a major environmental cleanup project.</p><p>The initiative aims to reduce pollution and improve green spaces across the city, with volunteers already making significant progress.</p>',
            'excerpt' => 'Community united for environmental cause.',
            'status' => 'published',
            'news_type' => 'manual',
            'image_type' => 'external',
            'source_url' => 'https://pklivenews.com'
        ],
        [
            'title' => 'Education Reform Announced by Government',
            'slug' => 'education-reform-announced-' . time(),
            'content' => '<p>The government has announced comprehensive education reforms aimed at improving learning outcomes.</p><p>The new policies include increased funding for schools, updated curriculum, and better teacher training programs.</p>',
            'excerpt' => 'Major education reforms unveiled.',
            'status' => 'published',
            'news_type' => 'manual',
            'image_type' => 'external',
            'source_url' => 'https://pklivenews.com'
        ]
    ];
    
    foreach ($sample_articles as $article) {
        $sql = "INSERT INTO news (title, slug, content, excerpt, status, news_type, image_type, source_url, published_at, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
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
            echo "<p class='success'>✓ Created: " . htmlspecialchars($article['title']) . "</p>";
        }
    }
} else {
    echo "<p class='success'>✓ Database already has $count articles</p>";
}

// Step 4: Test RSS import functionality
echo "<h3>Step 4: Testing RSS Import</h3>";
echo "<p>Running a quick RSS import test...</p>";

// Test one simple RSS feed
$test_url = 'https://news.google.com/rss';
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$xml_content = @file_get_contents($test_url, false, $context);
if ($xml_content !== false) {
    $xml = @simplexml_load_string($xml_content);
    if ($xml !== false) {
        echo "<p class='success'>✓ RSS feed test successful</p>";
        echo "<p>Found " . count($xml->channel->item) . " items in test feed</p>";
    } else {
        echo "<p class='error'>✗ RSS feed parsing failed</p>";
    }
} else {
    echo "<p class='error'>✗ RSS feed connection failed</p>";
    echo "<p>This may be due to network restrictions or firewall settings</p>";
}

echo "<h2>Fix Complete!</h2>";
echo "<div class='info'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='check_news.php' target='_blank'>Check News Status</a> - Verify articles are in database</li>";
echo "<li><a href='index.php' target='_blank'>Visit Homepage</a> - See articles displayed</li>";
echo "<li><a href='admin/' target='_blank'>Admin Panel</a> - Manage news and RSS sources</li>";
echo "<li><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron' target='_blank'>Test RSS Import</a> - Run import manually</li>";
echo "</ol>";
echo "</div>";

mysqli_close($conn);
?>

<style>
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; background: #f0f8ff; padding: 10px; border-radius: 5px; }
</style>
