<?php
/**
 * Enhanced RSS Parser with Advanced Image Extraction
 * Supports multiple image formats: media:content, media:thumbnail, enclosure, description HTML
 */

class EnhancedRSSParser {
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private $timeout = 60; // Increased timeout for better connectivity and larger feeds
    private $followRedirects = true;
    private $connectTimeout = 10; // Increased connection timeout
    private $maxExecutionTime = 300; // Increased max execution time to 300 seconds for more articles
    
    public function __construct() {
        if (!function_exists('curl_init')) {
            throw new Exception('cURL is not enabled on this server');
        }
    }
    
    /**
     * Fetch RSS feed content with enhanced error handling
     */
    public function fetchRSS($feedUrl) {
        // Check execution time before starting
        $startTime = microtime(true);
        $maxTime = ini_get('max_execution_time');
        
        // If max execution time is not set, set it to our limit
        if ($maxTime === false || $maxTime == 0) {
            set_time_limit($this->maxExecutionTime);
            $maxTime = $this->maxExecutionTime;
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $feedUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $this->followRedirects,
            CURLOPT_MAXREDIRS => 3, // Reduced from 5
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout, // Added connection timeout
            CURLOPT_DNS_CACHE_TIMEOUT => 60, // Added DNS cache timeout
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Cache-Control: max-age=0',
                'User-Agent: ' . $this->userAgent
            ]
        ]);
        
        // Check execution time before curl_exec
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($maxTime - 20)) { // Leave 20 seconds buffer for curl execution
            curl_close($ch);
            throw new Exception("Execution timeout: Not enough time to complete cURL request");
        }
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        
        // Check if we're approaching execution time limit
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($maxTime - 10)) { // Leave 10 seconds buffer
            throw new Exception("Execution timeout: RSS feed taking too long to process");
        }
        
        if ($error) {
            if (strpos($error, 'timeout') !== false) {
                throw new Exception("RSS feed timeout: Feed is taking too long to respond");
            }
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
     * Parse RSS feed with enhanced image extraction
     */
    public function parseRSS($feedUrl) {
        $startTime = microtime(true);
        $maxTime = ini_get('max_execution_time');
        
        // If max execution time is not set or is too low, set it to our limit
        if ($maxTime === false || $maxTime == 0 || $maxTime < 60) {
            set_time_limit($this->maxExecutionTime);
            $maxTime = $this->maxExecutionTime;
        }
        
        // Check if we have enough time to process the feed
        if (($maxTime - (microtime(true) - $startTime)) < 30) {
            throw new Exception("Insufficient execution time remaining to process RSS feed");
        }
        
        $xmlContent = $this->fetchRSS($feedUrl);
        
        // Check execution time after fetch
        $elapsedTime = microtime(true) - $startTime;
        if ($elapsedTime > ($maxTime - 15)) { // Leave 15 seconds buffer for parsing
            throw new Exception("Execution timeout: Not enough time to parse RSS feed");
        }
        
        // Validate XML content
        if (empty($xmlContent) || strpos($xmlContent, '<?xml') === false && strpos($xmlContent, '<rss') === false) {
            throw new Exception('Invalid RSS feed: URL does not return valid XML/RSS content');
        }
        
        // Parse XML with error handling
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();
        
        if (!$xml || !empty($xmlErrors)) {
            $errorMsg = 'Invalid RSS feed format';
            if (!empty($xmlErrors)) {
                $errorMsg .= ': ' . $xmlErrors[0]->message;
            }
            throw new Exception($errorMsg);
        }
        
        $articles = [];
        
        // Handle different RSS formats (RSS 2.0, Atom, etc.)
        $items = [];
        if (isset($xml->channel->item)) {
            // RSS 2.0
            $items = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            // Atom feed
            $items = $xml->entry;
        } elseif (isset($xml->item)) {
            // RSS 1.0
            $items = $xml->item;
        }
        
        foreach ($items as $index => $item) {
            // Check execution time before processing each item
            $elapsedTime = microtime(true) - $startTime;
            if ($elapsedTime > ($maxTime - 5)) { // Leave 5 seconds buffer
                break; // Stop processing if we're running out of time
            }
            
            $article = $this->extractArticleData($item, $feedUrl);
            if ($article) {
                $articles[] = $article;
            }
            
            // Increased limit: process up to 200 items to import more articles
            if ($index >= 199) {
                break;
            }
        }
        
        return $articles;
    }
    
    /**
     * Extract article data with comprehensive multimedia extraction
     */
    private function extractArticleData($item, $feedUrl) {
        $article = [
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'link' => '',
            'published_date' => '',
            'image' => '',
            'video_url' => '',
            'video_type' => '',
            'author' => '',
            'categories' => [],
            'media_type' => 'text' // Default to text
        ];
        
        // Extract title
        if (isset($item->title)) {
            $article['title'] = (string) $item->title;
        }
        
        // Extract link
        if (isset($item->link)) {
            $article['link'] = (string) $item->link;
        } elseif (isset($item->link['href'])) {
            // Atom feed format
            $article['link'] = (string) $item->link['href'];
        }
        
        // Extract content/description
        if (isset($item->description)) {
            $article['content'] = (string) $item->description;
        } elseif (isset($item->content)) {
            $article['content'] = (string) $item->content;
        } elseif (isset($item->summary)) {
            $article['content'] = (string) $item->summary;
        }
        
        // Extract published date
        if (isset($item->pubDate)) {
            $article['published_date'] = $this->parseDate((string) $item->pubDate);
        } elseif (isset($item->published)) {
            $article['published_date'] = $this->parseDate((string) $item->published);
        } elseif (isset($item->updated)) {
            $article['published_date'] = $this->parseDate((string) $item->updated);
        }
        
        // Extract author
        if (isset($item->author)) {
            $article['author'] = (string) $item->author;
        } elseif (isset($item->author->name)) {
            $article['author'] = (string) $item->author->name;
        }
        
        // Extract categories
        if (isset($item->category)) {
            foreach ($item->category as $category) {
                $article['categories'][] = (string) $category;
            }
        }
        
        // Enhanced multimedia extraction
        $article['image'] = $this->extractImage($item, $article['content'], $feedUrl);
        $videoData = $this->extractVideo($item, $article['content'], $feedUrl);
        $article['video_url'] = $videoData['url'];
        $article['video_type'] = $videoData['type'];
        
        // Determine media type priority
        if (!empty($article['video_url'])) {
            $article['media_type'] = 'video';
        } elseif (!empty($article['image'])) {
            $article['media_type'] = 'image';
        }
        
        // Generate excerpt
        $article['excerpt'] = $this->generateExcerpt($article['content']);
        
        return $article;
    }
    
    /**
     * Extract image using multiple methods
     */
    private function extractImage($item, $content, $feedUrl) {
        $image = '';
        
        // Method 1: media:content (most common for news feeds)
        if (isset($item->children('media', true)->content)) {
            $mediaContent = $item->children('media', true)->content;
            if (isset($mediaContent['url'])) {
                $image = (string) $mediaContent['url'];
            }
        }
        
        // Method 2: media:thumbnail
        if (empty($image) && isset($item->children('media', true)->thumbnail)) {
            $mediaThumbnail = $item->children('media', true)->thumbnail;
            if (isset($mediaThumbnail['url'])) {
                $image = (string) $mediaThumbnail['url'];
            }
        }
        
        // Method 3: enclosure (for RSS 2.0)
        if (empty($image) && isset($item->enclosure)) {
            if (isset($item->enclosure['url']) && 
                isset($item->enclosure['type']) && 
                strpos($item->enclosure['type'], 'image') !== false) {
                $image = (string) $item->enclosure['url'];
            }
        }
        
        // Method 4: Extract from description HTML
        if (empty($image) && !empty($content)) {
            $image = $this->extractImageFromHTML($content);
        }
        
        // Method 5: Check for image in different namespaces
        if (empty($image)) {
            $namespaces = $item->getNamespaces(true);
            foreach ($namespaces as $prefix => $namespace) {
                if ($prefix !== 'media') { // We already checked media
                    $children = $item->children($namespace);
                    foreach ($children as $child) {
                        if (isset($child['url']) && $this->isImageUrl($child['url'])) {
                            $image = (string) $child['url'];
                            break 2;
                        }
                    }
                }
            }
        }
        
        // Resolve relative URLs
        if (!empty($image)) {
            $image = $this->resolveUrl($image, $feedUrl);
        }
        
        return $image;
    }
    
    /**
     * Extract image from HTML content using DOM parsing
     */
    private function extractImageFromHTML($html) {
        $doc = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();
        
        $images = $doc->getElementsByTagName('img');
        
        if ($images->length > 0) {
            $firstImage = $images->item(0);
            $src = $firstImage->getAttribute('src');
            
            // Validate image URL
            if (!empty($src) && $this->isImageUrl($src)) {
                return $src;
            }
        }
        
        // Also check for meta og:image
        $xpath = new DOMXPath($doc);
        $ogImages = $xpath->query('//meta[@property="og:image"]/@content');
        
        if ($ogImages->length > 0) {
            return $ogImages->item(0)->textContent;
        }
        
        return '';
    }
    
    /**
     * Extract video using multiple methods
     */
    private function extractVideo($item, $content, $feedUrl) {
        $videoData = [
            'url' => '',
            'type' => ''
        ];
        
        // Method 1: media:content with video type
        if (isset($item->children('media', true)->content)) {
            $mediaContent = $item->children('media', true)->content;
            if (isset($mediaContent['url']) && isset($mediaContent['type'])) {
                $type = (string) $mediaContent['type'];
                if (strpos($type, 'video') !== false) {
                    $videoData['url'] = (string) $mediaContent['url'];
                    $videoData['type'] = $type;
                }
            }
        }
        
        // Method 2: enclosure with video type
        if (empty($videoData['url']) && isset($item->enclosure)) {
            if (isset($item->enclosure['url']) && 
                isset($item->enclosure['type']) && 
                strpos($item->enclosure['type'], 'video') !== false) {
                $videoData['url'] = (string) $item->enclosure['url'];
                $videoData['type'] = (string) $item->enclosure['type'];
            }
        }
        
        // Method 3: Extract from content HTML
        if (empty($videoData['url']) && !empty($content)) {
            $videoData = $this->extractVideoFromHTML($content);
        }
        
        // Method 4: Check for YouTube/Vimeo links in content
        if (empty($videoData['url'])) {
            $videoData = $this->extractVideoFromText($content);
        }
        
        // Method 5: Check for video in different namespaces
        if (empty($videoData['url'])) {
            $namespaces = $item->getNamespaces(true);
            foreach ($namespaces as $prefix => $namespace) {
                if ($prefix !== 'media') { // We already checked media
                    $children = $item->children($namespace);
                    foreach ($children as $child) {
                        if (isset($child['url']) && $this->isVideoUrl($child['url'])) {
                            $videoData['url'] = (string) $child['url'];
                            $videoData['type'] = $this->getVideoType($videoData['url']);
                            break 2;
                        }
                    }
                }
            }
        }
        
        // Resolve relative URLs
        if (!empty($videoData['url'])) {
            $videoData['url'] = $this->resolveUrl($videoData['url'], $feedUrl);
        }
        
        return $videoData;
    }
    
    /**
     * Extract video from HTML content using DOM parsing
     */
    private function extractVideoFromHTML($html) {
        $videoData = [
            'url' => '',
            'type' => ''
        ];
        
        $doc = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();
        
        // Check for video tags
        $videos = $doc->getElementsByTagName('video');
        if ($videos->length > 0) {
            $firstVideo = $videos->item(0);
            $src = $firstVideo->getAttribute('src');
            
            if (!empty($src)) {
                $videoData['url'] = $src;
                $videoData['type'] = 'video/html5';
                return $videoData;
            }
            
            // Check for source tags within video
            $sources = $firstVideo->getElementsByTagName('source');
            if ($sources->length > 0) {
                $firstSource = $sources->item(0);
                $src = $firstSource->getAttribute('src');
                $type = $firstSource->getAttribute('type');
                
                if (!empty($src)) {
                    $videoData['url'] = $src;
                    $videoData['type'] = $type ?: 'video/html5';
                    return $videoData;
                }
            }
        }
        
        // Check for iframe tags (YouTube, Vimeo, etc.)
        $iframes = $doc->getElementsByTagName('iframe');
        if ($iframes->length > 0) {
            $firstIframe = $iframes->item(0);
            $src = $firstIframe->getAttribute('src');
            
            if (!empty($src) && $this->isVideoUrl($src)) {
                $videoData['url'] = $src;
                $videoData['type'] = $this->getVideoType($src);
                return $videoData;
            }
        }
        
        // Check for meta og:video
        $xpath = new DOMXPath($doc);
        $ogVideos = $xpath->query('//meta[@property="og:video"]/@content');
        
        if ($ogVideos->length > 0) {
            $videoData['url'] = $ogVideos->item(0)->textContent;
            $videoData['type'] = 'video/og';
            return $videoData;
        }
        
        return $videoData;
    }
    
    /**
     * Extract video URLs from text content
     */
    private function extractVideoFromText($content) {
        $videoData = [
            'url' => '',
            'type' => ''
        ];
        
        // YouTube patterns
        $youtubePatterns = [
            '/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/https?:\/\/(?:www\.)?youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/https?:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]+)/'
        ];
        
        foreach ($youtubePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $videoData['url'] = 'https://www.youtube.com/watch?v=' . $matches[1];
                $videoData['type'] = 'video/youtube';
                return $videoData;
            }
        }
        
        // Vimeo patterns
        $vimeoPatterns = [
            '/https?:\/\/(?:www\.)?vimeo\.com\/(\d+)/',
            '/https?:\/\/player\.vimeo\.com\/video\/(\d+)/'
        ];
        
        foreach ($vimeoPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $videoData['url'] = 'https://vimeo.com/' . $matches[1];
                $videoData['type'] = 'video/vimeo';
                return $videoData;
            }
        }
        
        // Direct video file patterns
        $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv'];
        $pattern = '/https?:\/\/[^\s]+\.(?:' . implode('|', $videoExtensions) . ')(?:\?[^\s]*)?/i';
        
        if (preg_match($pattern, $content, $matches)) {
            $videoData['url'] = $matches[0];
            $videoData['type'] = 'video/direct';
            return $videoData;
        }
        
        return $videoData;
    }
    
    /**
     * Check if URL is a video URL
     */
    private function isVideoUrl($url) {
        $videoHosts = ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com', 'twitch.tv'];
        $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv'];
        
        // Check for video hosting platforms
        foreach ($videoHosts as $host) {
            if (strpos($url, $host) !== false) {
                return true;
            }
        }
        
        // Check for video file extensions
        $pattern = '/\.(?:' . implode('|', $videoExtensions) . ')(?:\?[^\s]*)?$/i';
        return preg_match($pattern, $url);
    }
    
    /**
     * Get video type based on URL
     */
    private function getVideoType($url) {
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'video/youtube';
        } elseif (strpos($url, 'vimeo.com') !== false) {
            return 'video/vimeo';
        } elseif (strpos($url, 'dailymotion.com') !== false) {
            return 'video/dailymotion';
        } elseif (strpos($url, 'twitch.tv') !== false) {
            return 'video/twitch';
        } else {
            return 'video/direct';
        }
    }
    
    /**
     * Check if URL points to an image
     */
    private function isImageUrl($url) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $path = parse_url($url, PHP_URL_PATH);
        
        if ($path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            return in_array($extension, $imageExtensions);
        }
        
        return false;
    }
    
    /**
     * Resolve relative URLs to absolute URLs
     */
    private function resolveUrl($url, $baseUrl) {
        if (empty($url)) return '';
        
        // Already absolute URL
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }
        
        $baseParts = parse_url($baseUrl);
        if (!$baseParts) return $url;
        
        $scheme = $baseParts['scheme'] ?? 'https';
        $host = $baseParts['host'] ?? '';
        
        if (strpos($url, '//') === 0) {
            return $scheme . ':' . $url;
        }
        
        if (strpos($url, '/') === 0) {
            return $scheme . '://' . $host . $url;
        }
        
        return $scheme . '://' . $host . '/' . ltrim($url, '/');
    }
    
    /**
     * Generate excerpt from content - Enhanced for better readability
     */
    private function generateExcerpt($content) {
        if (empty($content)) return '';
        
        // Strip HTML tags completely
        $text = strip_tags($content);
        
        // Remove HTML entities and decode them
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove extra whitespace, newlines, and special characters
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);
        
        // Remove common RSS feed artifacts
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', '&', $text);
        $text = preg_replace('/&quot;/', '"', $text);
        $text = preg_replace('/&#39;/', "'", $text);
        
        // Remove unwanted characters and clean up
        $text = preg_replace('/[^\w\s\.\,\!\?\-\:\;\(\)\"\'\/]/', '', $text);
        
        // Trim and clean up multiple spaces
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Truncate to reasonable length (250 characters for better context)
        if (strlen($text) > 250) {
            $text = substr($text, 0, 250);
            // Don't cut off in the middle of a word
            $lastSpace = strrpos($text, ' ');
            if ($lastSpace !== false) {
                $text = substr($text, 0, $lastSpace);
            }
            $text .= '...';
        }
        
        return trim($text);
    }
    
    /**
     * Parse date string to standard format
     */
    private function parseDate($dateStr) {
        if (empty($dateStr)) return date('Y-m-d H:i:s');
        
        $formats = [
            'Y-m-d\TH:i:s\Z',
            'Y-m-d\TH:i:sP',
            'Y-m-d\TH:i:s\+00:00',
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'F j, Y',
            'j F Y',
            'D, d M Y H:i:s O', // RFC 822
            'D, d M Y H:i:s \G\M\T', // RFC 822 GMT
            'Y-m-d\TH:i:s\.000\Z', // ISO 8601 with milliseconds
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }
        
        // Try strtotime as fallback
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Validate RSS feed URL
     */
    public function validateFeed($feedUrl) {
        try {
            $content = $this->fetchRSS($feedUrl);
            
            // Check if it's valid XML
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            $xmlErrors = libxml_get_errors();
            libxml_clear_errors();
            
            if (!$xml || !empty($xmlErrors)) {
                return [
                    'valid' => false,
                    'error' => 'Invalid XML format',
                    'details' => $xmlErrors
                ];
            }
            
            // Check if it has items/entries
            $items = [];
            if (isset($xml->channel->item)) {
                $items = $xml->channel->item;
            } elseif (isset($xml->entry)) {
                $items = $xml->entry;
            } elseif (isset($xml->item)) {
                $items = $xml->item;
            }
            
            return [
                'valid' => true,
                'title' => (string) ($xml->channel->title ?? $xml->title ?? 'Unknown Feed'),
                'description' => (string) ($xml->channel->description ?? $xml->subtitle ?? ''),
                'items_count' => count($items),
                'format' => $this->detectFeedFormat($xml)
            ];
            
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Detect RSS feed format
     */
    private function detectFeedFormat($xml) {
        if (isset($xml->channel)) {
            return 'RSS 2.0';
        } elseif (isset($xml->entry)) {
            return 'Atom';
        } elseif (isset($xml->item)) {
            return 'RSS 1.0';
        }
        return 'Unknown';
    }
    
    /**
     * Set timeout values
     */
    public function setTimeout($timeout, $connectTimeout = null) {
        $this->timeout = (int)$timeout;
        if ($connectTimeout !== null) {
            $this->connectTimeout = (int)$connectTimeout;
        }
    }
    
    /**
     * Get current timeout settings
     */
    public function getTimeout() {
        return [
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout
        ];
    }
}

// Global function for easy access
function parse_rss_feed($feedUrl) {
    static $parser = null;
    if ($parser === null) {
        $parser = new EnhancedRSSParser();
    }
    return $parser->parseRSS($feedUrl);
}

function validate_rss_feed($feedUrl) {
    static $parser = null;
    if ($parser === null) {
        $parser = new EnhancedRSSParser();
    }
    return $parser->validateFeed($feedUrl);
}
?>
