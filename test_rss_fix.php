<?php
// Test script for the fixed RSS parser
header('Content-Type: text/plain');

echo "=== RSS PARSER TIMEOUT FIX TEST ===\n\n";

// Include the fixed RSS parser
require_once 'includes/enhanced_rss_parser.php';

try {
    // Create parser instance
    $parser = new EnhancedRSSParser();
    
    echo "Current timeout settings:\n";
    $settings = $parser->getTimeout();
    echo "  Timeout: " . $settings['timeout'] . "s\n";
    echo "  Connect Timeout: " . $settings['connect_timeout'] . "s\n";
    
    echo "\nPHP max_execution_time: " . ini_get('max_execution_time') . "s\n";
    
    // Test with a working RSS feed
    echo "\nTesting BBC News RSS feed...\n";
    $startTime = microtime(true);
    
    $articles = $parser->parseRSS('https://feeds.bbci.co.uk/news/rss.xml');
    
    $endTime = microtime(true);
    $processingTime = round(($endTime - $startTime) * 1000, 2);
    
    echo "SUCCESS: Parsed " . count($articles) . " articles in {$processingTime}ms\n";
    
    // Show first article details
    if (!empty($articles)) {
        $firstArticle = $articles[0];
        echo "\nFirst article details:\n";
        echo "  Title: " . substr($firstArticle['title'], 0, 100) . "...\n";
        echo "  Link: " . $firstArticle['link'] . "\n";
        echo "  Image: " . (!empty($firstArticle['image']) ? 'Yes' : 'No') . "\n";
        echo "  Video: " . (!empty($firstArticle['video_url']) ? 'Yes' : 'No') . "\n";
        echo "  Excerpt: " . substr($firstArticle['excerpt'], 0, 150) . "...\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Error occurred at: " . date('Y-m-d H:i:s') . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
