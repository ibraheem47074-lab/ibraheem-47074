<?php
/**
 * Quick Fix - Create news_sources table immediately
 */

require_once __DIR__ . '/config/database.php';

echo "Creating news_sources table...\n";

// Simple table creation
$sql = "CREATE TABLE IF NOT EXISTS news_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    rss_url VARCHAR(500) DEFAULT NULL,
    type ENUM('rss', 'scrape') DEFAULT 'rss',
    category_id INT DEFAULT NULL,
    scrape_frequency INT DEFAULT 60,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_scraped TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "SUCCESS: Table created\n";
    
    // Add one test source
    $insert_sql = "INSERT IGNORE INTO news_sources (name, url, rss_url, type, category_id, status) 
                   VALUES ('BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml', 'rss', 1, 'active')";
    
    if (mysqli_query($conn, $insert_sql)) {
        echo "SUCCESS: Test source added\n";
    }
    
    echo "Fix complete! Now you can access admin pages.\n";
    
} else {
    echo "ERROR: " . mysqli_error($conn) . "\n";
}
?>
