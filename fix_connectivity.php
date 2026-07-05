<?php
// Complete Connectivity Fix Script
header('Content-Type: text/plain');

echo "=== CONNECTIVITY FIX IMPLEMENTATION ===\n\n";

// 1. Apply optimized settings
echo "1. APPLYING OPTIMIZED SETTINGS\n";
ini_set('default_socket_timeout', 45);
ini_set('max_execution_time', 180);
ini_set('allow_url_fopen', 'On');

echo "Updated timeout settings:\n";
echo "  default_socket_timeout: " . ini_get('default_socket_timeout') . "s\n";
echo "  max_execution_time: " . ini_get('max_execution_time') . "s\n";
echo "  allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";

// 2. Create enhanced RSS fetcher class
echo "\n2. CREATING ENHANCED RSS FETCHER\n";

class EnhancedRSSFetcher {
    private $timeout = 60;
    private $connect_timeout = 15;
    private $user_agent = 'PK-LIVE-NEWS-RSS-Reader/2.0';
    
    public function fetchRSS($url) {
        echo "Fetching RSS: $url\n";
        
        // Try multiple methods
        $content = $this->fetchWithCurl($url);
        if ($content === false) {
            echo "  cURL failed, trying file_get_contents...\n";
            $content = $this->fetchWithFileGetContents($url);
        }
        
        if ($content === false) {
            echo "  FAILED: All methods failed\n";
            return false;
        }
        
        // Parse RSS
        $xml = @simplexml_load_string($content);
        if ($xml === false) {
            echo "  FAILED: Invalid XML\n";
            return false;
        }
        
        $title = (string)$xml->channel->title;
        $items = count($xml->channel->item);
        $last_updated = (string)$xml->channel->lastBuildDate ?? 'Unknown';
        
        echo "  SUCCESS: $title ($items items)\n";
        echo "  Last Updated: $last_updated\n";
        
        return [
            'title' => $title,
            'items' => $items,
            'last_updated' => $last_updated,
            'content' => $content
        ];
    }
    
    private function fetchWithCurl($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_TCP_FASTOPEN => true,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);
        
        $start_time = microtime(true);
        $result = curl_exec($ch);
        $end_time = microtime(true);
        
        if (curl_errno($ch)) {
            echo "    cURL Error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return false;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time_ms = round(($end_time - $start_time) * 1000, 2);
        
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            echo "    cURL Success: HTTP $http_code ($time_ms ms)\n";
            return $result;
        }
        
        echo "    HTTP Error: $http_code ($time_ms ms)\n";
        return false;
    }
    
    private function fetchWithFileGetContents($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => $this->user_agent,
                'follow_location' => true,
                'max_redirects' => 5
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $start_time = microtime(true);
        $result = @file_get_contents($url, false, $context);
        $end_time = microtime(true);
        
        if ($result === false) {
            echo "    file_get_contents failed\n";
            return false;
        }
        
        $time_ms = round(($end_time - $start_time) * 1000, 2);
        echo "    file_get_contents Success ($time_ms ms)\n";
        
        return $result;
    }
}

// 3. Test various RSS feeds
echo "\n3. TESTING RSS FEEDS\n";

$fetcher = new EnhancedRSSFetcher();

$rss_feeds = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'CNN' => 'https://rss.cnn.com/rss/edition.rss',
    'Reuters' => 'https://feeds.reuters.com/reuters/topNews',
    'Al Jazeera' => 'https://feeds.aljazeera.com/xml/rss.xml',
    'Geo News Pakistan' => 'https://www.geo.tv/feed/rss.xml',
    'Dawn News' => 'https://www.dawn.com/feed/rss.xml'
];

$successful_feeds = [];
$failed_feeds = [];

foreach ($rss_feeds as $name => $url) {
    echo "\n--- Testing $name ---\n";
    $result = $fetcher->fetchRSS($url);
    
    if ($result !== false) {
        $successful_feeds[$name] = $result;
    } else {
        $failed_feeds[$name] = $url;
    }
}

// 4. Summary
echo "\n4. SUMMARY\n";
echo "Successful feeds: " . count($successful_feeds) . "\n";
echo "Failed feeds: " . count($failed_feeds) . "\n";

if (!empty($successful_feeds)) {
    echo "\nWorking feeds:\n";
    foreach ($successful_feeds as $name => $data) {
        echo "  - $name: {$data['items']} items\n";
    }
}

if (!empty($failed_feeds)) {
    echo "\nFailed feeds:\n";
    foreach ($failed_feeds as $name => $url) {
        echo "  - $name: $url\n";
    }
}

// 5. Create configuration file
echo "\n5. CREATING CONFIGURATION FILE\n";

$config = '<?php
// RSS Connectivity Configuration
// Add this to your main configuration file

// Optimized settings for RSS fetching
ini_set("default_socket_timeout", 45);
ini_set("max_execution_time", 180);
ini_set("allow_url_fopen", "On");

// Enhanced RSS Fetcher Class
class RSSFetcher {
    private $timeout = 60;
    private $connect_timeout = 15;
    private $user_agent = "PK-LIVE-NEWS-RSS-Reader/2.0";
    
    public function fetchRSS($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_USER_AGENT => $this->user_agent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => "gzip, deflate"
        ]);
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            $xml = @simplexml_load_string($result);
            return $xml !== false ? $xml : false;
        }
        
        return false;
    }
}

// Usage example:
// $fetcher = new RSSFetcher();
// $rss = $fetcher->fetchRSS("https://feeds.bbci.co.uk/news/rss.xml");
?>';

file_put_contents('rss_config.php', $config);
echo "Created rss_config.php with optimized settings\n";

echo "\n=== FIX COMPLETE ===\n";
echo "Next steps:\n";
echo "1. Include rss_config.php in your main application\n";
echo "2. Use the RSSFetcher class for all RSS operations\n";
echo "3. Test with the working feeds identified above\n";
echo "4. Monitor performance and adjust timeouts if needed\n";
?>
