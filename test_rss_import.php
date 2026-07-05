<?php
/**
 * Test RSS Import - Simple Web Interface
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>RSS Import Test - PK Live News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px; }
        .btn { padding: 10px 20px; background: #007cba; color: white; text-decoration: none; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>RSS Import Test</h1>
    
    <?php
    
    if (isset($_GET['action'])) {
        echo "<h2>Running RSS Import Test...</h2>\n";
        
        try {
            // Load required files
            require_once 'config/database.php';
            echo "<div class='success'>✓ Database connected</div>\n";
            
            require_once 'includes/enhanced_rss_parser.php';
            echo "<div class='success'>✓ RSS Parser loaded</div>\n";
            
            require_once 'includes/auto_news_importer.php';
            echo "<div class='success'>✓ Auto News Importer loaded</div>\n";
            
            // Check if RSS sources exist
            $sources_check = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'";
            $result = mysqli_query($conn, $sources_check);
            $row = mysqli_fetch_assoc($result);
            
            if ($row['count'] == 0) {
                echo "<div class='warning'>⚠ No active RSS sources found. Running setup...</div>\n";
                
                // Run setup
                include 'setup_rss_sources.php';
                echo "<div class='info'>ℹ Setup completed. Please refresh to test import.</div>\n";
            } else {
                echo "<div class='info'>ℹ Found {$row['count']} active RSS sources</div>\n";
                
                // Run import with limit
                $importer = new AutoNewsImporter($conn);
                $importer->setMaxArticlesPerFeed(2); // Limit for testing
                $importer->setDownloadImages(true);
                
                echo "<h3>Import Results:</h3>\n";
                $results = $importer->importFromAllSources();
                
                echo "<div class='info'>Total feeds: {$results['total_feeds']}</div>\n";
                echo "<div class='info'>Successful feeds: {$results['successful_feeds']}</div>\n";
                echo "<div class='info'>Failed feeds: {$results['error_feeds']}</div>\n";
                echo "<div class='success'>Articles imported: {$results['imported_articles']}</div>\n";
                echo "<div class='info'>Duplicates skipped: {$results['duplicate_articles']}</div>\n";
                
                if (!empty($results['details'])) {
                    echo "<h3>Feed Details:</h3>\n";
                    foreach ($results['details'] as $detail) {
                        if (isset($detail['error'])) {
                            echo "<div class='error'>✗ " . htmlspecialchars($detail['source_name']) . ": " . htmlspecialchars($detail['error']) . "</div>\n";
                        } else {
                            echo "<div class='success'>✓ " . htmlspecialchars($detail['source_name']) . ": {$detail['imported_articles']} imported, {$detail['duplicate_articles']} duplicates</div>\n";
                            
                            // Show sample articles if any
                            if (!empty($detail['articles'])) {
                                echo "<h4>Sample articles from {$detail['source_name']}:</h4>\n";
                                foreach (array_slice($detail['articles'], 0, 2) as $article) {
                                    if (isset($article['title'])) {
                                        echo "<div style='margin-left: 20px;'>";
                                        echo "<strong>" . htmlspecialchars($article['title']) . "</strong><br>\n";
                                        echo "Status: " . $article['status'] . "<br>\n";
                                        if (isset($article['image_downloaded'])) {
                                            echo "Image: " . ($article['image_downloaded'] ? '✓ Downloaded' : '✗ Not found/failed') . "<br>\n";
                                        }
                                        if (isset($article['sentiment'])) {
                                            echo "Sentiment: " . $article['sentiment'] . "<br>\n";
                                        }
                                        echo "</div>\n";
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Show recent imported articles
                echo "<h3>Recent Imported Articles:</h3>\n";
                $recent_query = "SELECT title, image, created_at, news_type FROM news WHERE news_type = 'rss_import' ORDER BY created_at DESC LIMIT 5";
                $recent_result = mysqli_query($conn, $recent_query);
                
                if (mysqli_num_rows($recent_result) > 0) {
                    while ($article = mysqli_fetch_assoc($recent_result)) {
                        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                        echo "<strong>" . htmlspecialchars($article['title']) . "</strong><br>\n";
                        echo "Created: " . $article['created_at'] . "<br>\n";
                        echo "Image: " . (!empty($article['image']) ? '<span class="success">✓ ' . htmlspecialchars($article['image']) . '</span>' : '<span class="warning">⚠ No image</span>') . "<br>\n";
                        echo "</div>\n";
                    }
                } else {
                    echo "<div class='warning'>No imported articles found</div>\n";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
        }
        
        echo "<p><a class='btn' href='test_rss_import.php'>← Back</a></p>";
        
    } else {
        ?>
        <p>This tool will test the RSS import system and help diagnose any issues.</p>
        
        <h2>Available Actions:</h2>
        
        <a class='btn' href='test_rss_import.php?action=setup'>Setup RSS Sources</a>
        <a class='btn' href='test_rss_import.php?action=test'>Test RSS Import</a>
        <a class='btn' href='cron_import_news.php?cron_key=pk_live_news_2024_cron'>Run Full Import</a>
        
        <h2>Quick Links:</h2>
        <a class='btn' href='admin/manage-sources.php'>Manage RSS Sources</a>
        <a class='btn' href='admin/'>Admin Dashboard</a>
        
        <h2>Common Issues & Solutions:</h2>
        <div style='background: #f9f9f9; padding: 15px; margin: 10px 0;'>
            <h3>1. RSS feeds not creating news:</h3>
            <ul>
                <li>Check if RSS sources are configured and active</li>
                <li>Verify RSS feed URLs are accessible</li>
                <li>Check database connection and tables</li>
                <li>Ensure uploads/news directory is writable</li>
            </ul>
            
            <h3>2. Images not showing:</h3>
            <ul>
                <li>Check if RSS feeds contain image URLs</li>
                <li>Verify image download is enabled</li>
                <li>Check uploads/news directory permissions</li>
                <li>Test with different RSS feeds</li>
            </ul>
            
            <h3>3. Duplicate articles:</h3>
            <ul>
                <li>System automatically detects duplicates by title and content</li>
                <li>Duplicates are skipped but counted in reports</li>
                <li>Check import logs for details</li>
            </ul>
        </div>
        
        <?php
    }
    ?>
    
</body>
</html>
