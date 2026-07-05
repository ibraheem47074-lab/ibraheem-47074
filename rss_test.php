<?php
/**
 * RSS System Diagnostic Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>RSS System Diagnostic Test</h1>\n";

// Test 1: Check required files
echo "<h2>1. Checking Required Files</h2>\n";
$requiredFiles = [
    'config/database.php',
    'includes/enhanced_rss_parser.php',
    'includes/auto_news_importer.php',
    'includes/web_scraper.php',
    'includes/sentiment_analysis.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>✓ $file exists</span><br>\n";
    } else {
        echo "<span style='color: red;'>✗ $file missing</span><br>\n";
    }
}

// Test 2: Check database connection
echo "<h2>2. Testing Database Connection</h2>\n";
try {
    require_once 'config/database.php';
    if (isset($conn) && $conn->connect_error) {
        echo "<span style='color: red;'>✗ Database connection failed: " . $conn->connect_error . "</span><br>\n";
    } else {
        echo "<span style='color: green;'>✓ Database connection successful</span><br>\n";
        
        // Check news_sources table
        $result = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
        if (mysqli_num_rows($result) > 0) {
            echo "<span style='color: green;'>✓ news_sources table exists</span><br>\n";
            
            // Count RSS sources
            $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'");
            $row = mysqli_fetch_assoc($count);
            echo "<span style='color: blue;'>ℹ Active RSS sources: " . $row['count'] . "</span><br>\n";
        } else {
            echo "<span style='color: red;'>✗ news_sources table missing</span><br>\n";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database error: " . $e->getMessage() . "</span><br>\n";
}

// Test 3: Test RSS Parser
echo "<h2>3. Testing RSS Parser</h2>\n";
try {
    require_once 'includes/enhanced_rss_parser.php';
    echo "<span style='color: green;'>✓ RSS Parser loaded successfully</span><br>\n";
    
    // Test with a sample RSS feed
    $parser = new EnhancedRSSParser();
    echo "<span style='color: green;'>✓ RSS Parser instantiated</span><br>\n";
    
    // Test BBC RSS feed
    echo "<h3>Testing BBC News RSS Feed</h3>\n";
    $testFeed = 'http://feeds.bbci.co.uk/news/rss.xml';
    try {
        $validation = $parser->validateFeed($testFeed);
        if ($validation['valid']) {
            echo "<span style='color: green;'>✓ BBC RSS feed is valid</span><br>\n";
            echo "<span style='color: blue;'>ℹ Feed title: " . $validation['title'] . "</span><br>\n";
            echo "<span style='color: blue;'>ℹ Items count: " . $validation['items_count'] . "</span><br>\n";
            
            // Try to parse articles
            $articles = $parser->parseRSS($testFeed);
            echo "<span style='color: green;'>✓ Successfully parsed " . count($articles) . " articles</span><br>\n";
            
            // Show first article details
            if (!empty($articles)) {
                $firstArticle = $articles[0];
                echo "<h4>First Article Sample:</h4>\n";
                echo "<strong>Title:</strong> " . htmlspecialchars($firstArticle['title']) . "<br>\n";
                echo "<strong>Image:</strong> " . (!empty($firstArticle['image']) ? '<span style="color: green;">✓ Found</span> - ' . htmlspecialchars($firstArticle['image']) : '<span style="color: orange;">⚠ Not found</span>') . "<br>\n";
                echo "<strong>Content Length:</strong> " . strlen($firstArticle['content']) . " characters<br>\n";
            }
        } else {
            echo "<span style='color: red;'>✗ BBC RSS feed validation failed: " . $validation['error'] . "</span><br>\n";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ BBC RSS feed test failed: " . $e->getMessage() . "</span><br>\n";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ RSS Parser error: " . $e->getMessage() . "</span><br>\n";
}

// Test 4: Test Auto News Importer
echo "<h2>4. Testing Auto News Importer</h2>\n";
try {
    require_once 'includes/auto_news_importer.php';
    echo "<span style='color: green;'>✓ Auto News Importer loaded successfully</span><br>\n";
    
    if (isset($conn)) {
        $importer = new AutoNewsImporter($conn);
        echo "<span style='color: green;'>✓ Auto News Importer instantiated</span><br>\n";
        
        // Test with a limited import
        echo "<h3>Testing Limited Import (1 article)</h3>\n";
        $results = $importer->importFromAllSources(1);
        
        echo "<span style='color: blue;'>ℹ Total feeds processed: " . $results['total_feeds'] . "</span><br>\n";
        echo "<span style='color: blue;'>ℹ Successful feeds: " . $results['successful_feeds'] . "</span><br>\n";
        echo "<span style='color: blue;'>ℹ Articles imported: " . $results['imported_articles'] . "</span><br>\n";
        echo "<span style='color: blue;'>ℹ Duplicate articles: " . $results['duplicate_articles'] . "</span><br>\n";
        
        if (!empty($results['details'])) {
            echo "<h4>Feed Details:</h4>\n";
            foreach ($results['details'] as $detail) {
                if (isset($detail['error'])) {
                    echo "<span style='color: red;'>✗ " . htmlspecialchars($detail['source_name']) . ": " . htmlspecialchars($detail['error']) . "</span><br>\n";
                } else {
                    echo "<span style='color: green;'>✓ " . htmlspecialchars($detail['source_name']) . ": " . $detail['imported_articles'] . " imported</span><br>\n";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Auto News Importer error: " . $e->getMessage() . "</span><br>\n";
}

// Test 5: Check uploads directory
echo "<h2>5. Checking Uploads Directory</h2>\n";
$uploadsDir = 'uploads/news';
if (is_dir($uploadsDir)) {
    echo "<span style='color: green;'>✓ Uploads directory exists</span><br>\n";
    if (is_writable($uploadsDir)) {
        echo "<span style='color: green;'>✓ Uploads directory is writable</span><br>\n";
    } else {
        echo "<span style='color: red;'>✗ Uploads directory is not writable</span><br>\n";
    }
} else {
    echo "<span style='color: red;'>✗ Uploads directory missing</span><br>\n";
}

// Test 6: Check helper functions
echo "<h2>6. Testing Helper Functions</h2>\n";
try {
    require_once 'config/helpers.php';
    echo "<span style='color: green;'>✓ Helper functions loaded</span><br>\n";
    
    // Test slugify function
    if (function_exists('slugify')) {
        $testSlug = slugify('Test RSS Article Title');
        echo "<span style='color: green;'>✓ slugify() works: " . $testSlug . "</span><br>\n";
    } else {
        echo "<span style='color: red;'>✗ slugify() function missing</span><br>\n";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Helper functions error: " . $e->getMessage() . "</span><br>\n";
}

echo "<h2>Diagnostic Complete</h2>\n";
echo "<p><a href='admin/manage-sources.php'>Manage RSS Sources</a> | <a href='cron_import_news.php?cron_key=pk_live_news_2024_cron'>Run Manual Import</a></p>\n";
?>
