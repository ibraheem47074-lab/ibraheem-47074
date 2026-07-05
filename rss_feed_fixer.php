<?php
/**
 * RSS Feed Fixer - Web Interface
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>RSS Feed Fixer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px; }
        .btn { padding: 10px 20px; background: #007cba; color: white; text-decoration: none; display: inline-block; margin: 5px; border-radius: 4px; }
        .btn:hover { background: #005a87; }
        .feed-item { background: #f9f9f9; padding: 10px; margin: 5px 0; border-left: 4px solid #007cba; }
        .feed-invalid { border-left-color: #dc3545; }
        .feed-valid { border-left-color: #28a745; }
    </style>
</head>
<body>
    <h1>RSS Feed Fixer</h1>
    
    <?php
    
    if (isset($_GET['action']) && $_GET['action'] == 'fix') {
        echo "<h2>Fixing RSS Feed URLs...</h2>\n";
        
        require_once __DIR__ . '/config/database.php';
        
        // Working RSS feeds
        $working_feeds = [
            'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
            'CNN' => 'https://rss.cnn.com/rss/edition.rss',
            'Reuters' => 'https://feeds.reuters.com/reuters/topNews',
            'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
            'The Guardian' => 'https://www.theguardian.com/world/rss',
            'Fox News' => 'https://feeds.foxnews.com/foxnews/latest',
            'Associated Press' => 'https://feeds.apnews.com/rss/apf-topnews',
            'NPR News' => 'https://feeds.npr.org/1001/rss.xml',
            'CBS News' => 'https://feeds.cbsnews.com/CBSNewsMain',
            'ABC News' => 'https://feeds.abcnews.com/abcnews/topstories'
        ];
        
        require_once __DIR__ . '/includes/enhanced_rss_parser.php';
        $parser = new EnhancedRSSParser();
        
        $fixed_count = 0;
        $failed_count = 0;
        
        foreach ($working_feeds as $name => $rss_url) {
            echo "<div class='feed-item'>";
            echo "<h3>$name</h3>";
            echo "<p><strong>URL:</strong> $rss_url</p>";
            
            try {
                // Test the feed
                $validation = $parser->validateFeed($rss_url);
                
                if ($validation['valid']) {
                    echo "<p class='success'>✓ Valid RSS feed - {$validation['items_count']} items</p>";
                    
                    // Update or insert in database
                    $check_query = "SELECT id FROM news_sources WHERE name = ?";
                    $stmt = mysqli_prepare($conn, $check_query);
                    mysqli_stmt_bind_param($stmt, 's', $name);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($result) > 0) {
                        // Update existing
                        $update_query = "UPDATE news_sources SET rss_url = ?, status = 'active' WHERE name = ?";
                        $stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($stmt, 'ss', $rss_url, $name);
                        mysqli_stmt_execute($stmt);
                        echo "<p class='info'>ℹ Updated existing source</p>";
                    } else {
                        // Insert new
                        $cat_result = mysqli_query($conn, "SELECT id FROM categories WHERE status = 'active' LIMIT 1");
                        $category_id = 1;
                        if ($cat_row = mysqli_fetch_assoc($cat_result)) {
                            $category_id = $cat_row['id'];
                        }
                        
                        $insert_query = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
                        $stmt = mysqli_prepare($conn, $insert_query);
                        mysqli_stmt_bind_param($stmt, 'sssi', $name, $rss_url, $rss_url, $category_id);
                        mysqli_stmt_execute($stmt);
                        echo "<p class='info'>ℹ Added new source</p>";
                    }
                    
                    $fixed_count++;
                    
                } else {
                    echo "<p class='error'>✗ Invalid RSS feed: " . htmlspecialchars($validation['error']) . "</p>";
                    $failed_count++;
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                $failed_count++;
            }
            
            echo "</div>";
        }
        
        echo "<h2>Summary</h2>";
        echo "<p class='success'>Fixed: $fixed_count feeds</p>";
        echo "<p class='error'>Failed: $failed_count feeds</p>";
        
        // Test import
        echo "<h3>Testing Import</h3>";
        try {
            require_once __DIR__ . '/includes/auto_news_importer.php';
            $importer = new AutoNewsImporter($conn);
            $importer->setMaxArticlesPerFeed(1);
            
            $results = $importer->importFromAllSources();
            echo "<div class='feed-item feed-valid'>";
            echo "<p class='success'>✓ Import test completed</p>";
            echo "<p>Feeds processed: {$results['total_feeds']}</p>";
            echo "<p>Successful: {$results['successful_feeds']}</p>";
            echo "<p>Articles imported: {$results['imported_articles']}</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='feed-item feed-invalid'>";
            echo "<p class='error'>✗ Import test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        
        echo "<p><a class='btn' href='rss_feed_fixer.php'>← Back</a></p>";
        
    } else {
        ?>
        <p>This tool will fix your RSS feed URLs with working RSS feed endpoints.</p>
        
        <h2>Problem</h2>
        <div class='feed-item feed-invalid'>
            <p>Your current RSS sources are pointing to website URLs instead of RSS feed URLs.</p>
            <p>For example: <code>https://www.reuters.com</code> should be <code>https://feeds.reuters.com/reuters/topNews</code></p>
        </div>
        
        <h2>Solution</h2>
        <p>This tool will:</p>
        <ul>
            <li>Update RSS sources with correct RSS feed URLs</li>
            <li>Test each feed to ensure it works</li>
            <li>Remove invalid feeds and add working alternatives</li>
            <li>Run a quick import test</li>
        </ul>
        
        <h2>Working RSS Feeds to be Added:</h2>
        <div class='feed-item feed-valid'>
            <h4>BBC News</h4>
            <p><code>https://feeds.bbci.co.uk/news/rss.xml</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>CNN</h4>
            <p><code>https://rss.cnn.com/rss/edition.rss</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>Reuters</h4>
            <p><code>https://feeds.reuters.com/reuters/topNews</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>Al Jazeera</h4>
            <p><code>https://www.aljazeera.com/xml/rss/all.xml</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>The Guardian</h4>
            <p><code>https://www.theguardian.com/world/rss</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>Fox News</h4>
            <p><code>https://feeds.foxnews.com/foxnews/latest</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>Associated Press</h4>
            <p><code>https://feeds.apnews.com/rss/apf-topnews</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>NPR News</h4>
            <p><code>https://feeds.npr.org/1001/rss.xml</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>CBS News</h4>
            <p><code>https://feeds.cbsnews.com/CBSNewsMain</code></p>
        </div>
        <div class='feed-item feed-valid'>
            <h4>ABC News</h4>
            <p><code>https://feeds.abcnews.com/abcnews/topstories</code></p>
        </div>
        
        <h2>Action</h2>
        <a class='btn' href='rss_feed_fixer.php?action=fix'>Fix RSS Feeds Now</a>
        
        <h2>Other Tools</h2>
        <a class='btn' href='admin/manage-sources.php' target='_blank'>Manage RSS Sources</a>
        <a class='btn' href='test_rss_import.php' target='_blank'>Test RSS Import</a>
        <a class='btn' href='rss_fix_test.php' target='_blank'>RSS System Test</a>
        
        <?php
    }
    ?>
    
</body>
</html>
