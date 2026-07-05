<?php
/**
 * Enhanced Image Import System
 * Automatically imports real pictures from RSS feeds with improved extraction and download
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

class EnhancedImageImportSystem {
    private $conn;
    private $importer;
    private $parser;
    private $maxImageSize = 5242880; // 5MB max image size
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $imageQuality = 85; // JPEG quality
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->importer = new AutoNewsImporter($conn);
        $this->parser = new EnhancedRSSParser();
        
        // Configure for optimal image import
        $this->importer->setDownloadImages(true);
        $this->importer->setGenerateAIImages(true);
    }
    
    /**
     * Import news with enhanced image extraction
     */
    public function importWithRealImages($maxArticlesPerFeed = 5) {
        echo "<h2>🖼️ Enhanced Image Import System</h2>\n";
        
        $results = [
            'total_feeds' => 0,
            'successful_feeds' => 0,
            'total_articles' => 0,
            'imported_articles' => 0,
            'images_downloaded' => 0,
            'images_generated' => 0,
            'errors' => []
        ];
        
        try {
            // Get active RSS sources
            $sources_query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' ORDER BY name ASC";
            $sources_result = mysqli_query($this->conn, $sources_query);
            
            if (!$sources_result) {
                throw new Exception("Failed to fetch news sources: " . mysqli_error($this->conn));
            }
            
            $results['total_feeds'] = mysqli_num_rows($sources_result);
            
            echo "<p>Processing {$results['total_feeds']} RSS feeds...</p>\n";
            
            while ($source = mysqli_fetch_assoc($sources_result)) {
                echo "<h3>📡 Processing: {$source['name']}</h3>\n";
                
                try {
                    $feedResult = $this->importFromSourceWithEnhancedImages($source, $maxArticlesPerFeed);
                    
                    $results['successful_feeds']++;
                    $results['total_articles'] += $feedResult['total_articles'];
                    $results['imported_articles'] += $feedResult['imported_articles'];
                    $results['images_downloaded'] += $feedResult['images_downloaded'];
                    $results['images_generated'] += $feedResult['images_generated'];
                    
                    echo "<div class='success'>✅ {$feedResult['imported_articles']} articles imported, {$feedResult['images_downloaded']} images downloaded, {$feedResult['images_generated']} AI images generated</div>\n";
                    
                } catch (Exception $e) {
                    $results['errors'][] = "Error with {$source['name']}: " . $e->getMessage();
                    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
                }
            }
            
            // Display final results
            $this->displayImportResults($results);
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
        }
        
        return $results;
    }
    
    /**
     * Import from a specific source with enhanced image processing
     */
    private function importFromSourceWithEnhancedImages($source, $maxArticles) {
        $result = [
            'source_name' => $source['name'],
            'total_articles' => 0,
            'imported_articles' => 0,
            'images_downloaded' => 0,
            'images_generated' => 0,
            'articles' => []
        ];
        
        // Parse RSS feed
        $articles = $this->parser->parseRSS($source['url']);
        $result['total_articles'] = count($articles);
        
        // Limit articles
        $articles = array_slice($articles, 0, $maxArticles);
        
        foreach ($articles as $article) {
            try {
                $importResult = $this->importArticleWithEnhancedImages($article, $source);
                
                if ($importResult['status'] === 'imported') {
                    $result['imported_articles']++;
                    if ($importResult['image_downloaded']) {
                        $result['images_downloaded']++;
                    }
                    if ($importResult['ai_image_generated']) {
                        $result['images_generated']++;
                    }
                }
                
                $result['articles'][] = $importResult;
                
            } catch (Exception $e) {
                $result['articles'][] = [
                    'title' => $article['title'] ?? 'Unknown',
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Update source timestamp
        $this->updateSourceTimestamp($source['id']);
        
        return $result;
    }
    
    /**
     * Import a single article with enhanced image processing
     */
    private function importArticleWithEnhancedImages($article, $source) {
        $result = [
            'title' => $article['title'],
            'status' => '',
            'image_downloaded' => false,
            'ai_image_generated' => false,
            'image_path' => '',
            'image_method' => ''
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
        
        // Enhanced image processing
        $imageData = $this->processArticleImage($article, $source);
        $imagePath = $imageData['path'];
        $imageType = $imageData['type'];
        $imageMethod = $imageData['method'];
        
        $result['image_method'] = $imageMethod;
        $result['image_path'] = $imagePath;
        
        if ($imageData['downloaded']) {
            $result['image_downloaded'] = true;
        }
        
        if ($imageData['ai_generated']) {
            $result['ai_image_generated'] = true;
        }
        
        // Create copyright-compliant content
        $processedContent = $this->processContentForCopyright($article, $source);
        
        // Insert into database
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
        $status = 'draft';
        
        // Set default sentiment
        $sentimentScore = 0;
        $sentimentLabel = 'neutral';
        
        mysqli_stmt_bind_param($stmt, 'ssssssssisidss', 
            $article['title'], $slug, $processedContent['content'], $processedContent['excerpt'], 
            $imagePath, $imageType, $videoUrl, $source['category_id'], $authorId, 
            $status, $sentimentScore, $sentimentLabel, $publishedAt, $article['link']
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
     * Enhanced image processing with multiple fallback methods
     */
    private function processArticleImage($article, $source) {
        $imageData = [
            'path' => '',
            'type' => 'manual',
            'downloaded' => false,
            'ai_generated' => false,
            'method' => 'none'
        ];
        
        // Method 1: Try to download image from RSS
        if (!empty($article['image'])) {
            $downloadedImage = $this->downloadAndValidateImage($article['image']);
            if ($downloadedImage) {
                $imageData['path'] = $downloadedImage;
                $imageData['type'] = 'rss';
                $imageData['downloaded'] = true;
                $imageData['method'] = 'rss_download';
                return $imageData;
            }
        }
        
        // Method 2: Extract image from article content
        $contentImage = $this->extractImageFromContent($article['content']);
        if ($contentImage) {
            $downloadedImage = $this->downloadAndValidateImage($contentImage);
            if ($downloadedImage) {
                $imageData['path'] = $downloadedImage;
                $imageData['type'] = 'content';
                $imageData['downloaded'] = true;
                $imageData['method'] = 'content_extraction';
                return $imageData;
            }
        }
        
        // Method 3: Scrape article page for images
        if (!empty($article['link'])) {
            $scrapedImage = $this->scrapeImageFromArticle($article['link']);
            if ($scrapedImage) {
                $downloadedImage = $this->downloadAndValidateImage($scrapedImage);
                if ($downloadedImage) {
                    $imageData['path'] = $downloadedImage;
                    $imageData['type'] = 'scraped';
                    $imageData['downloaded'] = true;
                    $imageData['method'] = 'article_scraping';
                    return $imageData;
                }
            }
        }
        
        // Method 4: Generate AI image as fallback
        if ($this->isAIImageGenerationEnabled()) {
            // Insert article first to get ID
            $tempImagePath = '';
            $tempImageType = 'manual';
            
            $processedContent = $this->processContentForCopyright($article, $source);
            
            $insertQuery = "INSERT INTO news (title, slug, content, excerpt, image, image_type, category_id, 
                            author_id, status, published_at, source_url, news_type, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rss_import', NOW())";
            
            $stmt = mysqli_prepare($this->conn, $insertQuery);
            $authorId = $this->getDefaultAuthorId();
            $publishedAt = !empty($article['published_date']) ? $article['published_date'] : date('Y-m-d H:i:s');
            $status = 'draft';
            
            $slug = $this->generateSlug($article['title']);
            
            mysqli_stmt_bind_param($stmt, 'ssssssisssss', 
                $article['title'], $slug, 
                $processedContent['content'], $processedContent['excerpt'], 
                $tempImagePath, $tempImageType, $source['category_id'], $authorId, 
                $status, $publishedAt, $article['link']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $newsId = mysqli_insert_id($this->conn);
                
                // Generate AI image
                $aiImageResult = $this->generateAIImage($article, $source, $newsId);
                if ($aiImageResult['success']) {
                    $imageData['path'] = $aiImageResult['image_path'];
                    $imageData['type'] = 'ai';
                    $imageData['ai_generated'] = true;
                    $imageData['method'] = 'ai_generation';
                    
                    // Update article with AI image
                    $updateQuery = "UPDATE news SET image = ?, image_type = ? WHERE id = ?";
                    $updateStmt = mysqli_prepare($this->conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, 'ssi', $imageData['path'], $imageData['type'], $newsId);
                    mysqli_stmt_execute($updateStmt);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return $imageData;
    }
    
    /**
     * Download and validate image
     */
    private function downloadAndValidateImage($imageUrl) {
        try {
            // Validate URL
            if (!$this->isValidImageUrl($imageUrl)) {
                return false;
            }
            
            // Download image
            $imageData = $this->fetchImageData($imageUrl);
            if (!$imageData) {
                return false;
            }
            
            // Validate image data
            if (!$this->isValidImageData($imageData)) {
                return false;
            }
            
            // Optimize and save
            $optimizedImage = $this->optimizeImage($imageData);
            if (!$optimizedImage) {
                return false;
            }
            
            return $this->saveImage($optimizedImage, $imageUrl);
            
        } catch (Exception $e) {
            error_log("Image download failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate image URL
     */
    private function isValidImageUrl($url) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check file extension
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedImageTypes)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Fetch image data from URL
     */
    private function fetchImageData($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Image-Importer/1.0)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error || $httpCode !== 200 || empty($data)) {
            return false;
        }
        
        // Check file size
        if (strlen($data) > $this->maxImageSize) {
            return false;
        }
        
        return $data;
    }
    
    /**
     * Validate image data
     */
    private function isValidImageData($data) {
        if (empty($data)) {
            return false;
        }
        
        // Check image signature
        $signatures = [
            'jpg' => "\xFF\xD8\xFF",
            'png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'gif' => "GIF87a",
            'gif2' => "GIF89a",
            'webp' => "RIFF"
        ];
        
        foreach ($signatures as $type => $signature) {
            if (substr($data, 0, strlen($signature)) === $signature) {
                return true;
            }
        }
        
        // Additional check with getimagesizefromstring
        if (function_exists('getimagesizefromstring')) {
            $imageInfo = @getimagesizefromstring($data);
            if ($imageInfo !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Optimize image
     */
    private function optimizeImage($data) {
        try {
            // Create image from string
            $image = imagecreatefromstring($data);
            if (!$image) {
                return $data; // Return original if optimization fails
            }
            
            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Resize if too large
            $maxWidth = 1200;
            $maxHeight = 800;
            
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }
            
            // Convert to JPEG with quality setting
            ob_start();
            imagejpeg($image, null, $this->imageQuality);
            $optimizedData = ob_get_contents();
            ob_end_clean();
            
            imagedestroy($image);
            
            return $optimizedData;
            
        } catch (Exception $e) {
            return $data; // Return original if optimization fails
        }
    }
    
    /**
     * Save image to filesystem
     */
    private function saveImage($data, $originalUrl) {
        $filename = 'rss_' . uniqid() . '.jpg';
        $uploadPath = 'uploads/news/' . $filename;
        $fullPath = __DIR__ . '/' . $uploadPath;
        
        // Ensure directory exists
        $uploadDir = dirname($fullPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (file_put_contents($fullPath, $data)) {
            return $uploadPath;
        }
        
        return false;
    }
    
    /**
     * Extract image from HTML content
     */
    private function extractImageFromContent($content) {
        if (empty($content)) {
            return false;
        }
        
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content);
        libxml_clear_errors();
        
        $images = $doc->getElementsByTagName('img');
        if ($images->length > 0) {
            $firstImage = $images->item(0);
            $src = $firstImage->getAttribute('src');
            
            if (!empty($src) && $this->isValidImageUrl($src)) {
                return $src;
            }
        }
        
        return false;
    }
    
    /**
     * Scrape image from article page
     */
    private function scrapeImageFromArticle($articleUrl) {
        try {
            $scraper = new WebScraper();
            $html = $scraper->fetch($articleUrl);
            $article = $scraper->extractArticle($html, $articleUrl);
            
            return $article['image'] ?? false;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate AI image
     */
    private function generateAIImage($article, $source, $newsId) {
        try {
            if (class_exists('AIImageGenerator')) {
                $aiGenerator = new AIImageGenerator($this->conn);
                
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
                
                return $aiGenerator->generateImageForNews($newsId, $article['title'], $categoryName);
            }
        } catch (Exception $e) {
            error_log("AI image generation failed: " . $e->getMessage());
        }
        
        return ['success' => false, 'error' => 'AI generation not available'];
    }
    
    /**
     * Check if AI image generation is enabled
     */
    private function isAIImageGenerationEnabled() {
        $tableCheck = "SHOW TABLES LIKE 'ai_settings'";
        $result = mysqli_query($this->conn, $tableCheck);
        
        if (mysqli_num_rows($result) === 0) {
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
     * Process content for copyright compliance
     */
    private function processContentForCopyright($article, $source) {
        $content = $article['content'];
        $excerpt = $article['excerpt'];
        
        // Clean content
        $cleanContent = strip_tags($content);
        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
        $cleanContent = trim($cleanContent);
        
        // Create attribution
        $attribution = "\n\n<p><em><strong>Source:</strong> <a href=\"{$article['link']}\" target=\"_blank\" rel=\"noopener\">{$source['name']}</a></em></p>";
        $readMore = "\n\n<p><strong><a href=\"{$article['link']}\" target=\"_blank\" rel=\"noopener\">Read full story on {$source['name']}</a></strong></p>";
        
        // Limit content for certain sources
        $summaryOnlySources = ['BBC News', 'CNN', 'Reuters', 'Al Jazeera'];
        if (in_array($source['name'], $summaryOnlySources)) {
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
            $processedContent = "<p>{$cleanContent}</p>";
        }
        
        $processedContent .= $attribution . $readMore;
        
        // Generate excerpt
        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($processedContent), 0, 200) . '...';
        }
        
        return [
            'content' => $processedContent,
            'excerpt' => $excerpt
        ];
    }
    
    /**
     * Check for duplicate articles
     */
    private function isDuplicate($title, $content) {
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
        
        return false;
    }
    
    /**
     * Generate unique slug
     */
    private function generateSlug($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
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
     * Get default author ID
     */
    private function getDefaultAuthorId() {
        $query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['id'];
        }
        
        return 1;
    }
    
    /**
     * Update source timestamp
     */
    private function updateSourceTimestamp($sourceId) {
        $updateQuery = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, 'i', $sourceId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Display import results
     */
    private function displayImportResults($results) {
        echo "<div class='results-summary'>\n";
        echo "<h3>📊 Import Summary</h3>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>Metric</th><th>Count</th></tr>\n";
        echo "<tr><td>Total feeds processed</td><td>{$results['total_feeds']}</td></tr>\n";
        echo "<tr><td>Successful feeds</td><td>{$results['successful_feeds']}</td></tr>\n";
        echo "<tr><td>Total articles found</td><td>{$results['total_articles']}</td></tr>\n";
        echo "<tr><td>Articles imported</td><td>{$results['imported_articles']}</td></tr>\n";
        echo "<tr><td>Images downloaded</td><td>{$results['images_downloaded']}</td></tr>\n";
        echo "<tr><td>AI images generated</td><td>{$results['images_generated']}</td></tr>\n";
        echo "</table>\n";
        
        if (!empty($results['errors'])) {
            echo "<h4>⚠️ Errors</h4>\n";
            echo "<ul>\n";
            foreach ($results['errors'] as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        echo "</div>\n";
    }
}

// Usage example
if (basename($_SERVER['PHP_SELF']) === 'enhanced_image_import_system.php') {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Enhanced Image Import System</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 5px 0; }
            .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 5px 0; }
            .results-summary { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>";
    
    try {
        require_once __DIR__ . '/config/database.php';
        
        if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection failed");
        }
        
        $enhancedImporter = new EnhancedImageImportSystem($conn);
        $results = $enhancedImporter->importWithRealImages(3);
        
    } catch (Exception $e) {
        echo "<div class='error'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "</body></html>";
}

?>
