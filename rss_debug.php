<?php
/**
 * RSS Debug Script - Run from command line or web
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "RSS System Debug\n";
echo "================\n\n";

// Check database connection first
try {
    require_once 'config/database.php';
    echo "✓ Database connection established\n";
    
    // Check if news_sources table exists and has RSS sources
    $sources_check = "SELECT id, name, url, status FROM news_sources WHERE type = 'rss' LIMIT 5";
    $sources_result = mysqli_query($conn, $sources_check);
    
    if ($sources_result && mysqli_num_rows($sources_result) > 0) {
        echo "✓ Found " . mysqli_num_rows($sources_result) . " RSS sources:\n";
        while ($source = mysqli_fetch_assoc($sources_result)) {
            echo "  - {$source['name']} ({$source['status']})\n";
        }
    } else {
        echo "✗ No RSS sources found in database\n";
        echo "  Please add RSS sources in admin/manage-sources.php\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test RSS parser
echo "\nTesting RSS Parser...\n";
try {
    require_once 'includes/enhanced_rss_parser.php';
    $parser = new EnhancedRSSParser();
    echo "✓ RSS parser initialized\n";
    
    // Test with a known good RSS feed
    $test_feeds = [
        'https://feeds.bbci.co.uk/news/rss.xml',
        'https://rss.cnn.com/rss/edition.rss',
        'https://feeds.reuters.com/reuters/topNews'
    ];
    
    foreach ($test_feeds as $feed_url) {
        echo "\nTesting feed: $feed_url\n";
        try {
            $validation = $parser->validateFeed($feed_url);
            if ($validation['valid']) {
                echo "✓ Feed is valid - {$validation['items_count']} items\n";
                
                // Try to parse a few articles
                $articles = $parser->parseRSS($feed_url);
                echo "✓ Parsed " . count($articles) . " articles\n";
                
                // Check first article for image
                if (!empty($articles)) {
                    $first = $articles[0];
                    echo "  First article: " . substr($first['title'], 0, 50) . "...\n";
                    echo "  Has image: " . (!empty($first['image']) ? 'YES' : 'NO') . "\n";
                    if (!empty($first['image'])) {
                        echo "  Image URL: " . $first['image'] . "\n";
                    }
                }
            } else {
                echo "✗ Feed validation failed: " . $validation['error'] . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Feed test failed: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ RSS parser error: " . $e->getMessage() . "\n";
}

// Test auto importer
echo "\nTesting Auto News Importer...\n";
try {
    require_once 'includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    echo "✓ Auto news importer initialized\n";
    
    // Test with very limited import
    echo "Running limited import test (max 1 article per feed)...\n";
    $results = $importer->importFromAllSources(1);
    
    echo "Results:\n";
    echo "  Total feeds: {$results['total_feeds']}\n";
    echo "  Successful: {$results['successful_feeds']}\n";
    echo "  Failed: {$results['error_feeds']}\n";
    echo "  Articles imported: {$results['imported_articles']}\n";
    echo "  Duplicates skipped: {$results['duplicate_articles']}\n";
    
    if (!empty($results['details'])) {
        echo "\nFeed details:\n";
        foreach ($results['details'] as $detail) {
            if (isset($detail['error'])) {
                echo "  ✗ {$detail['source_name']}: {$detail['error']}\n";
            } else {
                echo "  ✓ {$detail['source_name']}: {$detail['imported_articles']} imported, {$detail['duplicate_articles']} duplicates\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "✗ Auto importer error: " . $e->getMessage() . "\n";
}

echo "\nDebug complete.\n";
?>
