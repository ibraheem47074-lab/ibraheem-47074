<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "RSS Import Status Check\n";
echo "======================\n\n";

// Check if news_sources table exists
echo "1. Checking news_sources table...\n";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");

if ($table_check && mysqli_num_rows($table_check) > 0) {
    echo "   news_sources table exists\n";
    
    // Check table structure
    echo "\n2. news_sources table structure:\n";
    $columns_query = "SHOW COLUMNS FROM news_sources";
    $columns_result = mysqli_query($conn, $columns_query);
    
    if ($columns_result) {
        while ($column = mysqli_fetch_assoc($columns_result)) {
            echo "   - {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    // Check if there are any sources
    echo "\n3. RSS sources count:\n";
    $count_query = "SELECT COUNT(*) as total FROM news_sources";
    $count_result = mysqli_query($conn, $count_query);
    
    if ($count_result) {
        $count = mysqli_fetch_assoc($count_result)['total'];
        echo "   Total sources: $count\n";
        
        if ($count > 0) {
            echo "\n4. Active RSS sources:\n";
            $sources_query = "SELECT name, url, rss_url, type, status FROM news_sources WHERE status = 'active' ORDER BY name";
            $sources_result = mysqli_query($conn, $sources_query);
            
            if ($sources_result) {
                while ($source = mysqli_fetch_assoc($sources_result)) {
                    echo "   - {$source['name']} ({$source['type']})\n";
                    echo "     RSS: {$source['rss_url']}\n";
                    echo "     Status: {$source['status']}\n\n";
                }
            }
        } else {
            echo "   No RSS sources found in table\n";
        }
    }
    
} else {
    echo "   news_sources table does NOT exist\n";
    echo "\n4. Creating news_sources table...\n";
    
    $create_table = "CREATE TABLE news_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL COMMENT 'Name of news source',
        url VARCHAR(500) NOT NULL COMMENT 'Main URL of news source',
        rss_url VARCHAR(500) COMMENT 'RSS feed URL',
        type ENUM('rss', 'scrape') NOT NULL DEFAULT 'rss' COMMENT 'Import type',
        category_id INT COMMENT 'Default category ID',
        scrape_frequency INT DEFAULT 60 COMMENT 'Scraping frequency in minutes',
        status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Source status',
        last_scraped TIMESTAMP NULL COMMENT 'Last successful scrape',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_type (type),
        INDEX idx_last_scraped (last_scraped)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "   news_sources table created successfully\n";
        
        // Add some default RSS sources
        echo "\n5. Adding default RSS sources...\n";
        
        // Get default category
        $category_query = "SELECT id FROM categories WHERE status = 'active' LIMIT 1";
        $category_result = mysqli_query($conn, $category_query);
        $category_id = 1; // Default fallback
        
        if ($category_result && mysqli_num_rows($category_result) > 0) {
            $category_id = mysqli_fetch_assoc($category_result)['id'];
        }
        
        $default_sources = [
            [
                'name' => 'BBC News',
                'url' => 'https://www.bbc.com/news',
                'rss_url' => 'https://feeds.bbci.co.uk/news/rss.xml',
                'type' => 'rss',
                'category_id' => $category_id
            ],
            [
                'name' => 'CNN News',
                'url' => 'https://www.cnn.com',
                'rss_url' => 'https://rss.cnn.com/rss/edition.rss',
                'type' => 'rss',
                'category_id' => $category_id
            ],
            [
                'name' => 'Reuters News',
                'url' => 'https://www.reuters.com',
                'rss_url' => 'https://feeds.reuters.com/reuters/topNews',
                'type' => 'rss',
                'category_id' => $category_id
            ],
            [
                'name' => 'Al Jazeera',
                'url' => 'https://www.aljazeera.com',
                'rss_url' => 'https://www.aljazeera.com/xml/rss/all.xml',
                'type' => 'rss',
                'category_id' => $category_id
            ],
            [
                'name' => 'Associated Press',
                'url' => 'https://apnews.com',
                'rss_url' => 'https://feeds.apnews.com/rss/apf-topnews',
                'type' => 'rss',
                'category_id' => $category_id
            ]
        ];
        
        $added_count = 0;
        foreach ($default_sources as $source) {
            $insert_query = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) 
                           VALUES (?, ?, ?, ?, ?, 'active')";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssssi', 
                $source['name'], 
                $source['url'], 
                $source['rss_url'], 
                $source['type'], 
                $source['category_id']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                echo "   - Added: {$source['name']}\n";
                $added_count++;
            }
        }
        
        echo "\n   Added $added_count default RSS sources\n";
        
    } else {
        echo "   Error creating news_sources table: " . mysqli_error($conn) . "\n";
    }
}

// Check RSS import status
echo "\n6. RSS import status:\n";
$import_query = "SELECT COUNT(*) as total, 
                       COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today,
                       COUNT(CASE WHEN status = 'published' THEN 1 END) as published
                FROM news 
                WHERE news_type = 'rss_import'";
$import_result = mysqli_query($conn, $import_query);

if ($import_result) {
    $stats = mysqli_fetch_assoc($import_result);
    echo "   Total RSS imports: {$stats['total']}\n";
    echo "   Imported today: {$stats['today']}\n";
    echo "   Published: {$stats['published']}\n";
    
    if ($stats['total'] > 0) {
        echo "\n7. Recent RSS imports:\n";
        $recent_query = "SELECT title, source_url, status, created_at 
                        FROM news 
                        WHERE news_type = 'rss_import' 
                        ORDER BY created_at DESC 
                        LIMIT 5";
        $recent_result = mysqli_query($conn, $recent_query);
        
        if ($recent_result) {
            while ($row = mysqli_fetch_assoc($recent_result)) {
                echo "   - " . substr($row['title'], 0, 50) . "... ({$row['status']})\n";
                echo "     {$row['created_at']}\n";
            }
        }
    }
} else {
    echo "   Could not check RSS import status\n";
}

echo "\nRSS Import Status: ";
if (mysqli_num_rows($table_check) > 0) {
    echo "WORKING\n";
} else {
    echo "NEEDS SETUP\n";
}

echo "\nDone!\n";
?>
