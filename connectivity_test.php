<?php
/**
 * Server Connectivity Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Server Connectivity Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
        .test-item { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba; }
        .test-pass { border-left-color: #28a745; }
        .test-fail { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <h1>Server Connectivity Test</h1>
    <p>This tool tests if your server can access external websites and RSS feeds.</p>
    
    <?php
    
    echo "<h2>PHP Environment Check</h2>";
    
    // Check PHP extensions
    $extensions = ['curl', 'openssl', 'mbstring', 'json'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<div class='test-item test-pass'><p class='success'>✓ $ext extension loaded</p></div>";
        } else {
            echo "<div class='test-item test-fail'><p class='error'>✗ $ext extension missing</p></div>";
        }
    }
    
    // Check PHP version
    echo "<div class='test-item'>";
    echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        echo "<p class='success'>✓ PHP version is adequate</p>";
    } else {
        echo "<p class='warning'>⚠ Consider upgrading PHP</p>";
    }
    echo "</div>";
    
    // Check cURL
    echo "<div class='test-item'>";
    echo "<p><strong>cURL Test:</strong></p>";
    if (function_exists('curl_init')) {
        echo "<p class='success'>✓ cURL is available</p>";
        
        // Test cURL with a simple URL
        $ch = curl_init('https://www.google.com');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<p class='error'>✗ cURL Error: $error</p>";
        } elseif ($http_code == 200) {
            echo "<p class='success'>✓ cURL can access external sites</p>";
        } else {
            echo "<p class='warning'>⚠ cURL returned HTTP code: $http_code</p>";
        }
        
    } else {
        echo "<p class='error'>✗ cURL is not available</p>";
    }
    echo "</div>";
    
    echo "<h2>Network Connectivity Tests</h2>";
    
    // Test different types of connections
    $test_urls = [
        'Google (HTTP)' => 'http://www.google.com',
        'Google (HTTPS)' => 'https://www.google.com',
        'BBC News' => 'https://www.bbc.com',
        'RSS Feed Test' => 'https://feeds.bbci.co.uk/news/rss.xml'
    ];
    
    foreach ($test_urls as $name => $url) {
        echo "<div class='test-item'>";
        echo "<h3>$name</h3>";
        echo "<p>Testing: $url</p>";
        
        $start_time = microtime(true);
        
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_HEADER => false
            ]);
            
            $content = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            curl_close($ch);
            
            $end_time = microtime(true);
            $load_time = round(($end_time - $start_time) * 1000, 2);
            
            if ($error) {
                echo "<p class='error'>✗ Failed: $error</p>";
            } elseif ($http_code >= 200 && $http_code < 300) {
                echo "<p class='success'>✓ Success (HTTP $http_code)</p>";
                echo "<p>Response time: {$total_time}s</p>";
                echo "<p>Content size: " . strlen($content) . " bytes</p>";
                
                // Check if it's RSS content
                if (strpos($url, 'rss') !== false) {
                    if (strpos($content, '<?xml') !== false || strpos($content, '<rss') !== false) {
                        echo "<p class='success'>✓ Valid RSS content detected</p>";
                    } else {
                        echo "<p class='warning'>⚠ Not RSS content</p>";
                    }
                }
            } else {
                echo "<p class='error'>✗ HTTP Error: $http_code</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "</div>";
    }
    
    echo "<h2>DNS Resolution Test</h2>";
    
    $domains = ['google.com', 'bbc.com', 'reuters.com', 'aljazeera.com'];
    
    foreach ($domains as $domain) {
        echo "<div class='test-item'>";
        echo "<p>Testing DNS for: $domain</p>";
        
        $ip = gethostbyname($domain);
        
        if ($ip !== $domain) {
            echo "<p class='success'>✓ Resolves to: $ip</p>";
        } else {
            echo "<p class='error'>✗ DNS resolution failed</p>";
        }
        
        echo "</div>";
    }
    
    echo "<h2>Troubleshooting Steps</h2>";
    echo "<div class='test-item'>";
    echo "<h3>If tests are failing, try these fixes:</h3>";
    echo "<ol>";
    echo "<li><strong>Check Internet Connection:</strong> Ensure server has internet access</li>";
    echo "<li><strong>Firewall:</strong> Check if firewall is blocking outbound connections</li>";
    echo "<li><strong>Proxy Settings:</strong> Configure proxy if required</li>";
    echo "<li><strong>SSL Certificates:</strong> Update CA certificates on the server</li>";
    echo "<li><strong>PHP Extensions:</strong> Install missing PHP extensions</li>";
    echo "<li><strong>DNS:</strong> Check DNS resolution (try using 8.8.8.8)</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>Quick Fix Commands (for server admin)</h2>";
    echo "<div class='test-item'>";
    echo "<h3>Ubuntu/Debian:</h3>";
    echo "<pre>apt-get update
apt-get install php-curl php-openssl
update-ca-certificates</pre>";
    
    echo "<h3>CentOS/RHEL:</h3>";
    echo "<pre>yum install php-curl php-openssl
update-ca-trust</pre>";
    
    echo "<h3>Windows:</h3>";
    echo "<pre>; Enable in php.ini
extension=curl
extension=openssl</pre>";
    echo "</div>";
    
    ?>
    
</body>
</html>
