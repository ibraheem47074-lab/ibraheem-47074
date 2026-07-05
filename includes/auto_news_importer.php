<?php
/**
 * Automatic News Import System
 * Fetches news from RSS feeds with image extraction and copyright compliance
 */

require_once __DIR__ . '/../config/database.php';
require_once 'enhanced_rss_parser.php';
require_once 'web_scraper.php';
require_once 'ai_image_generator.php';
require_once 'smart_prompt_generator.php';
// require_once 'sentiment_analysis.php'; // Commented out to avoid errors

class AutoNewsImporter {
    private $conn;
    private $parser;
    private $scraper;
    private $aiGenerator;
    private $promptGenerator;
    private $maxArticlesPerFeed = 10;
    private $downloadImages = true;
    private $generateAIImages = true;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->parser = new EnhancedRSSParser();
        // Set very short timeouts to prevent PHP execution time exceeded errors
        // Optimized for offline resilience - fail fast and continue
        $this->parser->setTimeout(5, 3); // 5 seconds total, 3 seconds connection
        $this->scraper = new WebScraper();
        $this->aiGenerator = new AIImageGenerator($conn);
        $this->promptGenerator = new SmartPromptGenerator($conn);
    }
    
    /**
     * Import news from all active RSS sources
     */
    public function importFromAllSources($maxArticles = null) {
        $results = [
            'total_feeds' => 0,
            'successful_feeds' => 0,
            'total_articles' => 0,
            'imported_articles' => 0,
            'duplicate_articles' => 0,
            'error_feeds' => 0,
            'details' => []
        ];
        
        // Get all active RSS sources
        $sources_query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' ORDER BY name ASC";
        $sources_result = mysqli_query($this->conn, $sources_query);
        
        if (!$sources_result) {
            throw new Exception("Failed to fetch news sources: " . mysqli_error($this->conn));
        }
        
        $results['total_feeds'] = mysqli_num_rows($sources_result);
        
        while ($source = mysqli_fetch_assoc($sources_result)) {
            try {
                // Validate source structure
                if (!isset($source['id']) || !isset($source['name']) || !isset($source['url'])) {
                    error_log("Invalid source structure: " . json_encode($source));
                    continue;
                }
                
                $feedResult = $this->importFromSource($source, $maxArticles);
                $results['successful_feeds']++;
                $results['total_articles'] += $feedResult['total_articles'];
                $results['imported_articles'] += $feedResult['imported_articles'];
                $results['duplicate_articles'] += $feedResult['duplicate_articles'];
                $results['details'][] = $feedResult;
                
            } catch (Exception $e) {
                $results['error_feeds']++;
                $results['details'][] = [
                    'source_name' => $source['name'] ?? 'Unknown',
                    'source_url' => $source['url'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                    'total_articles' => 0,
                    'imported_articles' => 0,
                    'duplicate_articles' => 0
                ];
                
                error_log("RSS Import Error for " . ($source['name'] ?? 'Unknown') . ": " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Import news from a specific RSS source
     */
    public function importFromSource($source, $maxArticles = null) {
        // Validate source parameter
        if (!is_array($source) || !isset($source['name']) || !isset($source['url'])) {
            throw new Exception("Invalid source parameter: missing required fields");
        }
        
        $result = [
            'source_name' => $source['name'],
            'source_url' => $source['url'],
            'total_articles' => 0,
            'imported_articles' => 0,
            'duplicate_articles' => 0,
            'skipped_articles' => 0,
            'articles' => []
        ];
        
        // Parse RSS feed
        $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
        $articles = $this->parser->parseRSS($rss_url);
        $result['total_articles'] = count($articles);
        
        // Limit articles if specified
        if ($maxArticles !== null && $maxArticles > 0) {
            $articles = array_slice($articles, 0, $maxArticles);
        } else {
            $articles = array_slice($articles, 0, $this->maxArticlesPerFeed);
        }
        
        foreach ($articles as $article) {
            try {
                $importResult = $this->importArticle($article, $source);
                
                if ($importResult['status'] === 'imported') {
                    $result['imported_articles']++;
                } elseif ($importResult['status'] === 'duplicate') {
                    $result['duplicate_articles']++;
                } else {
                    $result['skipped_articles']++;
                }
                
                $result['articles'][] = $importResult;
                
            } catch (Exception $e) {
                $result['articles'][] = [
                    'title' => $article['title'] ?? 'Unknown',
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                error_log("Article Import Error: " . $e->getMessage());
            }
        }
        
        // Update source last scraped timestamp
        if (isset($source['id']) && is_numeric($source['id'])) {
            $this->updateSourceTimestamp($source['id']);
        } else {
            error_log("Warning: Source ID not found or invalid for source: " . ($source['name'] ?? 'Unknown'));
        }
        
        return $result;
    }
    
    /**
     * Import a single article
     */
    private function importArticle($article, $source) {
        $result = [
            'title' => $article['title'],
            'status' => '',
            'image_downloaded' => false,
            'sentiment' => ''
        ];
        
        // Validate required fields
        if (empty($article['title']) || empty($article['content'])) {
            $result['status'] = 'skipped';
            $result['error'] = 'Missing title or content';
            return $result;
        }
        
        // Check for duplicates
        if ($this->isDuplicate($article['title'], $article['content'])) {
            $result['status'] = 'duplicate';
            return $result;
        }
        
        // Generate slug
        $slug = $this->generateSlug($article['title']);
        
        // Download image if exists
        $imagePath = '';
        $imageType = 'manual';
        
        if (!empty($article['image']) && $this->downloadImages) {
            $imagePath = $this->downloadImage($article['image']);
            $result['image_downloaded'] = !empty($imagePath);
            if ($result['image_downloaded']) {
                $imageType = 'rss';
            }
        }
        
        // Generate AI image if no image found and AI generation is enabled
        if (empty($imagePath) && $this->generateAIImages && $this->isAIImageGenerationEnabled()) {
            // Create copyright-compliant content first
            $processedContent = $this->processContentForCopyright($article, $source);
            
            // Perform sentiment analysis (if available)
            $sentiment = ['score' => 0, 'label' => 'neutral'];
            if (function_exists('analyze_sentiment')) {
                $sentiment = analyze_sentiment($article['title'] . ' ' . $article['excerpt']);
            }
            
            // We need to insert article first to get the ID
            $tempImagePath = '';
            $tempImageType = $imageType;
            
            // Insert without image first (include video support)
            $videoUrl = $article['video_url'] ?? '';
            
            $insertQuery = "INSERT INTO news (title, slug, content, excerpt, image, image_type, video_url, category_id, 
                            author_id, status, sentiment_score, sentiment_label, published_at, 
                            source_url, news_type, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rss_import', NOW())";
            
            $stmt = mysqli_prepare($this->conn, $insertQuery);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
            }
            
            $authorId = $this->getDefaultAuthorId();
            $publishedAt = !empty($article['published_date']) ? $article['published_date'] : date('Y-m-d H:i:s');
            $status = 'draft'; // Save RSS imports as draft for review
            
            mysqli_stmt_bind_param($stmt, 'ssssssssisidss', 
                $article['title'], $slug, $processedContent['content'], $processedContent['excerpt'], 
                $tempImagePath, $tempImageType, $videoUrl, $source['category_id'], $authorId, 
                $status, $sentiment['score'], $sentiment['label'], $publishedAt, $article['link']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $newsId = mysqli_insert_id($this->conn);
                
                // Now generate AI image
                $aiImageResult = $this->generateAIImage($article, $source, $newsId);
                if ($aiImageResult['success']) {
                    $imagePath = $aiImageResult['image_path'];
                    $imageType = 'ai';
                    $result['ai_image_generated'] = true;
                    
                    // Update the record with image
                    $updateQuery = "UPDATE news SET image = ?, image_type = ? WHERE id = ?";
                    $updateStmt = mysqli_prepare($this->conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, 'ssi', $imagePath, $imageType, $newsId);
                    mysqli_stmt_execute($updateStmt);
                } else {
                    $result['ai_image_error'] = $aiImageResult['error'];
                }
                
                $result['status'] = 'imported';
                $result['news_id'] = $newsId;
                $result['sentiment'] = $sentiment['label'];
                mysqli_stmt_close($stmt);
                return $result;
            } else {
                throw new Exception("Failed to insert article: " . mysqli_stmt_error($stmt));
            }
        }
        
        // Perform sentiment analysis (if available)
        $sentiment = ['score' => 0, 'label' => 'neutral'];
        if (function_exists('analyze_sentiment')) {
            $sentiment = analyze_sentiment($article['title'] . ' ' . $article['excerpt']);
        }
        $result['sentiment'] = $sentiment['label'];
        
        // Create copyright-compliant content
        $processedContent = $this->processContentForCopyright($article, $source);
        
        // Insert into database with multimedia support
        $videoUrl = $article['video_url'] ?? '';
        $mediaType = $article['media_type'] ?? 'text';
        
        $insertQuery = "INSERT INTO news (title, slug, content, excerpt, image, image_type, video_url, category_id, 
                        author_id, status, sentiment_score, sentiment_label, published_at, 
                        source_url, news_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rss_import', NOW())";
        
        $stmt = mysqli_prepare($this->conn, $insertQuery);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        
        $authorId = $this->getDefaultAuthorId();
        $publishedAt = !empty($article['published_date']) ? $article['published_date'] : date('Y-m-d H:i:s');
        $status = 'draft'; // Save RSS imports as draft for review
        
        mysqli_stmt_bind_param($stmt, 'ssssssssisidss', 
            $article['title'], $slug, $processedContent['content'], $processedContent['excerpt'], 
            $imagePath, $imageType, $videoUrl, $source['category_id'], $authorId, 
            $status, $sentiment['score'], $sentiment['label'], $publishedAt, $article['link']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $result['status'] = 'imported';
            $result['news_id'] = mysqli_insert_id($this->conn);
        } else {
            throw new Exception("Failed to insert article: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    /**
     * Process content to be copyright compliant and readable
     */
    private function processContentForCopyright($article, $source) {
        $content = $article['content'];
        $excerpt = $article['excerpt'];
        
        // Clean up the content first
        $cleanContent = $this->cleanContent($content);
        
        // Create copyright-compliant version
        $processedContent = $cleanContent;
        $processedExcerpt = $excerpt;
        
        // Add source attribution
        $attribution = "\n\n<p><em><strong>Source:</strong> <a href=\"{$article['link']}\" target=\"_blank\" rel=\"noopener\">{$source['name']}</a></em></p>";
        
        // Add read more link
        $readMore = "\n\n<p><strong><a href=\"{$article['link']}\" target=\"_blank\" rel=\"noopener\">Read full story on {$source['name']}</a></strong></p>";
        
        // For certain sources, limit content to summary only
        $summaryOnlySources = ['BBC News', 'CNN', 'Reuters', 'Al Jazeera', 'Google News'];
        if (in_array($source['name'], $summaryOnlySources)) {
            // Keep only meaningful content (300 characters for better context)
            if (strlen($cleanContent) > 300) {
                $cleanContent = substr($cleanContent, 0, 300);
                $lastSpace = strrpos($cleanContent, ' ');
                if ($lastSpace !== false) {
                    $cleanContent = substr($cleanContent, 0, $lastSpace);
                }
                $cleanContent .= '...';
            }
            $processedContent = "<p>{$cleanContent}</p>";
        } else {
            // For other sources, keep full content but clean it up
            $processedContent = "<p>{$cleanContent}</p>";
        }
        
        // Combine content with attribution
        $processedContent .= $attribution . $readMore;
        
        // Update excerpt if needed
        if (empty($processedExcerpt)) {
            $processedExcerpt = substr(strip_tags($processedContent), 0, 200) . '...';
        }
        
        return [
            'content' => $processedContent,
            'excerpt' => $processedExcerpt
        ];
    }
    
    /**
     * Clean content for better readability
     */
    private function cleanContent($content) {
        if (empty($content)) return '';
        
        // Strip HTML tags
        $text = strip_tags($content);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove extra whitespace and newlines
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);
        
        // Remove common RSS artifacts
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', '&', $text);
        $text = preg_replace('/&quot;/', '"', $text);
        $text = preg_replace('/&#39;/', "'", $text);
        
        // Remove unwanted characters but keep punctuation
        $text = preg_replace('/[^\w\s\.\,\!\?\-\:\;\(\)\"\'\/]/', '', $text);
        
        // Clean up multiple spaces
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove common RSS feed patterns
        $patterns = [
            '/Click here to read more\.\.\./i',
            '/Read the full article at\.\.\./i',
            '/Continue reading\.\.\./i',
            '/Source:.*$/i',
            '/Image:.*$/i'
        ];
        
        foreach ($patterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }
        
        return trim($text);
    }
    
    /**
     * Check if article is duplicate
     */
    private function isDuplicate($title, $content) {
        // Check for similar titles
        $titleCheck = "SELECT id FROM news WHERE title LIKE ? OR slug LIKE ?";
        $stmt = mysqli_prepare($this->conn, $titleCheck);
        $similarTitle = "%$title%";
        $slug = $this->generateSlug($title);
        $similarSlug = "%$slug%";
        mysqli_stmt_bind_param($stmt, 'ss', $similarTitle, $similarSlug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            return true;
        }
        
        // Check for similar content (first 200 characters)
        $contentPreview = substr(strip_tags($content), 0, 200);
        if (!empty($contentPreview)) {
            $contentCheck = "SELECT id FROM news WHERE content LIKE ?";
            $stmt = mysqli_prepare($this->conn, $contentCheck);
            $similarContent = "%$contentPreview%";
            mysqli_stmt_bind_param($stmt, 's', $similarContent);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate unique slug
     */
    private function generateSlug($title) {
        $slug = slugify($title);
        
        // Check if slug exists
        $checkQuery = "SELECT id FROM news WHERE slug = ?";
        $stmt = mysqli_prepare($this->conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, 's', $slug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
    
    /**
     * Download image from URL
     */
    private function downloadImage($imageUrl) {
        try {
            $imageData = $this->scraper->fetch($imageUrl);
            
            // Check if it's actually an image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            
            if (strpos($mimeType, 'image/') !== 0) {
                return '';
            }
            
            // Generate filename
            $extension = explode('/', $mimeType)[1];
            $filename = uniqid('rss_') . '.' . $extension;
            $uploadPath = 'uploads/news/' . $filename;
            $fullPath = __DIR__ . '/../' . $uploadPath;
            
            // Ensure directory exists
            $uploadDir = dirname($fullPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file
            if (file_put_contents($fullPath, $imageData)) {
                return $uploadPath;
            }
            
        } catch (Exception $e) {
            error_log("Image download failed: " . $e->getMessage());
        }
        
        return '';
    }
    
    /**
     * Get default author ID for RSS imports
     */
    private function getDefaultAuthorId() {
        // Try to find an admin user
        $query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['id'];
        }
        
        // Fallback to first user
        $query = "SELECT id FROM users LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['id'];
        }
        
        return 1; // Ultimate fallback
    }
    
    /**
     * Update source last scraped timestamp
     */
    private function updateSourceTimestamp($sourceId) {
        // Validate source ID
        if (!is_numeric($sourceId) || $sourceId <= 0) {
            error_log("Invalid source ID for timestamp update: $sourceId");
            return;
        }
        
        $updateQuery = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $updateQuery);
        if (!$stmt) {
            error_log("Failed to prepare timestamp update query: " . mysqli_error($this->conn));
            return;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $sourceId);
        $success = mysqli_stmt_execute($stmt);
        
        if (!$success) {
            error_log("Failed to update source timestamp: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Set configuration options
     */
    public function setMaxArticlesPerFeed($max) {
        $this->maxArticlesPerFeed = (int)$max;
    }
    
    public function setDownloadImages($download) {
        $this->downloadImages = (bool)$download;
    }
    
    public function setGenerateAIImages($generate) {
        $this->generateAIImages = (bool)$generate;
    }
    
    /**
     * Check if AI image generation is enabled
     */
    private function isAIImageGenerationEnabled() {
        // First check if ai_settings table exists
        $tableCheck = "SHOW TABLES LIKE 'ai_settings'";
        $result = mysqli_query($this->conn, $tableCheck);
        
        if (mysqli_num_rows($result) === 0) {
            // Table doesn't exist, assume disabled
            return false;
        }
        
        $query = "SELECT setting_value FROM ai_settings WHERE setting_key = 'ai_image_generation_enabled'";
        $result = mysqli_query($this->conn, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['setting_value'] === 'true';
        }
        return false;
    }
    
    /**
     * Generate AI image for article
     */
    private function generateAIImage($article, $source, $newsId) {
        try {
            // Get category name
            $categoryName = 'International';
            if ($source['category_id']) {
                $catQuery = "SELECT name FROM categories WHERE id = ?";
                $stmt = mysqli_prepare($this->conn, $catQuery);
                mysqli_stmt_bind_param($stmt, 'i', $source['category_id']);
                mysqli_stmt_execute($stmt);
                $catResult = mysqli_stmt_get_result($stmt);
                if ($catRow = mysqli_fetch_assoc($catResult)) {
                    $categoryName = $catRow['name'];
                }
            }
            
            // Generate AI image
            $result = $this->aiGenerator->generateImageForNews(
                $newsId,
                $article['title'],
                $categoryName
            );
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Global function for easy access
function auto_import_news($conn, $maxArticles = null) {
    $importer = new AutoNewsImporter($conn);
    return $importer->importFromAllSources($maxArticles);
}

function import_from_source($conn, $source, $maxArticles = null) {
    $importer = new AutoNewsImporter($conn);
    return $importer->importFromSource($source, $maxArticles);
}
?>
