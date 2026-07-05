<?php
/**
 * Test RSS Import from Different Channels
 * Simple test to verify RSS import is working
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>RSS Channel Import Test</h1>\n";

// Test channels
$test_channels = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'CNN News' => 'https://rss.cnn.com/rss/edition.rss',
    'Reuters World' => 'https://feeds.reuters.com/reuters/worldNews',
    'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
    'Geo News' => 'https://www.geo.tv/rss/1.xml',
    'ARY News' => 'https://arynews.tv/feed/',
    'Dawn News' => 'https://www.dawn.com/rss'
];

echo "<h2>Testing RSS Feeds</h2>\n";

foreach ($test_channels as $name => $url) {
    echo "<h3>$name</h3>\n";
    echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>\n";
    
    // Test connectivity
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'PK-LIVE-NEWS-RSS-Reader/2.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>✗ Connection Error: $error</p>\n";
        continue;
    }
    
    if ($http_code != 200) {
        echo "<p style='color: red;'>✗ HTTP Error: $http_code</p>\n";
        continue;
    }
    
    // Parse XML
    $xml = @simplexml_load_string($content);
    if ($xml === false) {
        echo "<p style='color: red;'>✗ Invalid XML/RSS format</p>\n";
        continue;
    }
    
    // Count items
    $items = isset($xml->channel->item) ? $xml->channel->item : 
              (isset($xml->entry) ? $xml->entry : []);
    
    $item_count = count($items);
    echo "<p style='color: green;'>✓ Feed is valid - $item_count items found</p>\n";
    
    // Show sample items
    if ($item_count > 0) {
        echo "<h4>Sample Articles:</h4>\n";
        echo "<ul>\n";
        
        $count = 0;
        foreach ($items as $item) {
            if ($count >= 3) break;
            
            $title = (string) ($item->title ?? 'No title');
            $link = (string) ($item->link ?? '#');
            $pub_date = (string) ($item->pubDate ?? 'No date');
            
            echo "<li><strong>" . htmlspecialchars($title) . "</strong><br>\n";
            echo "<small>Date: $pub_date</small><br>\n";
            echo "<a href='$link' target='_blank'>Read more</a></li>\n";
            
            $count++;
        }
        echo "</ul>\n";
    }
    
    echo "<hr>\n";
}

echo "<h2>Database Import Test</h2>\n";

// Test actual import
try {
    require_once 'config/database.php';
    require_once 'includes/auto_news_importer_fixed.php';
    
    echo "<p>Testing database connection...</p>\n";
    
    $importer = new FixedAutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(2); // Limit for testing
    
    echo "<p>Running import test...</p>\n";
    $results = $importer->importFromAllSources();
    
    echo "<h3>Import Results:</h3>\n";
    echo "<ul>\n";
    echo "<li>Total feeds: {$results['total_feeds']}</li>\n";
    echo "<li>Successful feeds: {$results['successful_feeds']}</li>\n";
    echo "<li>Failed feeds: {$results['error_feeds']}</li>\n";
    echo "<li>Articles imported: <strong>{$results['imported_articles']}</strong></li>\n";
    echo "<li>Duplicates skipped: {$results['duplicate_articles']}</li>\n";
    echo "</ul>\n";
    
    if ($results['imported_articles'] > 0) {
        echo "<p style='color: green; font-size: larger;'><strong>✓ SUCCESS!</strong> RSS import is working!</p>\n";
        echo "<p>Check your admin panel for draft articles.</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No articles were imported. This could be due to:</p>\n";
        echo "<ul>\n";
        echo "<li>All articles are duplicates (imported within last 24 hours)</li>\n";
        echo "<li>Feed connectivity issues</li>\n";
        echo "<li>Database permission issues</li>\n";
        echo "</ul>\n";
    }
    
    // Show detailed results
    if (!empty($results['details'])) {
        echo "<h3>Feed Details:</h3>\n";
        foreach ($results['details'] as $detail) {
            if (isset($detail['error'])) {
                echo "<p style='color: red;'>✗ " . htmlspecialchars($detail['source_name']) . ": " . htmlspecialchars($detail['error']) . "</p>\n";
            } else {
                echo "<p style='color: green;'>✓ " . htmlspecialchars($detail['source_name']) . ": {$detail['imported_articles']} imported, {$detail['duplicate_articles']} duplicates</p>\n";
            }
        }
    }
    
    // Check recent imports
    echo "<h3>Recent Imported Articles:</h3>\n";
    $recent_query = "SELECT title, created_at, source_url FROM news WHERE news_type = 'rss_import' ORDER BY created_at DESC LIMIT 5";
    $recent_result = mysqli_query($conn, $recent_query);
    
    if (mysqli_num_rows($recent_result) > 0) {
        echo "<ul>\n";
        while ($article = mysqli_fetch_assoc($recent_result)) {
            echo "<li><strong>" . htmlspecialchars($article['title']) . "</strong><br>\n";
            echo "<small>Imported: {$article['created_at']}</small><br>\n";
            echo "<small>Source: " . htmlspecialchars($article['source_url']) . "</small></li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p>No imported articles found in database.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Import test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>Next Steps</h2>\n";
echo "<ol>\n";
echo "<li>If import is successful, set up a cron job to run automatically</li>\n";
echo "<li>Visit admin panel to review and publish imported articles</li>\n";
echo "<li>Configure RSS sources in admin panel if needed</li>\n";
echo "</ol>\n";

echo "<p><a href='admin/'>Go to Admin Panel</a> | <a href='test_rss_import_fixed.php'>Run Test Again</a></p>\n";
?>
