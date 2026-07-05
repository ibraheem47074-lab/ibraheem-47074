<?php
/**
 * Create news_sources table - Run this first
 */

require_once __DIR__ . '/config/database.php';

echo "Creating news_sources table...\n";

// Create the table
$create_sql = "CREATE TABLE IF NOT EXISTS `news_sources` (
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

if (mysqli_query($conn, $create_sql)) {
    echo "SUCCESS: news_sources table created\n";
    
    // Add basic RSS sources
    echo "Adding RSS sources...\n";
    
    // Get default category
    $cat_query = "SELECT id FROM categories WHERE status = 'active' LIMIT 1";
    $cat_result = mysqli_query($conn, $cat_query);
    $category_id = 1;
    
    if ($cat_result && mysqli_num_rows($cat_result) > 0) {
        $category_id = mysqli_fetch_assoc($cat_result)['id'];
    }
    
    $sources = [
        ['BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml'],
        ['CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss'],
        ['Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews'],
        ['Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml'],
        ['Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews']
    ];
    
    foreach ($sources as $source) {
        $insert_sql = "INSERT IGNORE INTO news_sources (name, url, rss_url, type, category_id, status) 
                       VALUES (?, ?, ?, 'rss', ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $source[0], $source[1], $source[2], $category_id);
        mysqli_stmt_execute($stmt);
        echo "Added: {$source[0]}\n";
    }
    
    echo "\nTable setup complete!\n";
    echo "Now you can run rss_fix_test.php\n";
    
} else {
    echo "ERROR: " . mysqli_error($conn) . "\n";
}
?>
