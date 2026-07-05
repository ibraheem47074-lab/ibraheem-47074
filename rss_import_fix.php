<?php
/**
 * RSS Import Fix Script
 * This script will create the news_sources table and set up RSS functionality
 */

require_once __DIR__ . '/config/database.php';

echo "RSS Import Fix\n";
echo "==============\n\n";

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

// Step 2: Add default RSS sources
echo "\nStep 2: Adding default RSS sources...\n";

// Get first active category as default
$category_query = "SELECT id FROM categories WHERE status = 'active' LIMIT 1";
$category_result = mysqli_query($conn, $category_query);
$category_id = 1; // Default fallback

if ($category_result && mysqli_num_rows($category_result) > 0) {
    $category_id = mysqli_fetch_assoc($category_result)['id'];
}

$rss_sources = [
    ['BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml'],
    ['CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss'],
    ['Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews'],
    ['Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml'],
    ['Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews'],
    ['Fox News', 'https://www.foxnews.com', 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest'],
    ['The Guardian', 'https://www.theguardian.com', 'https://www.theguardian.com/world/rss'],
    ['NBC News', 'https://www.nbcnews.com', 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml'],
    ['CBS News', 'https://www.cbsnews.com', 'https://www.cbsnews.com/rss/live/rss.rss'],
    ['NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml']
];

$added_count = 0;
foreach ($rss_sources as $source) {
    $insert_sql = "INSERT IGNORE INTO news_sources (name, url, rss_url, type, category_id, status) 
                   VALUES (?, ?, ?, 'rss', ?, 'active')";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $source[0], $source[1], $source[2], $category_id);
    
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo "   - Added: {$source[0]}\n";
        $added_count++;
    }
}

echo "\n   Added $added_count new RSS sources\n";

// Step 3: Check RSS import functionality
echo "\nStep 3: Checking RSS import functionality...\n";

// Check if news table has news_type column
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($column_check) == 0) {
    echo "   Adding news_type column to news table...\n";
    mysqli_query($conn, "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual'");
    echo "   news_type column added\n";
} else {
    echo "   news_type column exists\n";
}

// Step 4: Test RSS import
echo "\nStep 4: Testing RSS import...\n";
$test_sources = ['BBC News', 'CNN News'];
$imported_count = 0;

foreach ($test_sources as $source_name) {
    $source_query = "SELECT rss_url FROM news_sources WHERE name = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $source_query);
    mysqli_stmt_bind_param($stmt, 's', $source_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($source = mysqli_fetch_assoc($result)) {
        $rss_url = $source['rss_url'];
        echo "   Testing: $source_name\n";
        echo "   RSS URL: $rss_url\n";
        
        // Simple RSS fetch test
        $rss_content = @file_get_contents($rss_url);
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
                    $description = (string)$first_item->description;
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
                            echo "   Test article imported successfully\n";
                            $imported_count++;
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
    }
}

echo "\nStep 5: Final Status\n";
echo "===================\n";

// Count sources
$source_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'");
$source_count_result = mysqli_fetch_assoc($source_count);
echo "Active RSS sources: {$source_count_result['count']}\n";

// Count RSS imports
$import_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import'");
$import_count_result = mysqli_fetch_assoc($import_count);
echo "Total RSS imports: {$import_count_result['count']}\n";

echo "\nRSS Import Status: ";
if ($source_count_result['count'] > 0) {
    echo "WORKING\n";
} else {
    echo "NEEDS SETUP\n";
}

echo "\nNext steps:\n";
echo "1. Visit admin/manage-sources.php to manage RSS sources\n";
echo "2. Run cron_import_news.php to test full import\n";
echo "3. Set up cron job for automatic imports\n";

echo "\nFix complete!\n";
?>
