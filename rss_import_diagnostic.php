<?php
/**
 * RSS Import Diagnostic Tool for 39 Channels
 * Diagnoses and fixes RSS feed import issues
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

header('Content-Type: text/plain');

echo "RSS Import Diagnostic for 39 Channels\n";
echo "=====================================\n\n";

// Initialize parser
$parser = new EnhancedRSSParser();
$parser->setTimeout(10, 5); // 10 seconds timeout, 5 seconds connection

// Get all RSS sources from database
$sources_query = "SELECT * FROM news_sources WHERE type = 'rss' ORDER BY name ASC";
$sources_result = mysqli_query($conn, $sources_query);

if (!$sources_result) {
    echo "ERROR: Failed to fetch RSS sources: " . mysqli_error($conn) . "\n";
    exit(1);
}

$total_sources = mysqli_num_rows($sources_result);
echo "Found $total_sources RSS sources in database\n\n";

$working_sources = 0;
$failed_sources = 0;
$working_feeds = [];
$failed_feeds = [];

echo "Testing RSS feeds...\n";
echo "==================\n\n";

while ($source = mysqli_fetch_assoc($sources_result)) {
    $source_name = $source['name'];
    $rss_url = $source['rss_url'];
    
    echo "Testing: $source_name\n";
    echo "URL: $rss_url\n";
    
    try {
        $validation = $parser->validateFeed($rss_url);
        
        if ($validation['valid']) {
            echo "✓ VALID - {$validation['items_count']} items found\n";
            echo "  Format: {$validation['format']}\n";
            echo "  Title: {$validation['title']}\n";
            $working_sources++;
            $working_feeds[] = [
                'name' => $source_name,
                'url' => $rss_url,
                'items' => $validation['items_count']
            ];
        } else {
            echo "✗ INVALID - {$validation['error']}\n";
            $failed_sources++;
            $failed_feeds[] = [
                'name' => $source_name,
                'url' => $rss_url,
                'error' => $validation['error']
            ];
        }
    } catch (Exception $e) {
        echo "✗ ERROR - " . $e->getMessage() . "\n";
        $failed_sources++;
        $failed_feeds[] = [
            'name' => $source_name,
            'url' => $rss_url,
            'error' => $e->getMessage()
        ];
    }
    
    echo "\n";
}

echo "DIAGNOSTIC SUMMARY\n";
echo "==================\n";
echo "Total sources tested: $total_sources\n";
echo "Working feeds: $working_sources\n";
echo "Failed feeds: $failed_sources\n\n";

if ($failed_sources > 0) {
    echo "FAILED FEEDS DETAILS\n";
    echo "====================\n";
    foreach ($failed_feeds as $feed) {
        echo "❌ {$feed['name']}\n";
        echo "   URL: {$feed['url']}\n";
        echo "   Error: {$feed['error']}\n\n";
    }
}

if ($working_sources > 0) {
    echo "WORKING FEEDS DETAILS\n";
    echo "====================\n";
    foreach ($working_feeds as $feed) {
        echo "✅ {$feed['name']} - {$feed['items']} items\n";
    }
    echo "\n";
}

// Test actual import for first 3 working feeds
if ($working_sources >= 3) {
    echo "TESTING ACTUAL IMPORT (First 3 working feeds)\n";
    echo "============================================\n\n";
    
    require_once __DIR__ . '/includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(2); // Test with just 2 articles per feed
    
    $test_count = 0;
    foreach ($working_feeds as $feed) {
        if ($test_count >= 3) break;
        
        echo "Testing import from: {$feed['name']}\n";
        
        try {
            $source_data = [
                'id' => null,
                'name' => $feed['name'],
                'url' => $feed['url'],
                'category_id' => 1
            ];
            
            $result = $importer->importFromSource($source_data, 2);
            
            echo "  Total articles found: {$result['total_articles']}\n";
            echo "  Articles imported: {$result['imported_articles']}\n";
            echo "  Duplicate articles: {$result['duplicate_articles']}\n";
            echo "  Skipped articles: {$result['skipped_articles']}\n";
            
            if ($result['imported_articles'] > 0) {
                echo "  ✅ Import successful!\n";
            } else {
                echo "  ⚠️  No articles imported (may be duplicates or content issues)\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ Import failed: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
        $test_count++;
    }
}

// Check recent RSS imports
echo "\nRECENT RSS IMPORTS FROM DATABASE\n";
echo "=================================\n";

$recent_query = "SELECT title, source_url, status, created_at 
               FROM news 
               WHERE news_type = 'rss_import' 
               ORDER BY created_at DESC 
               LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "📰 {$row['title']} ({$row['status']})\n";
        echo "   Source: {$row['source_url']}\n";
        echo "   Created: {$row['created_at']}\n\n";
    }
} else {
    echo "No recent RSS imports found in database\n";
}

// RSS Import Statistics
echo "\nRSS IMPORT STATISTICS\n";
echo "====================\n";

$stats_query = "SELECT 
    COUNT(*) as total_rss,
    COUNT(CASE WHEN status = 'published' THEN 1 END) as published,
    COUNT(CASE WHEN status = 'draft' THEN 1 END) as drafts,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today,
    COUNT(CASE WHEN DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 END) as yesterday
    FROM news WHERE news_type = 'rss_import'";
$stats_result = mysqli_query($conn, $stats_query);

if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
    echo "Total RSS imports: {$stats['total_rss']}\n";
    echo "Published: {$stats['published']}\n";
    echo "Drafts: {$stats['drafts']}\n";
    echo "Imported today: {$stats['today']}\n";
    echo "Imported yesterday: {$stats['yesterday']}\n";
}

echo "\nRECOMMENDATIONS\n";
echo "==============\n";

if ($failed_sources > 0) {
    echo "1. Fix {$failed_sources} failed RSS feeds:\n";
    echo "   - Check if URLs are still valid\n";
    echo "   - Some feeds may have moved or require authentication\n";
    echo "   - Consider replacing with alternative feeds\n\n";
}

if ($working_sources > 0) {
    echo "2. Test full import with working feeds:\n";
    echo "   - Run: cron_import_news.php?cron_key=pk_live_news_2024_cron\n";
    echo "   - Monitor logs in logs/cron_import.log\n\n";
}

echo "3. Set up automatic cron job:\n";
echo "   - Add to crontab: */5 * * * * php /path/to/cron_import_news.php\n";
echo "   - Or use web-based cron with the cron key\n\n";

echo "4. Monitor imports regularly:\n";
echo "   - Check admin panel for draft articles\n";
echo "   - Review and publish quality content\n";
echo "   - Remove or update problematic feeds\n\n";

echo "Diagnostic complete!\n";
?>
