<?php
/**
 * Quick Test for 39 Channels RSS Import
 * Tests the RSS import system with a small sample
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain');

echo "Quick RSS Import Test for 39 Channels\n";
echo "====================================\n\n";

// Check if setup has been run
$table_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss'");
$table_result = mysqli_fetch_assoc($table_check);
$rss_source_count = $table_result['count'];

echo "RSS sources in database: $rss_source_count\n\n";

if ($rss_source_count < 10) {
    echo "⚠️  WARNING: Less than 10 RSS sources found.\n";
    echo "Please run fix_rss_39_channels.php first to set up the RSS sources.\n\n";
}

// Test a few RSS feeds
echo "Testing RSS feeds...\n";
echo "===================\n\n";

require_once __DIR__ . '/includes/enhanced_rss_parser.php';
$parser = new EnhancedRSSParser();
$parser->setTimeout(5, 3); // Short timeout for quick test

// Get first 3 RSS sources for testing
$test_query = "SELECT name, rss_url FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 3";
$test_result = mysqli_query($conn, $test_query);

if (!$test_result || mysqli_num_rows($test_result) == 0) {
    echo "❌ No active RSS sources found for testing.\n";
    echo "Please run fix_rss_39_channels.php first.\n";
    exit(1);
}

$working_feeds = 0;
$total_items = 0;

while ($source = mysqli_fetch_assoc($test_result)) {
    echo "Testing: {$source['name']}\n";
    echo "URL: {$source['rss_url']}\n";
    
    try {
        $validation = $parser->validateFeed($source['rss_url']);
        
        if ($validation['valid']) {
            echo "✅ VALID - {$validation['items_count']} items\n";
            $working_feeds++;
            $total_items += $validation['items_count'];
        } else {
            echo "❌ INVALID - {$validation['error']}\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR - " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test Results:\n";
echo "=============\n";
echo "Working feeds: $working_feeds/3\n";
echo "Total items found: $total_items\n\n";

// Test actual import if feeds are working
if ($working_feeds > 0) {
    echo "Testing actual import (1 article per working feed)...\n";
    echo "================================================\n\n";
    
    require_once __DIR__ . '/includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(1);
    
    // Reset the test result to iterate again
    mysqli_data_seek($test_result, 0);
    $imported_count = 0;
    
    while ($source = mysqli_fetch_assoc($test_result)) {
        echo "Testing import from: {$source['name']}\n";
        
        try {
            $source_data = [
                'id' => null,
                'name' => $source['name'],
                'url' => $source['rss_url'],
                'category_id' => 1
            ];
            
            $result = $importer->importFromSource($source_data, 1);
            
            echo "  Articles imported: {$result['imported_articles']}\n";
            echo "  Duplicate articles: {$result['duplicate_articles']}\n";
            
            if ($result['imported_articles'] > 0) {
                echo "  ✅ Import successful!\n";
                $imported_count++;
            } else {
                echo "  ⚠️  No new articles (may be duplicates)\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ Import failed: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "Import Test Results:\n";
    echo "===================\n";
    echo "Sources tested: $working_feeds\n";
    echo "Successful imports: $imported_count\n\n";
    
    if ($imported_count > 0) {
        echo "🎉 RSS import system is working!\n\n";
        
        // Show recent imports
        echo "Recent RSS imports:\n";
        echo "==================\n";
        
        $recent_query = "SELECT title, status, created_at FROM news 
                       WHERE news_type = 'rss_import' 
                       ORDER BY created_at DESC 
                       LIMIT 5";
        $recent_result = mysqli_query($conn, $recent_query);
        
        if ($recent_result && mysqli_num_rows($recent_result) > 0) {
            while ($row = mysqli_fetch_assoc($recent_result)) {
                echo "📰 {$row['title']} ({$row['status']})\n";
                echo "   Created: {$row['created_at']}\n\n";
            }
        }
    } else {
        echo "⚠️  No articles imported. Check:\n";
        echo "   - If articles already exist (duplicates)\n";
        echo "   - If RSS feeds have valid content\n";
        echo "   - Database permissions and structure\n\n";
    }
}

// Overall system status
echo "SYSTEM STATUS\n";
echo "=============\n";

$stats_query = "SELECT 
    COUNT(*) as total_rss,
    COUNT(CASE WHEN status = 'published' THEN 1 END) as published,
    COUNT(CASE WHEN status = 'draft' THEN 1 END) as drafts,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today
    FROM news WHERE news_type = 'rss_import'";
$stats_result = mysqli_query($conn, $stats_query);

if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
    echo "Total RSS imports: {$stats['total_rss']}\n";
    echo "Published: {$stats['published']}\n";
    echo "Drafts: {$stats['drafts']}\n";
    echo "Imported today: {$stats['today']}\n\n";
}

echo "RECOMMENDATIONS\n";
echo "==============\n";

if ($working_feeds == 3) {
    echo "✅ RSS feeds are working correctly\n";
    echo "✅ Import system is functional\n";
    echo "✅ Ready for full import\n\n";
    
    echo "Next steps:\n";
    echo "1. Run full import: cron_import_news.php?cron_key=pk_live_news_2024_cron\n";
    echo "2. Set up cron job for automatic imports\n";
    echo "3. Monitor and publish draft articles\n";
} else {
    echo "⚠️  Some RSS feeds are not working\n";
    echo "Recommendations:\n";
    echo "1. Run diagnostic: rss_import_diagnostic.php\n";
    echo "2. Check feed URLs and update if needed\n";
    echo "3. Test individual feeds\n";
    echo "4. Consider replacing problematic feeds\n";
}

echo "\nTest complete!\n";
?>
