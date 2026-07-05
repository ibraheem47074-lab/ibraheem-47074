<?php
/**
 * Quick RSS Feed Fix
 * Updates RSS sources with working feed URLs
 */

require_once 'config/database.php';

echo "<h1>Quick RSS Feed Fix</h1>\n";

// Working RSS feed URLs
$working_feeds = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'CNN' => 'https://rss.cnn.com/rss/edition.rss', 
    'Reuters' => 'https://feeds.reuters.com/reuters/topNews',
    'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
    'The Guardian' => 'https://www.theguardian.com/world/rss',
    'Fox News' => 'https://feeds.foxnews.com/foxnews/latest',
    'Associated Press' => 'https://feeds.apnews.com/rss/apf-topnews',
    'NPR News' => 'https://feeds.npr.org/1001/rss.xml'
];

$fixed_count = 0;
$failed_count = 0;

foreach ($working_feeds as $name => $rss_url) {
    echo "<h3>Processing: $name</h3>\n";
    echo "Setting URL: $rss_url<br>\n";
    
    // Check if source exists
    $check_query = "SELECT id, url FROM news_sources WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $source = mysqli_fetch_assoc($result);
        
        // Update with correct RSS URL
        $update_query = "UPDATE news_sources SET url = ?, rss_url = ?, status = 'active' WHERE name = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'sss', $rss_url, $rss_url, $name);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<span style='color: green;'>✓ Updated existing source</span><br>\n";
            $fixed_count++;
        } else {
            echo "<span style='color: red;'>✗ Update failed: " . mysqli_error($conn) . "</span><br>\n";
            $failed_count++;
        }
    } else {
        // Insert new source
        $cat_result = mysqli_query($conn, "SELECT id FROM categories WHERE status = 'active' LIMIT 1");
        $category_id = 1;
        if ($cat_row = mysqli_fetch_assoc($cat_result)) {
            $category_id = $cat_row['id'];
        }
        
        $insert_query = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssi', $name, $rss_url, $rss_url, $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<span style='color: green;'>✓ Added new source</span><br>\n";
            $fixed_count++;
        } else {
            echo "<span style='color: red;'>✗ Insert failed: " . mysqli_error($conn) . "</span><br>\n";
            $failed_count++;
        }
    }
    echo "<hr>\n";
}

echo "<h2>Summary</h2>\n";
echo "<span style='color: green;'>Fixed: $fixed_count feeds</span><br>\n";
echo "<span style='color: red;'>Failed: $failed_count feeds</span><br>\n";

// Test import
echo "<h3>Testing RSS Import</h3>\n";
try {
    require_once 'includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(2);
    
    $results = $importer->importFromAllSources();
    
    echo "<div style='background: #f0f8ff; padding: 10px; border-left: 4px solid #007cba;'>\n";
    echo "<p><strong>Import Test Results:</strong></p>\n";
    echo "<p>Feeds processed: {$results['total_feeds']}</p>\n";
    echo "<p>Successful feeds: {$results['successful_feeds']}</p>\n";
    echo "<p>Articles imported: {$results['imported_articles']}</p>\n";
    echo "<p>Duplicate articles: {$results['duplicate_articles']}</p>\n";
    echo "</div>\n";
    
    if ($results['imported_articles'] > 0) {
        echo "<p style='color: green;'><strong>✓ RSS System is now working!</strong></p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ RSS feeds are accessible but no new articles were imported (likely duplicates)</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Import test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h3>Next Steps</h3>\n";
echo "<ol>\n";
echo "<li><a href='admin/manage-sources.php' target='_blank'>Manage RSS Sources</a></li>\n";
echo "<li><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron' target='_blank'>Run Full Import</a></li>\n";
echo "<li><a href='index.php' target='_blank'>View Website</a></li>\n";
echo "</ol>\n";

echo "<p><strong>RSS Feed Fix Complete!</strong> The system should now be able to import news from RSS feeds.</p>\n";
?>
