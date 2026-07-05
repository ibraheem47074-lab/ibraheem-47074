<?php
/**
 * Detailed RSS Feed Debug Tool
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Detailed RSS Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px; font-size: 12px; }
        .btn { padding: 10px 20px; background: #007cba; color: white; text-decoration: none; display: inline-block; margin: 5px; border-radius: 4px; }
        .feed-item { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba; }
        .feed-invalid { border-left-color: #dc3545; }
        .feed-valid { border-left-color: #28a745; }
        .debug-section { margin: 15px 0; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Detailed RSS Feed Debug</h1>
    
    <?php
    
    if (isset($_GET['test_feed'])) {
        $feed_url = $_GET['test_feed'];
        echo "<h2>Testing Feed: " . htmlspecialchars($feed_url) . "</h2>\n";
        
        require_once __DIR__ . '/includes/enhanced_rss_parser.php';
        
        try {
            $parser = new EnhancedRSSParser();
            
            echo "<div class='debug-section'>";
            echo "<h3>Step 1: Raw Fetch Test</h3>";
            
            // Test raw fetch
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $feed_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HEADER => true
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "<p class='error'>✗ cURL Error: $error</p>";
            } else {
                echo "<p class='success'>✓ cURL successful</p>";
                echo "<p>HTTP Code: $http_code</p>";
                
                // Separate headers and content
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $header_size);
                $content = substr($response, $header_size);
                
                echo "<p>Content Length: " . strlen($content) . " bytes</p>";
                echo "<p>Content Preview:</p>";
                echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
                
                // Check if it looks like RSS/XML
                if (strpos($content, '<?xml') !== false || strpos($content, '<rss') !== false) {
                    echo "<p class='success'>✓ Contains XML/RSS markers</p>";
                } else {
                    echo "<p class='warning'>⚠ No XML/RSS markers found</p>";
                }
            }
            
            echo "</div>";
            
            echo "<div class='debug-section'>";
            echo "<h3>Step 2: RSS Parser Test</h3>";
            
            $validation = $parser->validateFeed($feed_url);
            
            if ($validation['valid']) {
                echo "<p class='success'>✓ RSS Parser validation successful</p>";
                echo "<p>Feed Title: " . htmlspecialchars($validation['title']) . "</p>";
                echo "<p>Items Count: {$validation['items_count']}</p>";
                echo "<p>Format: {$validation['format']}</p>";
                
                // Try to parse articles
                $articles = $parser->parseRSS($feed_url);
                echo "<p class='success'>✓ Parsed " . count($articles) . " articles</p>";
                
                if (!empty($articles)) {
                    echo "<h4>First Article Details:</h4>";
                    $first = $articles[0];
                    echo "<p><strong>Title:</strong> " . htmlspecialchars($first['title']) . "</p>";
                    echo "<p><strong>Link:</strong> " . htmlspecialchars($first['link']) . "</p>";
                    echo "<p><strong>Image:</strong> " . (!empty($first['image']) ? htmlspecialchars($first['image']) : 'None') . "</p>";
                    echo "<p><strong>Content Length:</strong> " . strlen($first['content']) . " chars</p>";
                }
                
            } else {
                echo "<p class='error'>✗ RSS Parser validation failed</p>";
                echo "<p>Error: " . htmlspecialchars($validation['error']) . "</p>";
                if (isset($validation['details']) && !empty($validation['details'])) {
                    echo "<p>XML Errors:</p>";
                    echo "<pre>" . print_r($validation['details'], true) . "</pre>";
                }
            }
            
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='feed-item feed-invalid'>";
            echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Stack trace:</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }
        
        echo "<p><a class='btn' href='rss_debug_detailed.php'>← Back to All Feeds</a></p>";
        
    } else {
        // Test all common RSS feeds
        require_once __DIR__ . '/config/database.php';
        
        echo "<h2>Testing Common RSS Feeds</h2>\n";
        
        $test_feeds = [
            'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
            'CNN' => 'https://rss.cnn.com/rss/edition.rss',
            'Reuters' => 'https://feeds.reuters.com/reuters/topNews',
            'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
            'The Guardian' => 'https://www.theguardian.com/world/rss',
            'Fox News' => 'https://feeds.foxnews.com/foxnews/latest',
            'Associated Press' => 'https://feeds.apnews.com/rss/apf-topnews',
            'NPR News' => 'https://feeds.npr.org/1001/rss.xml',
            'CBS News' => 'https://feeds.cbsnews.com/CBSNewsMain',
            'ABC News' => 'https://feeds.abcnews.com/abcnews/topstories',
            'Google News' => 'https://news.google.com/rss',
            'Yahoo News' => 'https://news.yahoo.com/rss'
        ];
        
        $working_feeds = [];
        $failed_feeds = [];
        
        foreach ($test_feeds as $name => $url) {
            echo "<div class='feed-item'>";
            echo "<h3>$name</h3>";
            echo "<p><strong>URL:</strong> " . htmlspecialchars($url) . "</p>";
            
            try {
                require_once __DIR__ . '/includes/enhanced_rss_parser.php';
                $parser = new EnhancedRSSParser();
                
                // Quick validation
                $validation = $parser->validateFeed($url);
                
                if ($validation['valid']) {
                    echo "<p class='success'>✓ WORKING - {$validation['items_count']} items</p>";
                    echo "<p>Format: {$validation['format']}</p>";
                    $working_feeds[$name] = $url;
                    
                    // Add quick test link
                    echo "<p><a class='btn' href='?test_feed=" . urlencode($url) . "'>Detailed Test</a></p>";
                    
                } else {
                    echo "<p class='error'>✗ FAILED: " . htmlspecialchars($validation['error']) . "</p>";
                    $failed_feeds[$name] = ['url' => $url, 'error' => $validation['error']];
                    
                    // Add debug link
                    echo "<p><a class='btn' href='?test_feed=" . urlencode($url) . "'>Debug This Feed</a></p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>✗ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</p>";
                $failed_feeds[$name] = ['url' => $url, 'error' => $e->getMessage()];
            }
            
            echo "</div>";
        }
        
        // Summary
        echo "<h2>Summary</h2>";
        echo "<div class='feed-item feed-valid'>";
        echo "<h3>Working Feeds (" . count($working_feeds) . ")</h3>";
        foreach ($working_feeds as $name => $url) {
            echo "<p class='success'>✓ $name</p>";
        }
        echo "</div>";
        
        if (!empty($failed_feeds)) {
            echo "<div class='feed-item feed-invalid'>";
            echo "<h3>Failed Feeds (" . count($failed_feeds) . ")</h3>";
            foreach ($failed_feeds as $name => $info) {
                echo "<p class='error'>✗ $name: " . htmlspecialchars($info['error']) . "</p>";
            }
            echo "</div>";
        }
        
        // Common issues and solutions
        echo "<h2>Common Issues & Solutions</h2>";
        echo "<div class='debug-section'>";
        echo "<h3>Why RSS Feeds Fail:</h3>";
        echo "<ul>";
        echo "<li><strong>Network Issues:</strong> Firewall, proxy, or DNS problems</li>";
        echo "<li><strong>SSL/TLS Issues:</strong> Outdated PHP or missing SSL certificates</li>";
        echo "<li><strong>Feed Moved:</strong> RSS feed URL changed or discontinued</li>";
        echo "<li><strong>Rate Limiting:</strong> Server blocking frequent requests</li>";
        echo "<li><strong>Invalid XML:</strong> Malformed RSS feed</li>";
        echo "<li><strong>User Agent Blocking:</strong> Server blocking default user agents</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-section'>";
        echo "<h3>Quick Fixes:</h3>";
        echo "<ol>";
        echo "<li>Check internet connection</li>";
        echo "<li>Try different RSS feeds (use working ones above)</li>";
        echo "<li>Update PHP cURL SSL certificates</li>";
        echo "<li>Check server firewall settings</li>";
        echo "<li>Use a VPN if blocked by geography</li>";
        echo "</ol>";
        echo "</div>";
        
        // Auto-fix button
        if (!empty($working_feeds)) {
            echo "<h2>Auto-Fix Option</h2>";
            echo "<p>Click below to automatically update your database with working feeds:</p>";
            echo "<a class='btn' href='rss_feed_fixer.php?action=fix'>Update Database with Working Feeds</a>";
        }
    }
    ?>
    
</body>
</html>
