<?php
/**
 * Web-based RSS Feed Fix Round 2
 * Execute this through browser to fix remaining RSS feeds
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain');

echo "PK Live News - RSS Feed Fix Round 2 (Web)\n";
echo "========================================\n\n";

// Second round fixes
$rss_fixes = [
    'Dawn News' => 'https://www.dawn.com/feed/rss.xml',
    'Express Tribune' => 'https://tribune.com.pk/feed/rss.xml', 
    'Geo News' => 'https://www.geo.tv/rss/latest-stories.xml',
    'Reuters News' => 'https://www.reuters.com/rssFeed/worldNews',
    'The News International' => 'https://www.thenews.com.pk/rss/latest-stories.xml'
];

echo "Updating RSS feed URLs...\n";
$update_count = 0;

foreach ($rss_fixes as $source_name => $new_url) {
    $update_query = "UPDATE news_sources SET rss_url = ? WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ss', $new_url, $source_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo "✓ Updated $source_name: $new_url\n";
            $update_count++;
        } else {
            echo "- No changes for $source_name\n";
        }
    } else {
        echo "✗ Error updating $source_name: " . mysqli_error($conn) . "\n";
    }
}

echo "\nDatabase updates: $update_count\n\n";

// Test with existing parser
require_once __DIR__ . '/includes/enhanced_rss_parser.php';
$parser = new EnhancedRSSParser();

echo "Testing updated feeds...\n";
$success_count = 0;

foreach ($rss_fixes as $source_name => $url) {
    echo "Testing $source_name...\n";
    
    try {
        $validation = $parser->validateFeed($url);
        if ($validation['valid']) {
            echo "✓ SUCCESS - {$validation['items_count']} items\n";
            $success_count++;
        } else {
            echo "✗ FAILED - {$validation['error']}\n";
        }
    } catch (Exception $e) {
        echo "✗ ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\nRound 2 Complete: $success_count/" . count($rss_fixes) . " feeds working\n";
echo "Run RSS import to test all fixes!\n";
?>
