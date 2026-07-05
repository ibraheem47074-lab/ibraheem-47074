<?php
// Simple test to check RSS parser without database dependency
require_once 'includes/enhanced_rss_parser.php';

try {
    $parser = new EnhancedRSSParser();
    echo "✅ Enhanced RSS Parser loaded successfully!\n";
    
    // Test with a simple RSS feed
    $validation = $parser->validateFeed('http://feeds.bbci.co.uk/news/world/rss.xml');
    
    if ($validation['valid']) {
        echo "✅ RSS Feed validation successful!\n";
        echo "Title: " . $validation['title'] . "\n";
        echo "Format: " . $validation['format'] . "\n";
        echo "Articles: " . $validation['items_count'] . "\n";
    } else {
        echo "❌ RSS Feed validation failed: " . $validation['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
