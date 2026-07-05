<?php
/**
 * Quick RSS Sources Setup Script
 */

require_once __DIR__ . '/config/database.php';

echo "Setting up RSS Sources for PK Live News\n";
echo "========================================\n\n";

// Check if news_sources table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($table_check) == 0) {
    echo "Creating news_sources table...\n";
    
    $create_table = "CREATE TABLE news_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(500) NOT NULL,
        rss_url VARCHAR(500),
        type ENUM('rss', 'scrape') NOT NULL DEFAULT 'rss',
        category_id INT,
        scrape_frequency INT DEFAULT 60,
        status ENUM('active', 'inactive') DEFAULT 'active',
        last_scraped TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✓ news_sources table created successfully\n";
    } else {
        echo "✗ Error creating news_sources table: " . mysqli_error($conn) . "\n";
        exit(1);
    }
} else {
    echo "✓ news_sources table already exists\n";
}

// Check if categories exist and get default category
$categories_query = "SELECT id, name FROM categories WHERE status = 'active' LIMIT 1";
$categories_result = mysqli_query($conn, $categories_query);

if (mysqli_num_rows($categories_result) == 0) {
    echo "Creating default category...\n";
    
    $create_category = "INSERT INTO categories (name, slug, status, created_at) VALUES ('General', 'general', 'active', NOW())";
    if (mysqli_query($conn, $create_category)) {
        $category_id = mysqli_insert_id($conn);
        echo "✓ Default 'General' category created\n";
    } else {
        echo "✗ Error creating default category: " . mysqli_error($conn) . "\n";
        exit(1);
    }
} else {
    $category = mysqli_fetch_assoc($categories_result);
    $category_id = $category['id'];
    echo "✓ Using existing category: {$category['name']}\n";
}

