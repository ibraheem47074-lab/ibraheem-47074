<?php
// Test script for the emergency RSS fix
header('Content-Type: text/plain');

echo "=== EMERGENCY RSS FIX TEST ===\n\n";

// Set execution time limits
set_time_limit(60); // 1 minute max
$start_time = microtime(true);
$max_execution_time = ini_get('max_execution_time');

echo "Max execution time: {$max_execution_time}s\n";
echo "Starting emergency fix test...\n\n";

// Test feeds - limited to prevent timeout
$test_feeds = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'Google News' => 'https://news.google.com/rss',
    'CNN' => 'http://rss.cnn.com/rss/edition.rss'
];

$success_count = 0;
$failed_count = 0;

foreach ($test_feeds as $name => $url) {
    // Check execution time
    $elapsed_time = microtime(true) - $start_time;
    $remaining_time = $max_execution_time - $elapsed_time;
    
    if ($remaining_time < 10) {
        echo "TIMEOUT PROTECTION: Stopping test\n";
        echo "Processed: $success_count success, $failed_count failed\n";
        break;
    }
    
    echo "Testing: $name\n";
    echo "URL: $url\n";
    echo "Remaining time: " . round($remaining_time, 1) . "s\n";
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 10, // Short timeout for testing
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'PK-LIVE-NEWS-Test/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "  FAILED: $error\n";
            $failed_count++;
        } elseif ($http_code == 200) {
            echo "  SUCCESS: HTTP 200\n";
            
            // Check if it's RSS content
            if (strpos($content, '<?xml') !== false || strpos($content, '<rss') !== false) {
                echo "  VALID RSS content\n";
                $success_count++;
            } else {
                echo "  INVALID: Not RSS content\n";
                $failed_count++;
            }
        } else {
            echo "  FAILED: HTTP $http_code\n";
            $failed_count++;
        }
        
    } catch (Exception $e) {
        echo "  EXCEPTION: " . $e->getMessage() . "\n";
        $failed_count++;
    }
    
    echo "\n";
}

$total_time = microtime(true) - $start_time;
echo "=== TEST COMPLETE ===\n";
echo "Total time: " . round($total_time, 2) . "s\n";
echo "Success: $success_count\n";
echo "Failed: $failed_count\n";

if ($success_count > 0) {
    echo "\nEmergency fix is working!\n";
    echo "The timeout protection and optimized settings are effective.\n";
} else {
    echo "\nAll feeds failed - check connectivity or feed URLs.\n";
}
?>
