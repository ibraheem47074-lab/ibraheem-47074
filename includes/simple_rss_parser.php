<?php
/**
 * Simple RSS Parser with Timeout Protection
 * Basic RSS parsing without advanced features to prevent timeouts
 */

class SimpleRSSParser {
    private $timeout = 5; // Very short timeout
    private $maxExecutionTime = 30; // 30 seconds max
    
    public function __construct() {
        // Set execution time limit
        set_time_limit($this->maxExecutionTime);
    }
    
    /**
     * Simple RSS fetch with basic timeout protection
     */
    public function fetchRSS($feedUrl) {
        $startTime = microtime(true);
        
        // Use file_get_contents as fallback if curl fails
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => 'Mozilla/5.0 (compatible; RSS Parser)',
                'header' => 'Accept: application/rss+xml, application/xml, text/xml'
            ]
        ]);
        
        // Try to suppress warnings and handle errors manually
        $content = @file_get_contents($feedUrl, false, $context);
        
        if ($content === false) {
            // Fallback to curl if file_get_contents fails
            return $this->fetchWithCurl($feedUrl, $startTime);
        }
        
        // Check execution time
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($this->maxExecutionTime - 10)) {
            throw new Exception("Execution timeout approaching");
        }
        
        return $content;
    }
    
    /**
     * Fallback curl method with strict timeout
     */
    private function fetchWithCurl($feedUrl, $startTime) {
        if (!function_exists('curl_init')) {
            throw new Exception('Neither file_get_contents nor cURL available');
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $feedUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects to save time
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; RSS Parser)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("RSS fetch error: $error");
        }
        
        // Check execution time
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($this->maxExecutionTime - 10)) {
            throw new Exception("Execution timeout approaching");
        }
        
        return $content;
    }
    
    /**
     * Simple RSS parsing with timeout protection
     */
    public function parseRSS($feedUrl) {
        $startTime = microtime(true);
        
        try {
            $xmlContent = $this->fetchRSS($feedUrl);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch RSS: " . $e->getMessage());
        }
        
        // Check execution time after fetch
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($this->maxExecutionTime - 5)) {
            throw new Exception("Not enough time to parse RSS");
        }
        
        // Basic validation
        if (empty($xmlContent) || strlen($xmlContent) < 100) {
            throw new Exception('Invalid RSS feed: Content too small');
        }
        
        // Simple XML parsing
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        
        if (!$xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception('Invalid XML: ' . substr($errors[0]->message ?? 'Unknown error', 0, 50));
        }
        
        // Quick extraction of basic items
        $items = [];
        $channel = $xml->channel ?? $xml;
        
        if (isset($channel->item)) {
            $itemCount = 0;
            foreach ($channel->item as $item) {
                $itemCount++;
                if ($itemCount > 5) break; // Limit to 5 items to save time
                
                // Check execution time during processing
                $elapsedTime = microtime(true) - $startTime;
                if ($elapsedTime > ($this->maxExecutionTime - 2)) {
                    break;
                }
                
                $items[] = [
                    'title' => (string)($item->title ?? ''),
                    'link' => (string)($item->link ?? ''),
                    'description' => substr((string)($item->description ?? ''), 0, 200),
                    'pubDate' => (string)($item->pubDate ?? ''),
                    'guid' => (string)($item->guid ?? '')
                ];
            }
        }
        
        return [
            'title' => (string)($channel->title ?? 'Unknown Feed'),
            'description' => substr((string)($channel->description ?? ''), 0, 100),
            'link' => (string)($channel->link ?? ''),
            'items' => $items,
            'total_items' => count($items)
        ];
    }
    
    /**
     * Quick validation without full parsing
     */
    public function validateFeed($feedUrl) {
        try {
            $content = $this->fetchRSS($feedUrl);
            
            // Quick check for RSS indicators
            if (strpos($content, '<rss') !== false || strpos($content, '<feed') !== false) {
                return ['valid' => true, 'message' => 'Feed appears valid'];
            } else {
                return ['valid' => false, 'message' => 'Not a valid RSS/Atom feed'];
            }
        } catch (Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
