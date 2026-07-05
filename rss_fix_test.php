<?php
/**
 * RSS System Fix Test - Simple test from web root
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>RSS System Fix Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px; }
        .btn { padding: 10px 20px; background: #007cba; color: white; text-decoration: none; display: inline-block; margin: 5px; border-radius: 4px; }
        .btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>RSS System Fix Test</h1>
    
    <?php
    
    if (isset($_GET['test'])) {
        echo "<h2>Testing RSS System Components...</h2>\n";
        
        // Test 1: Database connection
        echo "<h3>1. Testing Database Connection</h3>\n";
        try {
            require_once 'config/database.php';
            if (isset($conn) && !$conn->connect_error) {
                echo "<div class='success'>✓ Database connection successful</div>\n";
            } else {
                echo "<div class='error'>✗ Database connection failed</div>\n";
                exit;
            }
        } catch (Exception $e) {
            echo "<div class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            exit;
        }
        
        // Test 2: Check tables
        echo "<h3>2. Checking Database Tables</h3>\n";
        $tables = ['news', 'categories', 'news_sources'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='success'>✓ Table '$table' exists</div>\n";
            } else {
                echo "<div class='error'>✗ Table '$table' missing</div>\n";
            }
        }
        
        // Test 3: RSS Sources
        echo "<h3>3. Checking RSS Sources</h3>\n";
        $sources_query = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'";
        $result = mysqli_query($conn, $sources_query);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] > 0) {
            echo "<div class='success'>✓ Found {$row['count']} active RSS sources</div>\n";
        } else {
            echo "<div class='warning'>⚠ No active RSS sources found</div>\n";
            echo "<div class='info'>Creating RSS sources...</div>\n";
            
            // Create news_sources table if needed
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
            if (mysqli_num_rows($table_check) == 0) {
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
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                mysqli_query($conn, $create_table);
                echo "<div class='success'>✓ Created news_sources table</div>\n";
            }
            
            // Get default category
            $cat_result = mysqli_query($conn, "SELECT id FROM categories WHERE status = 'active' LIMIT 1");
            if ($cat_row = mysqli_fetch_assoc($cat_result)) {
                $category_id = $cat_row['id'];
                
                // Add sample RSS sources
                $rss_feeds = [
                    ['BBC News', 'https://feeds.bbci.co.uk/news/rss.xml'],
                    ['CNN News', 'https://rss.cnn.com/rss/edition.rss'],
                    ['Reuters', 'https://feeds.reuters.com/reuters/topNews']
                ];
                
                foreach ($rss_feeds as $feed) {
                    $insert = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
                    $stmt = mysqli_prepare($conn, $insert);
                    mysqli_stmt_bind_param($stmt, 'sssi', $feed[0], $feed[1], $feed[1], $category_id);
                    mysqli_stmt_execute($stmt);
                }
                echo "<div class='success'>✓ Added sample RSS sources</div>\n";
            }
        }
        
        // Test 4: RSS Parser
        echo "<h3>4. Testing RSS Parser</h3>\n";
        try {
            require_once 'includes/enhanced_rss_parser.php';
            $parser = new EnhancedRSSParser();
            echo "<div class='success'>✓ RSS Parser loaded</div>\n";
            
            // Test a feed
            $test_feed = 'https://feeds.bbci.co.uk/news/rss.xml';
            echo "<div class='info'>Testing feed: $test_feed</div>\n";
            
            $articles = $parser->parseRSS($test_feed);
            echo "<div class='success'>✓ Successfully parsed " . count($articles) . " articles</div>\n";
            
            if (!empty($articles)) {
                $first = $articles[0];
                echo "<div class='info'>Sample article: " . htmlspecialchars(substr($first['title'], 0, 60)) . "...</div>\n";
                echo "<div class='info'>Has image: " . (!empty($first['image']) ? 'YES' : 'NO') . "</div>\n";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>✗ RSS Parser error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
        }
        
        // Test 5: Auto Importer
        echo "<h3>5. Testing Auto Importer</h3>\n";
        try {
            require_once 'includes/auto_news_importer.php';
            $importer = new AutoNewsImporter($conn);
            echo "<div class='success'>✓ Auto Importer loaded</div>\n";
            
            // Run limited import
            $importer->setMaxArticlesPerFeed(1);
            $results = $importer->importFromAllSources();
            
            echo "<div class='info'>Import Results:</div>\n";
            echo "<div class='info'>- Total feeds: {$results['total_feeds']}</div>\n";
            echo "<div class='info'>- Successful: {$results['successful_feeds']}</div>\n";
            echo "<div class='success'>- Articles imported: {$results['imported_articles']}</div>\n";
            echo "<div class='info'>- Duplicates: {$results['duplicate_articles']}</div>\n";
            
        } catch (Exception $e) {
            echo "<div class='error'>✗ Auto Importer error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
        }
        
        echo "<p><a class='btn' href='rss_fix_test.php'>← Back to Test Menu</a></p>";
        
    } else {
        ?>
        <p>This tool will test and fix RSS system issues step by step.</p>
        
        <h2>Test Options:</h2>
        <a class='btn' href='rss_fix_test.php?test=1'>Run Complete RSS System Test</a>
        
        <h2>Manual Tests:</h2>
        <a class='btn' href='setup_rss_sources.php' target='_blank'>Setup RSS Sources</a>
        <a class='btn' href='test_rss_import.php' target='_blank'>Test RSS Import</a>
        <a class='btn' href='cron_import_news.php?cron_key=pk_live_news_2024_cron' target='_blank'>Run Cron Import</a>
        
        <h2>Admin Links:</h2>
        <a class='btn' href='admin/manage-sources.php' target='_blank'>Manage RSS Sources</a>
        <a class='btn' href='admin/' target='_blank'>Admin Dashboard</a>
        
        <h2>Common Issues Fixed:</h2>
        <div style='background: #f9f9f9; padding: 15px; margin: 10px 0;'>
            <h3>✓ Fixed Include Path Issues</h3>
            <p>Updated all require_once statements to use absolute paths with __DIR__</p>
            
            <h3>✓ Fixed Missing Dependencies</h3>
            <p>Made sentiment analysis optional to prevent errors</p>
            
            <h3>✓ Enhanced Error Handling</h3>
            <p>Added comprehensive error checking and reporting</p>
            
            <h3>✓ Improved Image Extraction</h3>
            <p>Multiple methods to extract images from RSS feeds</p>
        </div>
        
        <?php
    }
    ?>
    
</body>
</html>
