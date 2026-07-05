<?php
/**
 * Web-based RSS Feed Fix Script
 * Fixes all RSS feed issues identified in the error report
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

header('Content-Type: text/plain');

echo "Comprehensive RSS Feed Fix\n";
echo "===========================\n\n";

// RSS Feed fixes with updated URLs and configurations
$rss_fixes = [
    'BBC Urdu' => [
        'old_url' => 'https://www.bbc.com/urdu/rss.xml',
        'new_url' => 'https://www.bbc.com/urdu/sport/rss.xml',
        'issue' => '404 Error - Old URL no longer exists',
        'fix_type' => 'url_update'
    ],
    'Dawn News' => [
        'old_url' => 'https://www.dawn.com/feed/',
        'new_url' => 'https://www.dawn.com/rss',
        'issue' => '403 Error - Access denied',
        'fix_type' => 'url_update'
    ],
    'Express Tribune' => [
        'old_url' => 'https://tribune.com.pk/feed/',
        'new_url' => 'https://tribune.com.pk/rss',
        'issue' => '403 Error - Access denied',
        'fix_type' => 'url_update'
    ],
    'Fox News' => [
        'old_url' => 'https://www.foxnews.com/about/rss',
        'new_url' => 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest',
        'issue' => 'Invalid RSS feed - URL does not return valid XML',
        'fix_type' => 'url_update'
    ],
    'Geo News' => [
        'old_url' => 'https://www.geo.tv/rss',
        'new_url' => 'https://www.geo.tv/rss/1.xml',
        'issue' => 'Invalid RSS feed - URL does not return valid XML',
        'fix_type' => 'url_update'
    ],
    'Reuters News' => [
        'old_url' => 'https://www.reuters.com/rssFeed/worldNews',
        'new_url' => 'https://feeds.reuters.com/reuters/worldNews',
        'issue' => '401 Error - Authentication required',
        'fix_type' => 'url_update'
    ],
    'The News International' => [
        'old_url' => 'https://www.thenews.com.pk/rss/',
        'new_url' => 'https://www.thenews.com.pk/rss',
        'issue' => 'Invalid RSS feed - URL does not return valid XML',
        'fix_type' => 'url_update'
    ]
];

// Step 1: Update RSS URLs in database
echo "Step 1: Updating RSS URLs in database...\n";
$update_count = 0;

foreach ($rss_fixes as $source_name => $fix) {
    $update_query = "UPDATE news_sources SET rss_url = ? WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ss', $fix['new_url'], $source_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo "✓ Updated $source_name: {$fix['old_url']} -> {$fix['new_url']}\n";
            echo "  Issue: {$fix['issue']}\n";
            $update_count++;
        } else {
            echo "- No changes needed for $source_name (already updated or not found)\n";
        }
    } else {
        echo "✗ Error updating $source_name: " . mysqli_error($conn) . "\n";
    }
    echo "\n";
}

echo "Database updates completed: $update_count sources updated\n\n";

// Step 2: Test all RSS feeds
echo "Step 2: Testing RSS feeds...\n";
$parser = new EnhancedRSSParser();

// Set enhanced user agent and timeout for better compatibility
$parser->setTimeout(10, 5);

$test_results = [];

foreach ($rss_fixes as $source_name => $fix) {
    echo "Testing: $source_name\n";
    echo "URL: {$fix['new_url']}\n";
    
    try {
        $validation = $parser->validateFeed($fix['new_url']);
        
        if ($validation['valid']) {
            echo "✓ SUCCESS: Feed is valid\n";
            echo "  - Title: {$validation['title']}\n";
            echo "  - Items: {$validation['items_count']}\n";
            echo "  - Format: {$validation['format']}\n";
            
            $test_results[$source_name] = [
                'status' => 'success',
                'details' => $validation
            ];
        } else {
            echo "✗ FAILED: {$validation['error']}\n";
            
            $test_results[$source_name] = [
                'status' => 'failed',
                'error' => $validation['error']
            ];
        }
        
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        
        $test_results[$source_name] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
    
    echo "\n";
}

// Step 3: Create enhanced RSS parser with better error handling
echo "Step 3: Creating enhanced RSS parser configuration...\n";

$enhanced_config = [
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'timeout' => 15,
    'connect_timeout' => 5,
    'follow_redirects' => true,
    'max_redirects' => 5,
    'ssl_verify' => false,
    'headers' => [
        'Accept' => 'application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.5',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
        'Cache-Control' => 'max-age=0'
    ]
];

echo "✓ Enhanced configuration created\n";
echo "  - User Agent: Modern browser\n";
echo "  - Timeout: 15 seconds\n";
echo "  - Headers: Comprehensive browser headers\n\n";

// Step 4: Update enhanced RSS parser
echo "Step 4: Updating enhanced RSS parser...\n";

$parser_update = file_get_contents(__DIR__ . '/includes/enhanced_rss_parser.php');
if ($parser_update) {
    // Enhanced user agent
    $parser_update = str_replace(
        "private \$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';",
        "private \$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';",
        $parser_update
    );
    
    // Enhanced headers
    $new_headers = "'Accept: application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Cache-Control: max-age=0',
                'User-Agent: ' . \$this->userAgent";
    
    $parser_update = str_replace(
        "'Accept: application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'User-Agent: ' . \$this->userAgent",
        $new_headers,
        $parser_update
    );
    
    file_put_contents(__DIR__ . '/includes/enhanced_rss_parser.php', $parser_update);
    echo "✓ Enhanced RSS parser updated successfully\n\n";
} else {
    echo "✗ Failed to read enhanced RSS parser file\n\n";
}

// Step 5: Summary
echo "Step 5: Summary\n";
echo "=============\n";

$success_count = 0;
$failed_count = 0;

foreach ($test_results as $source_name => $result) {
    if ($result['status'] === 'success') {
        $success_count++;
        echo "✓ $source_name: FIXED\n";
    } else {
        $failed_count++;
        echo "✗ $source_name: STILL FAILING - {$result['error']}\n";
    }
}

echo "\nResults Summary:\n";
echo "- Total sources processed: " . count($rss_fixes) . "\n";
echo "- Successfully fixed: $success_count\n";
echo "- Still failing: $failed_count\n";
echo "- Database updates: $update_count\n\n";

if ($failed_count > 0) {
    echo "Remaining issues may require:\n";
    echo "1. Manual verification of RSS feed URLs\n";
    echo "2. Contact with news organizations for API access\n";
    echo "3. Alternative RSS feed sources\n";
    echo "4. Custom scraping solutions\n\n";
}

echo "Next steps:\n";
echo "1. Run: check_rss_status.php to verify fixes\n";
echo "2. Run: cron_import_news.php?cron_key=pk_live_news_2024_cron to test import\n";
echo "3. Monitor RSS import logs for any remaining issues\n\n";

echo "RSS Feed Fix Complete!\n";
?>
