<?php
// Connectivity Fix Script
header('Content-Type: text/plain');

echo "=== CONNECTIVITY FIX SCRIPT ===\n\n";

// 1. Optimize PHP settings for better connectivity
echo "1. OPTIMIZING PHP SETTINGS\n";

$fixes = [
    'default_socket_timeout' => 30,
    'max_execution_time' => 120,
    'allow_url_fopen' => 'On'
];

foreach ($fixes as $setting => $value) {
    $current = ini_get($setting);
    if ($current != $value) {
        ini_set($setting, $value);
        echo "Updated $setting: $current -> $value\n";
    } else {
        echo "$setting already set to: $value\n";
    }
}

// 2. Create optimized cURL configuration
echo "\n2. CREATING OPTIMIZED CURL CONFIGURATION\n";

function createOptimizedCurl($url, $timeout = 30) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false,  // Temporarily disable for testing
        CURLOPT_SSL_VERIFYHOST => false,   // Temporarily disable for testing
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_ENCODING => 'gzip, deflate',
        CURLOPT_TCP_FASTOPEN => true,
        CURLOPT_FORBID_REUSE => false,
        CURLOPT_FRESH_CONNECT => false
    ]);
    return $ch;
}

// 3. Test with optimized settings
echo "\n3. TESTING WITH OPTIMIZED SETTINGS\n";

$test_urls = [
    'http://httpbin.org/ip',
    'http://example.com',
    'https://api.ipify.org?format=json'
];

foreach ($test_urls as $url) {
    echo "Testing: $url\n";
    
    $ch = createOptimizedCurl($url);
    $start_time = microtime(true);
    $result = curl_exec($ch);
    $end_time = microtime(true);
    
    if (curl_errno($ch)) {
        echo "  FAILED: " . curl_error($ch) . "\n";
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time_ms = round(($end_time - $start_time) * 1000, 2);
        echo "  SUCCESS: HTTP $http_code ($time_ms ms)\n";
        echo "  Response: " . substr($result, 0, 100) . "...\n";
    }
    curl_close($ch);
    echo "\n";
}

// 4. Create fallback connectivity class
echo "4. CREATING FALLBACK CONNECTIVITY CLASS\n";

class ConnectivityHelper {
    private static $timeout = 30;
    private static $user_agent = 'PK-LIVE-NEWS/1.0';
    
    public static function fetchContent($url, $method = 'GET', $data = null) {
        // Try cURL first
        $result = self::fetchWithCurl($url, $method, $data);
        if ($result !== false) {
            return $result;
        }
        
        // Fallback to file_get_contents
        echo "cURL failed, trying file_get_contents...\n";
        return self::fetchWithFileGetContents($url, $method, $data);
    }
    
    private static function fetchWithCurl($url, $method, $data) {
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::$timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => self::$user_agent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        
        curl_setopt_array($ch, $options);
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return false;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            return $result;
        }
        
        echo "HTTP Error: $http_code\n";
        return false;
    }
    
    private static function fetchWithFileGetContents($url, $method, $data) {
        $context_options = [
            'http' => [
                'method' => $method,
                'timeout' => self::$timeout,
                'user_agent' => self::$user_agent,
                'follow_location' => true,
                'max_redirects' => 3
            ]
        ];
        
        if ($method === 'POST' && $data !== null) {
            $context_options['http']['content'] = is_array($data) ? http_build_query($data) : $data;
            $context_options['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\n";
        }
        
        $context = stream_context_create($context_options);
        
        try {
            $result = file_get_contents($url, false, $context);
            if ($result !== false) {
                return $result;
            }
        } catch (Exception $e) {
            echo "file_get_contents Error: " . $e->getMessage() . "\n";
        }
        
        return false;
    }
    
    public static function testRSSFeed($feed_url) {
        echo "Testing RSS Feed: $feed_url\n";
        
        $content = self::fetchContent($feed_url);
        if ($content === false) {
            echo "  FAILED: Could not fetch content\n";
            return false;
        }
        
        // Try to parse as XML
        $xml = @simplexml_load_string($content);
        if ($xml === false) {
            echo "  FAILED: Invalid XML content\n";
            return false;
        }
        
        $title = (string)$xml->channel->title;
        $item_count = count($xml->channel->item);
        
        echo "  SUCCESS: $title ($item_count items)\n";
        return true;
    }
}

// 5. Test RSS feeds with the helper
echo "\n5. TESTING RSS FEEDS WITH CONNECTIVITY HELPER\n";

$rss_feeds = [
    'https://feeds.bbci.co.uk/news/rss.xml',
    'https://rss.cnn.com/rss/edition.rss',
    'https://feeds.reuters.com/reuters/topNews'
];

foreach ($rss_feeds as $feed) {
    ConnectivityHelper::testRSSFeed($feed);
    echo "\n";
}

// 6. Create configuration file for persistent fixes
echo "6. CREATING PERSISTENT CONFIGURATION\n";

$config_content = '<?php
// Connectivity Configuration Fix
// Add these settings to your PHP configuration or .htaccess

// .htaccess version:
/*
php_value default_socket_timeout 30
php_value max_execution_time 120
php_value allow_url_fopen On
*/

// Or in your PHP script:
ini_set("default_socket_timeout", 30);
ini_set("max_execution_time", 120);
ini_set("allow_url_fopen", "On");

// Optimized cURL settings for RSS feeds
function createRSSCurl($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_USERAGENT => "PK-LIVE-NEWS-RSS-Reader/1.0",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_ENCODING => "gzip, deflate"
    ]);
    return $ch;
}
?>';

file_put_contents('connectivity_config.php', $config_content);
echo "Created connectivity_config.php with persistent settings\n";

echo "\n=== FIX SCRIPT COMPLETE ===\n";
echo "Recommendations:\n";
echo "1. Add the settings from connectivity_config.php to your main configuration\n";
echo "2. Use the ConnectivityHelper class for all external requests\n";
echo "3. Consider increasing server timeout limits if issues persist\n";
echo "4. Check if your hosting provider blocks external connections\n";
?>