// RSS Sources to add
$rss_sources = [
    [
        'name' => 'BBC News',
        'url' => 'https://www.bbc.com/news',
        'rss_url' => 'https://feeds.bbci.co.uk/news/rss.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'BBC World News',
        'url' => 'https://www.bbc.com/news/world',
        'rss_url' => 'https://feeds.bbci.co.uk/news/world/rss.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'CNN News',
        'url' => 'https://www.cnn.com',
        'rss_url' => 'https://rss.cnn.com/rss/edition.rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'CNN World',
        'url' => 'https://www.cnn.com/world',
        'rss_url' => 'https://rss.cnn.com/rss/edition_world.rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Reuters News',
        'url' => 'https://www.reuters.com',
        'rss_url' => 'https://feeds.reuters.com/reuters/topNews',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Reuters World',
        'url' => 'https://www.reuters.com/world',
        'rss_url' => 'https://feeds.reuters.com/reuters/worldNews',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Al Jazeera',
        'url' => 'https://www.aljazeera.com',
        'rss_url' => 'https://www.aljazeera.com/xml/rss/all.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Associated Press',
        'url' => 'https://apnews.com',
        'rss_url' => 'https://feeds.apnews.com/rss/apf-topnews',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Fox News',
        'url' => 'https://www.foxnews.com',
        'rss_url' => 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'The Guardian',
        'url' => 'https://www.theguardian.com',
        'rss_url' => 'https://www.theguardian.com/world/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'The New York Times',
        'url' => 'https://www.nytimes.com',
        'rss_url' => 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Washington Post',
        'url' => 'https://www.washingtonpost.com',
        'rss_url' => 'https://www.washingtonpost.com/world/rss/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Bloomberg',
        'url' => 'https://www.bloomberg.com',
        'rss_url' => 'https://feeds.bloomberg.com/markets/news.rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Financial Times',
        'url' => 'https://www.ft.com',
        'rss_url' => 'https://www.ft.com/rss/home',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'CNBC',
        'url' => 'https://www.cnbc.com',
        'rss_url' => 'https://www.cnbc.com/id/100003114/device/rss/rss.html',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'NBC News',
        'url' => 'https://www.nbcnews.com',
        'rss_url' => 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'CBS News',
        'url' => 'https://www.cbsnews.com',
        'rss_url' => 'https://www.cbsnews.com/rss/live/rss.rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'ABC News',
        'url' => 'https://abcnews.go.com',
        'rss_url' => 'https://abcnews.go.com/xml/rss/abc_us_topstories.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'NPR News',
        'url' => 'https://www.npr.org',
        'rss_url' => 'https://feeds.npr.org/1001/rss.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'PBS NewsHour',
        'url' => 'https://www.pbs.org/newshour',
        'rss_url' => 'https://www.pbs.org/newshour/rss/feed',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Deutsche Welle (DW)',
        'url' => 'https://www.dw.com',
        'rss_url' => 'https://www.dw.com/en/rss/top-stories',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'France 24',
        'url' => 'https://www.france24.com',
        'rss_url' => 'https://www.france24.com/en/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'RT News',
        'url' => 'https://www.rt.com',
        'rss_url' => 'https://www.rt.com/rss/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'CGTN',
        'url' => 'https://news.cgtn.com',
        'rss_url' => 'https://news.cgtn.com/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'NDTV',
        'url' => 'https://www.ndtv.com',
        'rss_url' => 'https://feeds.ndtv.com/ndtv/rss/top-stories.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Times of India',
        'url' => 'https://timesofindia.indiatimes.com',
        'rss_url' => 'https://timesofindia.indiatimes.com/rssfeedstopstories.cms',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Hindustan Times',
        'url' => 'https://www.hindustantimes.com',
        'rss_url' => 'https://www.hindustantimes.com/rss/topnews/rssfeed.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Dawn News',
        'url' => 'https://www.dawn.com',
        'rss_url' => 'https://www.dawn.com/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Geo News',
        'url' => 'https://www.geo.tv',
        'rss_url' => 'https://www.geo.tv/rss/1.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'ARY News',
        'url' => 'https://arynews.tv',
        'rss_url' => 'https://arynews.tv/feed/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Express Tribune',
        'url' => 'https://tribune.com.pk',
        'rss_url' => 'https://tribune.com.pk/rss/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'The News International',
        'url' => 'https://www.thenews.com.pk',
        'rss_url' => 'https://www.thenews.com.pk/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Pakistan Today',
        'url' => 'https://www.pakistantoday.com.pk',
        'rss_url' => 'https://www.pakistantoday.com.pk/feed/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Dunya News',
        'url' => 'https://www.dunyanews.tv',
        'rss_url' => 'https://www.dunyanews.tv/rss.xml',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'Samaa TV',
        'url' => 'https://www.samaa.tv',
        'rss_url' => 'https://www.samaa.tv/feed/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => '24 News HD',
        'url' => 'https://www.24news.tv',
        'rss_url' => 'https://www.24news.tv/feed/',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'BBC Urdu',
        'url' => 'https://www.bbc.com/urdu',
        'rss_url' => 'https://www.bbc.com/urdu/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'VOA Urdu',
        'url' => 'https://www.voaurdu.com',
        'rss_url' => 'https://www.voaurdu.com/a/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ],
    [
        'name' => 'RFE/RL Urdu',
        'url' => 'https://urdu.rferl.org',
        'rss_url' => 'https://urdu.rferl.org/rss',
        'type' => 'rss',
        'category_id' => $category_id,
        'scrape_frequency' => 30
    ]
];

echo "\nAdding RSS sources...\n";
$added_count = 0;

foreach ($rss_sources as $source) {
    // Check if source already exists
    $check_query = "SELECT id FROM news_sources WHERE name = ? OR rss_url = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 'ss', $source['name'], $source['rss_url']);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Insert new source
        $insert_query = "INSERT INTO news_sources (name, url, rss_url, type, category_id, scrape_frequency, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'ssssii', 
            $source['name'], 
            $source['url'], 
            $source['rss_url'], 
            $source['type'], 
            $source['category_id'], 
            $source['scrape_frequency']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Added: {$source['name']}\n";
            $added_count++;
        } else {
            echo "✗ Error adding {$source['name']}: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "- Skipped (already exists): {$source['name']}\n";
    }
}

echo "\nSetup complete! Added $added_count new RSS sources.\n";
echo "\nNext steps:\n";
echo "1. Visit admin/manage-sources.php to manage sources\n";
echo "2. Run cron_import_news.php?cron_key=pk_live_news_2024_cron to test import\n";
echo "3. Set up a cron job to run the import script regularly\n";

// Test one RSS feed
echo "\nTesting RSS feed import...\n";
try {
    require_once 'includes/enhanced_rss_parser.php';
    $parser = new EnhancedRSSParser();
    
    // Test BBC feed
    $test_feed = 'https://feeds.bbci.co.uk/news/rss.xml';
    echo "Testing: $test_feed\n";
    
    $validation = $parser->validateFeed($test_feed);
    if ($validation['valid']) {
        echo "✓ Feed is valid - {$validation['items_count']} items\n";
        
        $articles = $parser->parseRSS($test_feed);
        echo "✓ Successfully parsed " . count($articles) . " articles\n";
        
        if (!empty($articles)) {
            $first = $articles[0];
            echo "✓ Sample article: " . substr($first['title'], 0, 60) . "...\n";
            echo "✓ Has image: " . (!empty($first['image']) ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "✗ Feed validation failed: " . $validation['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ RSS test failed: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>
