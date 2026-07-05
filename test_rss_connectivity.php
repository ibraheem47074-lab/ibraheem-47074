<?php
/**
 * RSS Connectivity Test
 * Test RSS functionality with basic feed
 */

require_once 'config/database.php';
require_once 'includes/enhanced_rss_parser.php';

echo "<h2>🔍 RSS Connection Test</h2>";

// Test with a simple, reliable RSS feed
$testFeeds = [
    'https://rss.cnn.com/rss/edition.rss',
    'https://feeds.bbci.co.uk/news/rss.xml',
    'https://www.reuters.com/rssFeed/worldNews'
];

$parser = new EnhancedRSSParser();
$results = [];

foreach ($testFeeds as $feedUrl) {
    echo "<h3>Testing: " . parse_url($feedUrl, PHP_URL_HOST) . "</h3>";
    
    try {
        $startTime = microtime(true);
        $feedData = $parser->fetchRSS($feedUrl);
        $fetchTime = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($feedData) {
            $articles = $parser->parseArticles($feedData);
            $articleCount = count($articles);
            
            echo "✅ <strong>Success!</strong><br>";
            echo "⏱️ Fetch time: {$fetchTime}ms<br>";
            echo "📰 Articles found: {$articleCount}<br>";
            
            if ($articleCount > 0) {
                $firstArticle = $articles[0];
                echo "📝 First article: " . htmlspecialchars(substr($firstArticle['title'], 0, 80)) . "...<br>";
                echo "🔗 Link: <a href='" . htmlspecialchars($firstArticle['link']) . "' target='_blank'>View</a><br>";
            }
            
            $results[$feedUrl] = ['success' => true, 'articles' => $articleCount, 'time' => $fetchTime];
        } else {
            echo "❌ <strong>Failed to parse RSS</strong><br>";
            $results[$feedUrl] = ['success' => false, 'error' => 'Parse failed'];
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        $results[$feedUrl] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    echo "<hr>";
}

// Summary
echo "<h2>📊 Test Summary</h2>";
$successCount = 0;
$failCount = 0;

foreach ($results as $url => $result) {
    if ($result['success']) {
        $successCount++;
        echo "✅ " . parse_url($url, PHP_URL_HOST) . " - {$result['articles']} articles ({$result['time']}ms)<br>";
    } else {
        $failCount++;
        echo "❌ " . parse_url($url, PHP_URL_HOST) . " - {$result['error']}<br>";
    }
}

echo "<h3>🎯 Results: {$successCount} working, {$failCount} failed</h3>";

// Network diagnostics
echo "<h2>🌐 Network Diagnostics</h2>";
echo "<h4>DNS Resolution Test:</h4>";

$hosts = ['www.google.com', 'www.bbc.com', 'www.cnn.com'];
foreach ($hosts as $host) {
    $ip = gethostbyname($host);
    if ($ip !== $host) {
        echo "✅ {$host} → {$ip}<br>";
    } else {
        echo "❌ {$host} → DNS resolution failed<br>";
    }
}

echo "<h4>cURL Test:</h4>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response && $httpCode === 200) {
    echo "✅ cURL to Google.com - HTTP {$httpCode}<br>";
} else {
    echo "❌ cURL to Google.com - Error: {$error}<br>";
}

// Recommendations
echo "<h2>💡 Recommendations</h2>";
if ($failCount > 0) {
    echo "<div class='alert alert-warning'>";
    echo "<strong>Network Issues Detected</strong><br>";
    echo "1. Check internet connection<br>";
    echo "2. Verify DNS settings<br>";
    echo "3. Check firewall/proxy settings<br>";
    echo "4. Try using different DNS servers (8.8.8.8, 1.1.1.1)<br>";
    echo "</div>";
} else {
    echo "<div class='alert alert-success'>";
    echo "<strong>RSS feeds are working!</strong><br>";
    echo "You can proceed with normal RSS imports.";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
