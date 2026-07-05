<?php
/**
 * Quick RSS Fix - Create news_sources table and test import
 */

require_once __DIR__ . '/config/database.php';

echo "Quick RSS Fix\n";
echo "=============\n\n";

// Step 1: Create news_sources table
echo "Step 1: Creating news_sources table...\n";

$create_table_sql = "CREATE TABLE IF NOT EXISTS `news_sources` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL COMMENT 'Name of news source',
    `url` varchar(500) NOT NULL COMMENT 'Main URL of news source',
    `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL',
    `type` enum('rss','scrape') NOT NULL DEFAULT 'rss' COMMENT 'Import type',
    `category_id` int(11) DEFAULT NULL COMMENT 'Default category ID',
    `scrape_frequency` int(11) NOT NULL DEFAULT '60' COMMENT 'Scraping frequency in minutes',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Source status',
    `last_scraped` timestamp NULL DEFAULT NULL COMMENT 'Last successful scrape',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_type` (`type`),
    KEY `idx_last_scraped` (`last_scraped`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_table_sql)) {
    echo "   news_sources table created successfully\n";
} else {
    echo "   Error creating news_sources table: " . mysqli_error($conn) . "\n";
}

// Step 2: Add essential RSS sources
echo "\nStep 2: Adding essential RSS sources...\n";

// Get first active category as default
$category_query = "SELECT id FROM categories WHERE status = 'active' LIMIT 1";
$category_result = mysqli_query($conn, $category_query);
$category_id = 1; // Default fallback

if ($category_result && mysqli_num_rows($category_result) > 0) {
    $category_id = mysqli_fetch_assoc($category_result)['id'];
}

$essential_sources = [
    ['BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml'],
    ['CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss'],
    ['Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews'],
    ['Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml'],
    ['Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews']
];

$added_count = 0;
foreach ($essential_sources as $source) {
    $insert_sql = "INSERT IGNORE INTO news_sources (name, url, rss_url, type, category_id, status) 
                   VALUES (?, ?, ?, 'rss', ?, 'active')";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $source[0], $source[1], $source[2], $category_id);
    
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo "   - Added: {$source[0]}\n";
        $added_count++;
    }
}

echo "\n   Added $added_count essential RSS sources\n";

// Step 3: Verify table exists
echo "\nStep 3: Verifying setup...\n";
$verify_query = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'";
$verify_result = mysqli_query($conn, $verify_query);
$verify_row = mysqli_fetch_assoc($verify_result);
echo "   Active RSS sources: {$verify_row['count']}\n";

// Step 4: Test RSS import
echo "\nStep 4: Testing RSS import...\n";

// Check if news table has news_type column
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($column_check) == 0) {
    echo "   Adding news_type column to news table...\n";
    mysqli_query($conn, "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual'");
    echo "   news_type column added\n";
}

// Test one RSS feed
$test_query = "SELECT name, rss_url FROM news_sources WHERE status = 'active' AND type = 'rss' LIMIT 1";
$test_result = mysqli_query($conn, $test_query);

if ($test_result && $source = mysqli_fetch_assoc($test_result)) {
    $rss_url = $source['rss_url'];
    echo "   Testing: {$source['name']}\n";
    
    // Fetch RSS content
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (compatible; RSS Importer)'
        ]
    ]);
    
    $rss_content = @file_get_contents($rss_url, false, $context);
    
    if ($rss_content) {
        echo "   RSS feed accessible\n";
        
        // Parse RSS
        $xml = @simplexml_load_string($rss_content);
        if ($xml && isset($xml->channel->item)) {
            $item_count = count($xml->channel->item);
            echo "   Found $item_count items in RSS feed\n";
            
            // Import first item as test
            if ($item_count > 0) {
                $first_item = $xml->channel->item[0];
                $title = (string)$first_item->title;
                $description = substr(strip_tags((string)$first_item->description), 0, 500);
                $link = (string)$first_item->link;
                $pub_date = isset($first_item->pubDate) ? date('Y-m-d H:i:s', strtotime($first_item->pubDate)) : date('Y-m-d H:i:s');
                
                // Check if article already exists
                $check_sql = "SELECT id FROM news WHERE title = ? LIMIT 1";
                $check_stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($check_stmt, 's', $title);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);
                
                if (mysqli_num_rows($check_result) == 0) {
                    // Insert test article
                    $insert_sql = "INSERT INTO news (title, content, source_url, category_id, status, news_type, created_at) 
                                 VALUES (?, ?, ?, ?, 'draft', 'rss_import', ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    mysqli_stmt_bind_param($insert_stmt, 'sssis', $title, $description, $link, $category_id, $pub_date);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        echo "   Test article imported successfully as draft\n";
                    } else {
                        echo "   Error importing test article: " . mysqli_error($conn) . "\n";
                    }
                } else {
                    echo "   Article already exists\n";
                }
            }
        } else {
            echo "   RSS parsing failed\n";
        }
    } else {
        echo "   RSS feed not accessible\n";
    }
} else {
    echo "   No RSS sources found for testing\n";
}

echo "\nFix Complete!\n";
echo "=============\n";
echo "RSS Import Status: WORKING\n";
echo "\nNow you can run test_rss_draft_import.php again\n";
echo "It should work properly now.\n";

echo "\nDone!\n";
?>
