<?php
/**
 * Web Scraper Library for PK Live News
 * Supports scraping news from various sources with content extraction
 */

class WebScraper {
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    private $timeout = 30;
    private $followRedirects = true;
    
    public function __construct() {
        // Check if cURL is enabled
        if (!function_exists('curl_init')) {
            throw new Exception('cURL is not enabled on this server');
        }
    }
    
    /**
     * Fetch content from URL
     */
    public function fetch($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $this->followRedirects,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/rss+xml,application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode - Failed to fetch content from $url");
        }
        
        // Check if content is empty or too small to be valid
        if (empty($content) || strlen($content) < 50) {
            throw new Exception("Invalid content: Response too small or empty");
        }
        
        // For RSS feeds, check if content type is appropriate
        if (strpos($url, 'rss') !== false || strpos($url, 'feed') !== false) {
            if ($contentType && strpos($contentType, 'xml') === false && strpos($contentType, 'rss') === false) {
                // Still try to parse, but add a warning
                error_log("Warning: RSS feed URL returned non-XML content type: $contentType");
            }
        }
        
        return $content;
    }
    
    /**
     * Debug method to check what content is returned from a URL
     */
    public function debugFetch($url) {
        try {
            $content = $this->fetch($url);
            
            $debug = [
                'url' => $url,
                'content_length' => strlen($content),
                'first_200_chars' => substr($content, 0, 200),
                'contains_xml' => strpos($content, '<?xml') !== false || strpos($content, '<rss') !== false,
                'is_html' => strpos($content, '<html') !== false || strpos($content, '<!DOCTYPE') !== false,
                'is_json' => strpos($content, '{') === 0 || strpos($content, '[') === 0,
            ];
            
            return $debug;
        } catch (Exception $e) {
            return [
                'url' => $url,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract article content from HTML
     */
    public function extractArticle($html, $url) {
        $article = [
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'image' => '',
            'author' => '',
            'published_date' => '',
            'source_url' => $url
        ];
        
        // Create DOMDocument for parsing
        $dom = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Extract title
        $titleSelectors = [
            '//h1',
            '//title',
            '//meta[@property="og:title"]/@content',
            '//meta[@name="title"]/@content',
            '//*[@class="title"]',
            '//*[@class="headline"]'
        ];
        
        foreach ($titleSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $article['title'] = trim($nodes->item(0)->textContent);
                break;
            }
        }
        
        // Extract content
        $contentSelectors = [
            '//article',
            '//div[@class="content"]',
            '//div[@class="article-content"]',
            '//div[@class="post-content"]',
            '//div[@class="entry-content"]',
            '//main',
            '//div[@class="story-body"]'
        ];
        
        foreach ($contentSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $content = $this->cleanContent($nodes->item(0));
                if (strlen($content) > 200) {
                    $article['content'] = $content;
                    break;
                }
            }
        }
        
        // Fallback: get all paragraphs
        if (empty($article['content'])) {
            $paragraphs = $xpath->query('//p');
            $content = '';
            foreach ($paragraphs as $p) {
                $text = trim($p->textContent);
                if (strlen($text) > 20) {
                    $content .= $text . "\n\n";
                }
            }
            $article['content'] = trim($content);
        }
        
        // Extract excerpt
        $excerptSelectors = [
            '//meta[@name="description"]/@content',
            '//meta[@property="og:description"]/@content'
        ];
        
        foreach ($excerptSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $article['excerpt'] = trim($nodes->item(0)->textContent);
                break;
            }
        }
        
        // Generate excerpt from content if not found
        if (empty($article['excerpt']) && !empty($article['content'])) {
            $article['excerpt'] = substr(strip_tags($article['content']), 0, 200) . '...';
        }
        
        // Extract image
        $imageSelectors = [
            '//meta[@property="og:image"]/@content',
            '//img[@class="featured"]/@src',
            '//img[@class="main-image"]/@src',
            '//article//img[1]/@src'
        ];
        
        foreach ($imageSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $imgSrc = $nodes->item(0)->textContent;
                $article['image'] = $this->resolveUrl($imgSrc, $url);
                break;
            }
        }
        
        // Extract author
        $authorSelectors = [
            '//meta[@name="author"]/@content',
            '//meta[@property="article:author"]/@content',
            '//*[@class="author"]',
            '//*[@class="byline"]'
        ];
        
        foreach ($authorSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $article['author'] = trim($nodes->item(0)->textContent);
                break;
            }
        }
        
        // Extract published date
        $dateSelectors = [
            '//meta[@property="article:published_time"]/@content',
            '//meta[@name="publish-date"]/@content',
            '//time/@datetime',
            '//*[@class="date"]',
            '//*[@class="published"]'
        ];
        
        foreach ($dateSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $dateStr = trim($nodes->item(0)->textContent);
                $article['published_date'] = $this->parseDate($dateStr);
                break;
            }
        }
        
        return $article;
    }
    
    /**
     * Clean HTML content
     */
    private function cleanContent($node) {
        // Remove unwanted elements
        $unwantedTags = ['script', 'style', 'nav', 'header', 'footer', 'aside', 'advertisement'];
        
        foreach ($unwantedTags as $tag) {
            $elements = $node->getElementsByTagName($tag);
            foreach ($elements as $element) {
                $element->parentNode->removeChild($element);
            }
        }
        
        return trim($node->textContent);
    }
    
    /**
     * Resolve relative URLs
     */
    private function resolveUrl($url, $baseUrl) {
        if (empty($url)) return '';
        
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }
        
        $baseParts = parse_url($baseUrl);
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
     * Parse date string
     */
    private function parseDate($dateStr) {
        $formats = [
            'Y-m-d\TH:i:s\Z',
            'Y-m-d\TH:i:sP',
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'F j, Y',
            'j F Y'
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
     * Enhanced RSS scraping with image extraction
     */
    public function scrapeRSS($feedUrl) {
        // Try to use enhanced RSS parser first
        if (class_exists('EnhancedRSSParser')) {
            try {
                $enhancedParser = new EnhancedRSSParser();
                return $enhancedParser->parseRSS($feedUrl);
            } catch (Exception $e) {
                error_log("Enhanced RSS parser failed, falling back to basic parser: " . $e->getMessage());
            }
        }
        
        // Fallback to basic RSS parsing
        $xml = $this->fetch($feedUrl);
        
        // Check if the response looks like XML/RSS
        if (empty($xml) || strpos($xml, '<?xml') === false && strpos($xml, '<rss') === false) {
            throw new Exception('Invalid RSS feed: URL does not return valid XML/RSS content');
        }
        
        // Suppress XML parsing errors and handle them gracefully
        libxml_use_internal_errors(true);
        $xmlObject = simplexml_load_string($xml);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();
        
        if (!$xmlObject || !empty($xmlErrors)) {
            $errorMsg = 'Invalid RSS feed format';
            if (!empty($xmlErrors)) {
                $errorMsg .= ': ' . $xmlErrors[0]->message;
            }
            throw new Exception($errorMsg);
        }
        
        $articles = [];
        
        foreach ($xmlObject->channel->item as $item) {
            $article = [
                'title' => (string) $item->title,
                'content' => (string) $item->description,
                'excerpt' => '',
                'link' => (string) $item->link,
                'published_date' => $this->parseDate((string) $item->pubDate),
                'image' => ''
            ];
            
            // Enhanced image extraction
            $article['image'] = $this->extractImageFromRSS($item, $article['content'], $feedUrl);
            
            // Generate excerpt
            $article['excerpt'] = substr(strip_tags($article['content']), 0, 200) . '...';
            
            $articles[] = $article;
        }
        
        return $articles;
    }
    
    /**
     * Enhanced image extraction from RSS item
     */
    private function extractImageFromRSS($item, $content, $feedUrl) {
        $image = '';
        
        // Method 1: media:content
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
        
        // Method 3: enclosure
        if (empty($image) && isset($item->enclosure)) {
            if (isset($item->enclosure['url']) && 
                isset($item->enclosure['type']) && 
                strpos($item->enclosure['type'], 'image') !== false) {
                $image = (string) $item->enclosure['url'];
            }
        }
        
        // Method 4: Extract from description HTML
        if (empty($image) && !empty($content)) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($content);
            libxml_clear_errors();
            
            $images = $doc->getElementsByTagName('img');
            if ($images->length > 0) {
                $firstImage = $images->item(0);
                $src = $firstImage->getAttribute('src');
                if (!empty($src)) {
                    $image = $src;
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
     * Check if content is duplicate
     */
    public function isDuplicate($title, $content) {
        global $conn;
        
        // Check for similar titles
        $titleCheck = "SELECT id FROM news WHERE title LIKE ?";
        $stmt = mysqli_prepare($conn, $titleCheck);
        $similarTitle = "%$title%";
        mysqli_stmt_bind_param($stmt, 's', $similarTitle);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            return true;
        }
        
        // Check for similar content (first 200 characters)
        $contentPreview = substr(strip_tags($content), 0, 200);
        $contentCheck = "SELECT id FROM news WHERE content LIKE ?";
        $stmt = mysqli_prepare($conn, $contentCheck);
        $similarContent = "%$contentPreview%";
        mysqli_stmt_bind_param($stmt, 's', $similarContent);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }
}

// Global function for easy access
function scrape_article($url) {
    static $scraper = null;
    if ($scraper === null) {
        $scraper = new WebScraper();
    }
    return $scraper->extractArticle($scraper->fetch($url), $url);
}
?>
