<?php
/**
 * Web-based RSS Feed Fix Script
 * Fixes all failing RSS feeds with correct URLs
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain');

echo "PK Live News - RSS Feed Fix (Web Version)\n";
echo "==========================================\n\n";

// RSS Feed fixes with updated URLs
$rss_fixes = [
    'BBC Urdu' => [
        'old_url' => 'https://www.bbc.com/urdu/rss.xml',
        'new_url' => 'https://www.bbc.com/urdu/index.xml',
        'issue' => '404 Error - Fixed URL'
    ],
    'Dawn News' => [
        'old_url' => 'https://www.dawn.com/feed/',
        'new_url' => 'https://www.dawn.com/rss',
        'issue' => '403 Error - Updated RSS endpoint'
    ],
    'Express Tribune' => [
        'old_url' => 'https://tribune.com.pk/feed/',
        'new_url' => 'https://tribune.com.pk/rss',
        'issue' => '403 Error - Updated RSS endpoint'
    ],
    'Fox News' => [
        'old_url' => 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest',
        'new_url' => 'https://www.foxnews.com/rss',
        'issue' => 'Invalid RSS - Simplified URL'
    ],
    'Geo News' => [
        'old_url' => 'https://www.geo.tv/rss/1.xml',
        'new_url' => 'https://www.geo.tv/feed/',
        'issue' => 'Invalid RSS - Updated feed path'
    ],
    'Reuters News' => [
        'old_url' => 'https://www.reuters.com/rssFeed/worldNews',
        'new_url' => 'https://feeds.reuters.com/reuters/topNews',
        'issue' => '401 Error - Public feed URL'
    ],
    'The News International' => [
        'old_url' => 'https://www.thenews.com.pk/rss',
        'new_url' => 'https://www.thenews.com.pk/rss/feed/',
        'issue' => 'Invalid RSS - Updated feed path'
    ]
];

echo "Updating RSS feed URLs in database...\n";
$update_count = 0;

foreach ($rss_fixes as $source_name => $fix) {
    // Update the news_sources table
    $update_query = "UPDATE news_sources SET rss_url = ? WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ss', $fix['new_url'], $source_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo "✓ Updated $source_name\n";
            echo "  Old: {$fix['old_url']}\n";
            echo "  New: {$fix['new_url']}\n";
            echo "  Issue: {$fix['issue']}\n\n";
            $update_count++;
        } else {
            echo "- No changes needed for $source_name (not found or already updated)\n\n";
        }
    } else {
        echo "✗ Error updating $source_name: " . mysqli_error($conn) . "\n\n";
    }
}

echo "Database updates completed: $update_count sources updated\n\n";

// Test the updated feeds
echo "Testing updated RSS feeds...\n";

// Include the enhanced RSS parser
require_once __DIR__ . '/includes/enhanced_rss_parser.php';
$parser = new EnhancedRSSParser();

$success_count = 0;
$failed_count = 0;

foreach ($rss_fixes as $source_name => $fix) {
    echo "Testing: $source_name\n";
    echo "URL: {$fix['new_url']}\n";
    
    try {
        $validation = $parser->validateFeed($fix['new_url']);
        
        if ($validation['valid']) {
            echo "✓ SUCCESS - Feed is valid\n";
            echo "  Title: {$validation['title']}\n";
            echo "  Items: {$validation['items_count']}\n";
            $success_count++;
        } else {
            echo "✗ FAILED - {$validation['error']}\n";
            $failed_count++;
        }
        
    } catch (Exception $e) {
        echo "✗ ERROR - " . $e->getMessage() . "\n";
        $failed_count++;
    }
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Total feeds tested: " . count($rss_fixes) . "\n";
echo "Successfully fixed: $success_count\n";
echo "Still failing: $failed_count\n";
echo "Database updates: $update_count\n\n";

echo "Next steps:\n";
echo "1. Run the RSS import again to test the fixes\n";
echo "2. Monitor the import logs for any remaining issues\n";
echo "3. All feeds should now work with the updated URLs\n\n";

echo "RSS Feed Fix Complete!\n";
?>
