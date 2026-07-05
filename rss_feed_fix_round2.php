<?php
/**
 * RSS Feed Fix - Round 2
 * Fixes remaining failing RSS feeds with alternative URLs and enhanced user agents
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain');

echo "PK Live News - RSS Feed Fix Round 2\n";
echo "===================================\n\n";

// Second round of RSS feed fixes with alternative URLs
$rss_fixes_round2 = [
    'Dawn News' => [
        'current_url' => 'https://www.dawn.com/rss',
        'new_url' => 'https://www.dawn.com/feed/rss.xml',
        'issue' => '403 Error - Alternative RSS endpoint with .xml extension'
    ],
    'Express Tribune' => [
        'current_url' => 'https://tribune.com.pk/rss',
        'new_url' => 'https://tribune.com.pk/feed/rss.xml',
        'issue' => 'Invalid XML - Alternative RSS endpoint'
    ],
    'Geo News' => [
        'current_url' => 'https://www.geo.tv/feed/',
        'new_url' => 'https://www.geo.tv/rss/latest-stories.xml',
        'issue' => 'Invalid XML - Direct XML feed URL'
    ],
    'Reuters News' => [
        'current_url' => 'https://feeds.reuters.com/reuters/topNews',
        'new_url' => 'https://www.reuters.com/rssFeed/worldNews',
        'issue' => 'Host resolution - Try original URL with proper headers'
    ],
    'The News International' => [
        'current_url' => 'https://www.thenews.com.pk/rss/feed/',
        'new_url' => 'https://www.thenews.com.pk/rss/latest-stories.xml',
        'issue' => 'Invalid XML - Direct XML feed URL'
    ]
];

echo "Updating RSS feed URLs (Round 2)...\n";
$update_count = 0;

foreach ($rss_fixes_round2 as $source_name => $fix) {
    // Update the news_sources table
    $update_query = "UPDATE news_sources SET rss_url = ? WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ss', $fix['new_url'], $source_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo "✓ Updated $source_name\n";
            echo "  Current: {$fix['current_url']}\n";
            echo "  New: {$fix['new_url']}\n";
            echo "  Issue: {$fix['issue']}\n\n";
            $update_count++;
        } else {
            echo "- No changes needed for $source_name\n\n";
        }
    } else {
        echo "✗ Error updating $source_name: " . mysqli_error($conn) . "\n\n";
    }
}

echo "Database updates completed: $update_count sources updated\n\n";

// Create enhanced RSS parser with multiple user agents
echo "Creating enhanced RSS parser with multiple user agents...\n";

$enhanced_parser_code = '<?php
/**
 * Enhanced RSS Parser with Multiple User Agents and Retry Logic
 * Specifically designed to handle 403 errors and connection issues
 */

class EnhancedRSSParserV3 {
    private $userAgents = [
        // Modern browsers
        \'chrome\' => \'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\',
        \'firefox\' => \'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0\',
        \'safari\' => \'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15\',
        
        // RSS readers
        \'feedly\' => \'Mozilla/5.0 (compatible; FeedlyBot/1.0; +https://feedly.com)\',
        \'inoreader\' => \'Mozilla/5.0 (compatible; Inoreader; +https://www.inoreader.com/)\',
        \'feedreader\' => \'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 FeedReader/1.0\',
        
        // News aggregators
        \'googlebot\' => \'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\',
        \'bingbot\' => \'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\'
    ];
    
    private $timeout = 15;
    private $connectTimeout = 8;
    private $maxRedirects = 5;
    
