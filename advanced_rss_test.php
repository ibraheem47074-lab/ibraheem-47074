<?php
// Advanced RSS Feed Testing Script
// Tests different approaches to fetch RSS feeds

function test_rss_feeds() {
    $feeds = [
        'ARY News' => 'https://arynews.tv/en/feed/',
        'BBC News' => 'http://feeds.bbci.co.uk/news/world/south_asia/rss.xml',
        'Dawn News' => 'https://www.dawn.com/feed/rss/pakistan'
    ];
    
    echo "<h1>RSS Feed Testing</h1>";
    
    foreach ($feeds as $name => $url) {
        echo "<h2>Testing: $name</h2>";
        echo "URL: $url<br>";
        
        // Method 1: Basic file_get_contents
        echo "<h3>Method 1: Basic file_get_contents</h3>";
        $content1 = @file_get_contents($url);
        if ($content1) {
            echo "✓ Content fetched (" . strlen($content1) . " bytes)<br>";
            $xml1 = @simplexml_load_string($content1);
            if ($xml1) {
                echo "✓ XML parsed successfully<br>";
            } else {
                echo "✗ XML parsing failed<br>";
            }
        } else {
            echo "✗ Failed to fetch<br>";
        }
        
        // Method 2: With context and user agent
        echo "<h3>Method 2: With context and user agent</h3>";
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'header' => "Accept: application/rss+xml, application/xml, text/xml\r\n"
            ]
        ]);
        
        $content2 = @file_get_contents($url, false, $context);
        if ($content2) {
            echo "✓ Content fetched (" . strlen($content2) . " bytes)<br>";
            $xml2 = @simplexml_load_string($content2);
            if ($xml2) {
                echo "✓ XML parsed successfully<br>";
                if (isset($xml2->channel->item)) {
                    echo "✓ Found " . count($xml2->channel->item) . " items<br>";
                }
            } else {
                echo "✗ XML parsing failed<br>";
                // Show first 500 characters for debugging
                echo "<pre>" . htmlspecialchars(substr($content2, 0, 500)) . "</pre>";
            }
        } else {
            echo "✗ Failed to fetch<br>";
            $error = error_get_last();
            if ($error) {
                echo "Error: " . $error['message'] . "<br>";
            }
        }
        
        // Method 3: Using cURL
        echo "<h3>Method 3: Using cURL</h3>";
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $content3 = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error3 = curl_error($ch);
            curl_close($ch);
            
            if ($content3) {
                echo "✓ Content fetched (HTTP $http_code, " . strlen($content3) . " bytes)<br>";
                if ($error3) {
                    echo "Warning: $error3<br>";
                }
                $xml3 = @simplexml_load_string($content3);
                if ($xml3) {
                    echo "✓ XML parsed successfully<br>";
                } else {
                    echo "✗ XML parsing failed<br>";
                }
            } else {
                echo "✗ Failed to fetch: $error3<br>";
            }
        } else {
            echo "✗ cURL not available<br>";
        }
        
        echo "<hr>";
    }
}

// Check server capabilities
echo "<h2>Server Capabilities</h2>";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "<br>";
echo "curl_enabled: " . (function_exists('curl_init') ? 'YES' : 'NO') . "<br>";
echo "openssl: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "<br>";

test_rss_feeds();
?>