    public function __construct() {
        if (!function_exists(\'curl_init\')) {
            throw new Exception(\'cURL is not enabled on this server\');
        }
    }
    
    /**
     * Fetch RSS feed with retry logic and multiple user agents
     */
    public function fetchRSS($feedUrl, $maxRetries = 5) {
        $userAgentKeys = array_keys($this->userAgents);
        $lastError = null;
        
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $userAgentKey = $userAgentKeys[$attempt % count($userAgentKeys)];
            $userAgent = $this->userAgents[$userAgentKey];
            
            try {
                $result = $this->fetchWithUserAgent($feedUrl, $userAgent);
                if ($result !== false) {
                    return $result;
                }
            } catch (Exception $e) {
                $lastError = $e;
                error_log("RSS fetch attempt " . ($attempt + 1) . " failed for $feedUrl with $userAgentKey: " . $e->getMessage());
                
                // Wait a bit before retry
                if ($attempt < $maxRetries - 1) {
                    usleep(500000); // 0.5 second delay
                }
            }
        }
        
        throw $lastError ?: new Exception("Failed to fetch RSS feed after $maxRetries attempts");
    }
    
    /**
     * Fetch RSS with specific user agent and enhanced headers
     */
    private function fetchWithUserAgent($feedUrl, $userAgent) {
        $ch = curl_init();
        
        // Comprehensive headers to avoid blocking
        $headers = [
            \'Accept: application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8\',
            \'Accept-Language: en-US,en;q=0.9,en-GB;q=0.8,en;q=0.7,*;q=0.6\',
            \'Accept-Encoding: gzip, deflate, br\',
            \'Connection: keep-alive\',
            \'Upgrade-Insecure-Requests: 1\',
            \'Sec-Fetch-Dest: document\',
            \'Sec-Fetch-Mode: navigate\',
            \'Sec-Fetch-Site: none\',
            \'Sec-Fetch-User: ?1\',
            \'Sec-GPC: 1\',
            \'Cache-Control: max-age=0\',
            \'Pragma: no-cache\',
            \'DNT: 1\',
            \'User-Agent: \' . $userAgent
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $feedUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => $this->maxRedirects,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
            CURLOPT_DNS_SERVERS => \'8.8.8.8,1.1.1.1\', // Use public DNS
            CURLOPT_DNS_CACHE_TIMEOUT => 300
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode - Failed to fetch RSS feed from $feedUrl");
        }
        
        if (empty($content) || strlen($content) < 50) {
            throw new Exception("Invalid RSS feed: Content too small or empty");
        }
        
        // Handle gzipped content
        if (substr($content, 0, 2) === "\x1f\x8b") {
            $content = gzdecode($content);
            if ($content === false) {
                throw new Exception("Invalid RSS feed: Failed to decompress gzipped content");
            }
        }
        
        return $content;
    }
    
    /**
     * Parse RSS feed with enhanced error handling
     */
    public function parseRSS($feedUrl) {
        $xmlContent = $this->fetchRSS($feedUrl);
        
        // Validate XML content
        if (empty($xmlContent) || strpos($xmlContent, \'<?xml\') === false && strpos($xmlContent, \'<rss\') === false) {
            throw new Exception(\'Invalid RSS feed: URL does not return valid XML/RSS content\');
        }
        
        // Parse XML with error handling
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();
        
        if (!$xml || !empty($xmlErrors)) {
            $errorMsg = \'Invalid RSS feed format\';
            if (!empty($xmlErrors)) {
                $errorMsg .= \': \' . $xmlErrors[0]->message;
            }
            throw new Exception($errorMsg);
        }
        
        $articles = [];
        
        // Handle different RSS formats
        $items = [];
        if (isset($xml->channel->item)) {
            $items = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            $items = $xml->entry;
        } elseif (isset($xml->item)) {
            $items = $xml->item;
        }
        
        foreach ($items as $item) {
            $article = $this->extractArticleData($item);
            if ($article) {
                $articles[] = $article;
            }
        }
        
        return $articles;
    }
    
    /**
     * Extract article data (simplified version)
     */
    private function extractArticleData($item) {
        $article = [
            \'title\' => \'\',
            \'content\' => \'\',
            \'link\' => \'\',
            \'published_date\' => \'\',
            \'image\' => \'\'
        ];
        
        if (isset($item->title)) {
            $article[\'title\'] = (string) $item->title;
        }
        
        if (isset($item->link)) {
            $article[\'link\'] = (string) $item->link;
        } elseif (isset($item->link[\'href\'])) {
            $article[\'link\'] = (string) $item->link[\'href\'];
        }
        
        if (isset($item->description)) {
            $article[\'content\'] = (string) $item->description;
        } elseif (isset($item->content)) {
            $article[\'content\'] = (string) $item->content;
        }
        
        if (isset($item->pubDate)) {
            $article[\'published_date\'] = date(\'Y-m-d H:i:s\', strtotime((string) $item->pubDate));
        }
        
        // Extract image
        if (isset($item->children(\'media\', true)->content)) {
            $mediaContent = $item->children(\'media\', true)->content;
            if (isset($mediaContent[\'url\'])) {
                $article[\'image\'] = (string) $mediaContent[\'url\'];
            }
        }
        
        return $article;
    }
    
    /**
     * Validate RSS feed
     */
    public function validateFeed($feedUrl) {
        try {
            $content = $this->fetchRSS($feedUrl);
            
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            $xmlErrors = libxml_get_errors();
            libxml_clear_errors();
            
            if (!$xml || !empty($xmlErrors)) {
                return [
                    \'valid\' => false,
                    \'error\' => \'Invalid XML format\'
                ];
            }
            
            $items = [];
            if (isset($xml->channel->item)) {
                $items = $xml->channel->item;
            } elseif (isset($xml->entry)) {
                $items = $xml->entry;
            }
            
            return [
                \'valid\' => true,
                \'title\' => (string) ($xml->channel->title ?? $xml->title ?? \'Unknown Feed\'),
                \'items_count\' => count($items)
            ];
            
        } catch (Exception $e) {
            return [
                \'valid\' => false,
                \'error\' => $e->getMessage()
            ];
        }
    }
}

// Global function for easy access
function parse_rss_feed_v3($feedUrl) {
    static $parser = null;
    if ($parser === null) {
        $parser = new EnhancedRSSParserV3();
    }
    return $parser->parseRSS($feedUrl);
}
?>';

// Write the enhanced parser
file_put_contents(__DIR__ . '/includes/enhanced_rss_parser_v3.php', $enhanced_parser_code);
echo "✓ Created enhanced RSS parser V3 with multiple user agents\n";

// Test the updated feeds
echo "\nTesting updated RSS feeds (Round 2)...\n";
require_once __DIR__ . '/includes/enhanced_rss_parser_v3.php';

$parser = new EnhancedRSSParserV3();

$success_count = 0;
$failed_count = 0;

foreach ($rss_fixes_round2 as $source_name => $fix) {
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

echo "=== ROUND 2 SUMMARY ===\n";
echo "Total feeds tested: " . count($rss_fixes_round2) . "\n";
echo "Successfully fixed: $success_count\n";
echo "Still failing: $failed_count\n";
echo "Database updates: $update_count\n\n";

echo "Next steps:\n";
echo "1. The enhanced parser V3 will handle 403 errors with multiple user agents\n";
echo "2. Run the RSS import again to test all fixes\n";
echo "3. Monitor import logs for any remaining issues\n\n";

echo "RSS Feed Fix Round 2 Complete!\n";
?>
