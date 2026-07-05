<?php
/**
 * Enhanced Image Import System for RSS Feeds
 * Tests and improves real image extraction and download functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Image Import System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .test-section { border: 1px solid #ddd; margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .image-preview { max-width: 150px; max-height: 100px; border: 1px solid #ccc; }
        .code { background-color: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
        .progress { width: 100%; background-color: #f0f0f0; border-radius: 5px; }
        .progress-bar { width: 0%; height: 20px; background-color: #007bff; border-radius: 5px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🖼️ Enhanced Image Import System Test</h1>
        
        <div class='test-section info'>
            <h3>📋 System Overview</h3>
            <p>This test suite analyzes and enhances the image import functionality for RSS news feeds.</p>
            <ul>
                <li>✅ Analyzes current image extraction methods</li>
                <li>✅ Tests real RSS feed image extraction</li>
                <li>✅ Validates image download functionality</li>
                <li>✅ Provides enhancement recommendations</li>
            </ul>
        </div>";

// Test 1: System Analysis
echo "
        <div class='test-section'>
            <h2>🔍 System Analysis</h2>
";

testSystemAnalysis();

echo "
        </div>";

// Test 2: RSS Feed Image Extraction Test
echo "
        <div class='test-section'>
            <h2>📡 RSS Feed Image Extraction Test</h2>
";

testRSSImageExtraction();

echo "
        </div>";

// Test 3: Image Download Test
echo "
        <div class='test-section'>
            <h2>⬇️ Image Download Test</h2>
";

testImageDownload();

echo "
        </div>";

// Test 4: Enhancement Recommendations
echo "
        <div class='test-section'>
            <h2>🚀 Enhancement Recommendations</h2>
";

showEnhancementRecommendations();

echo "
        </div>";

echo "
    </div>
</body>
</html>";

/**
 * Test 1: System Analysis
 */
function testSystemAnalysis() {
    echo "<h3>Current System Capabilities</h3>";
    
    // Check if required classes exist
    $checks = [
        'AutoNewsImporter' => class_exists('AutoNewsImporter'),
        'EnhancedRSSParser' => class_exists('EnhancedRSSParser'),
        'WebScraper' => class_exists('WebScraper'),
        'AIImageGenerator' => class_exists('AIImageGenerator'),
        'cURL extension' => function_exists('curl_init'),
        'DOM extension' => class_exists('DOMDocument'),
        'File info extension' => function_exists('finfo_open')
    ];
    
    echo "<table>";
    echo "<tr><th>Component</th><th>Status</th><th>Notes</th></tr>";
    
    foreach ($checks as $component => $exists) {
        $status = $exists ? '✅ Available' : '❌ Missing';
        $statusClass = $exists ? 'success' : 'error';
        echo "<tr class='$statusClass'>";
        echo "<td>$component</td>";
        echo "<td>$status</td>";
        echo "<td>" . getComponentNotes($component, $exists) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check upload directories
    echo "<h4>Upload Directories</h4>";
    $directories = [
        'uploads/news' => 'News images',
        'uploads/categories' => 'Category images',
        'uploads/editions' => 'Edition images'
    ];
    
    foreach ($directories as $dir => $description) {
        $path = __DIR__ . '/' . $dir;
        $exists = is_dir($path);
        $writable = $exists && is_writable($path);
        
        echo "<p><strong>$dir</strong> ($description): ";
        if (!$exists) {
            echo "<span class='error'>❌ Directory doesn't exist</span>";
        } elseif (!$writable) {
            echo "<span class='warning'>⚠️ Directory exists but not writable</span>";
        } else {
            echo "<span class='success'>✅ Directory exists and writable</span>";
        }
        echo "</p>";
    }
}

/**
 * Test 2: RSS Image Extraction
 */
function testRSSImageExtraction() {
    echo "<h3>Testing RSS Feed Image Extraction</h3>";
    
    $testFeeds = [
        [
            'name' => 'BBC News World',
            'url' => 'http://feeds.bbci.co.uk/news/world/rss.xml',
            'expected_images' => true
        ],
        [
            'name' => 'CNN Top Stories',
            'url' => 'http://rss.cnn.com/rss/edition.rss',
            'expected_images' => true
        ],
        [
            'name' => 'Reuters World',
            'url' => 'https://www.reuters.com/rssFeed/worldNews',
            'expected_images' => true
        ],
        [
            'name' => 'Al Jazeera',
            'url' => 'https://www.aljazeera.com/xml/rss/all.xml',
            'expected_images' => true
        ]
    ];
    
    foreach ($testFeeds as $feed) {
        echo "<div class='test-section'>";
        echo "<h4>📡 Testing: {$feed['name']}</h4>";
        echo "<p><strong>URL:</strong> " . htmlspecialchars($feed['url']) . "</p>";
        
        try {
            $parser = new EnhancedRSSParser();
            $articles = $parser->parseRSS($feed['url']);
            
            echo "<p><strong>Articles found:</strong> " . count($articles) . "</p>";
            
            $imageStats = [
                'total' => count($articles),
                'with_images' => 0,
                'downloadable' => 0,
                'image_methods' => []
            ];
            
            echo "<table>";
            echo "<tr><th>Article</th><th>Title</th><th>Image Found</th><th>Image URL</th><th>Image Method</th><th>Preview</th></tr>";
            
            $count = 0;
            foreach ($articles as $article) {
                if ($count >= 5) break; // Show first 5 articles
                
                $title = substr(htmlspecialchars($article['title']), 0, 50);
                $hasImage = !empty($article['image']);
                $imageUrl = htmlspecialchars($article['image'] ?? '');
                $imageMethod = detectImageMethod($article['image'] ?? '');
                
                if ($hasImage) {
                    $imageStats['with_images']++;
                    if (isImageDownloadable($article['image'])) {
                        $imageStats['downloadable']++;
                    }
                    $imageStats['image_methods'][$imageMethod] = ($imageStats['image_methods'][$imageMethod] ?? 0) + 1;
                }
                
                echo "<tr>";
                echo "<td>" . ($count + 1) . "</td>";
                echo "<td>{$title}...</td>";
                echo "<td>" . ($hasImage ? '✅ Yes' : '❌ No') . "</td>";
                echo "<td><small>{$imageUrl}</small></td>";
                echo "<td>{$imageMethod}</td>";
                echo "<td>";
                if ($hasImage) {
                    echo "<img src='{$article['image']}' class='image-preview' alt='Article image' onerror=\"this.style.display='none'\">";
                }
                echo "</td>";
                echo "</tr>";
                
                $count++;
            }
            
            echo "</table>";
            
            // Show statistics
            echo "<h5>📊 Image Statistics</h5>";
            echo "<ul>";
            echo "<li>Articles with images: {$imageStats['with_images']}/{$imageStats['total']} (" . round($imageStats['with_images']/$imageStats['total']*100, 1) . "%)</li>";
            echo "<li>Downloadable images: {$imageStats['downloadable']}/{$imageStats['with_images']} (" . round($imageStats['downloadable']/max($imageStats['with_images'],1)*100, 1) . "%)</li>";
            
            if (!empty($imageStats['image_methods'])) {
                echo "<li>Image extraction methods:</li>";
                foreach ($imageStats['image_methods'] as $method => $count) {
                    echo "<ul><li>{$method}: {$count}</li></ul>";
                }
            }
            echo "</ul>";
            
            // Overall result
            $successRate = $imageStats['with_images'] / $imageStats['total'];
            if ($successRate >= 0.8) {
                echo "<p class='success'>✅ Excellent image extraction rate</p>";
            } elseif ($successRate >= 0.5) {
                echo "<p class='warning'>⚠️ Moderate image extraction rate</p>";
            } else {
                echo "<p class='error'>❌ Poor image extraction rate</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "</div>";
    }
}

/**
 * Test 3: Image Download Test
 */
function testImageDownload() {
    echo "<h3>Testing Image Download Functionality</h3>";
    
    // Test URLs with different image types
    $testImages = [
        [
            'url' => 'https://ichef.bbci.co.uk/news/976/cpsprodpb/16F7B/production/_123456789_mediaitem123456789.jpg',
            'type' => 'RSS Feed Image',
            'expected' => 'success'
        ],
        [
            'url' => 'https://cdn.cnn.com/cnnnext/dam/assets/231231000000-01-test-image-large-169.jpg',
            'type' => 'News Site Image',
            'expected' => 'success'
        ],
        [
            'url' => 'https://www.reuters.com/resizer/abc123/test-image.jpg',
            'type' => 'Reuters Image',
            'expected' => 'success'
        ],
        [
            'url' => 'https://example.com/nonexistent.jpg',
            'type' => 'Broken Image',
            'expected' => 'error'
        ]
    ];
    
    echo "<table>";
    echo "<tr><th>Test Image</th><th>Type</th><th>URL</th><th>Expected</th><th>Actual Result</th><th>Download Time</th><th>File Size</th></tr>";
    
    foreach ($testImages as $test) {
        $startTime = microtime(true);
        $result = downloadSingleImage($test['url']);
        $endTime = microtime(true);
        $downloadTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        $resultClass = $result['success'] ? 'success' : 'error';
        $actualResult = $result['success'] ? '✅ Success' : '❌ Failed';
        $fileSize = $result['success'] ? formatBytes($result['file_size']) : 'N/A';
        
        echo "<tr class='$resultClass'>";
        echo "<td>{$test['type']}</td>";
        echo "<td>" . htmlspecialchars($test['url']) . "</td>";
        echo "<td>{$test['expected']}</td>";
        echo "<td>{$actualResult}</td>";
        echo "<td>{$downloadTime}ms</td>";
        echo "<td>{$fileSize}</td>";
        echo "</tr>";
        
        if ($result['success'] && !empty($result['local_path'])) {
            echo "<tr><td colspan='7' style='text-align:center;'>";
            echo "<img src='{$result['local_path']}' style='max-width:100px;max-height:75px;' alt='Downloaded image'>";
            echo "</td></tr>";
        }
    }
    
    echo "</table>";
    
    // Test the actual downloadImage function
    echo "<h4>Testing AutoNewsImporter Download Function</h4>";
    
    try {
        $conn = getDatabaseConnection();
        $importer = new AutoNewsImporter($conn);
        
        $testUrl = 'https://ichef.bbci.co.uk/news/976/cpsprodpb/16F7B/production/_123456789_mediaitem123456789.jpg';
        
        // Use reflection to access private method for testing
        $reflection = new ReflectionClass($importer);
        $method = $reflection->getMethod('downloadImage');
        // For PHP 8.1+, setAccessible is no longer needed for private methods in reflection
        $result = $method->invoke($importer, $testUrl);
        
        if (!empty($result)) {
            echo "<p class='success'>✅ downloadImage() function works correctly</p>";
            echo "<p>Downloaded to: " . htmlspecialchars($result) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ downloadImage() function returned empty result</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error testing downloadImage(): " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

/**
 * Test 4: Enhancement Recommendations
 */
function showEnhancementRecommendations() {
    echo "<h3>🚀 System Enhancement Recommendations</h3>";
    
    $recommendations = [
        [
            'priority' => 'High',
            'title' => 'Improve Image URL Validation',
            'description' => 'Add better validation for image URLs before attempting download',
            'implementation' => 'Add URL format checking, domain whitelist, and size limits',
            'code' => '
// Add to downloadImage method
private function isValidImageUrl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    
    $allowedDomains = ["bbc.co.uk", "cnn.com", "reuters.com", "aljazeera.com"];
    $host = parse_url($url, PHP_URL_HOST);
    
    foreach ($allowedDomains as $domain) {
        if (strpos($host, $domain) !== false) return true;
    }
    
    return false;
}'
        ],
        [
            'priority' => 'High',
            'title' => 'Add Image Caching',
            'description' => 'Cache downloaded images to avoid re-downloading the same images',
            'implementation' => 'Create a hash-based caching system for images',
            'code' => '
// Add image caching
private function getCachedImage($imageUrl) {
    $hash = md5($imageUrl);
    $cachePath = "uploads/cache/{$hash}.jpg";
    
    if (file_exists($cachePath)) {
        return $cachePath;
    }
    
    return null;
}'
        ],
        [
            'priority' => 'Medium',
            'title' => 'Enhanced Image Extraction',
            'description' => 'Extract images from article content when RSS feed doesn\'t include images',
            'implementation' => 'Scrape the article page to find images when RSS doesn\'t provide them',
            'code' => '
// Add content scraping for images
private function extractImageFromArticle($articleUrl) {
    $scraper = new WebScraper();
    $html = $scraper->fetch($articleUrl);
    $article = $scraper->extractArticle($html, $articleUrl);
    return $article["image"] ?? "";
}'
        ],
        [
            'priority' => 'Medium',
            'title' => 'Image Optimization',
            'description' => 'Optimize downloaded images for web performance',
            'implementation' => 'Resize and compress images after download',
            'code' => '
// Add image optimization
private function optimizeImage($imagePath) {
    $info = getimagesize($imagePath);
    if ($info[0] > 1200 || $info[1] > 800) {
        // Resize large images
        $this->resizeImage($imagePath, 1200, 800);
    }
}'
        ],
        [
            'priority' => 'Low',
            'title' => 'Add Image Alt Text Generation',
            'description' => 'Generate alt text for downloaded images using AI',
            'implementation' => 'Use AI to describe images for accessibility',
            'code' => '
// Add AI alt text generation
private function generateImageAltText($imagePath, $articleTitle) {
    // Use AI to generate descriptive alt text
    return $this->aiGenerator->describeImage($imagePath, $articleTitle);
}'
        ]
    ];
    
    foreach ($recommendations as $rec) {
        $priorityClass = $rec['priority'] === 'High' ? 'error' : ($rec['priority'] === 'Medium' ? 'warning' : 'info');
        
        echo "<div class='test-section $priorityClass'>";
        echo "<h4>🎯 {$rec['priority']} Priority: {$rec['title']}</h4>";
        echo "<p><strong>Description:</strong> {$rec['description']}</p>";
        echo "<p><strong>Implementation:</strong> {$rec['implementation']}</p>";
        echo "<details>";
        echo "<summary>View Code Example</summary>";
        echo "<pre class='code'>" . htmlspecialchars($rec['code']) . "</pre>";
        echo "</details>";
        echo "</div>";
    }
    
    echo "<h3>🛠️ Quick Implementation Steps</h3>";
    echo "<ol>";
    echo "<li>Enable image downloading in AutoNewsImporter: <code>\$importer->setDownloadImages(true);</code></li>";
    echo "<li>Set reasonable limits: <code>\$importer->setMaxArticlesPerFeed(5);</code></li>";
    echo "<li>Test with your RSS feeds using the test script</li>";
    echo "<li>Monitor the uploads/news directory for downloaded images</li>";
    echo "<li>Check the database for image_path entries in news table</li>";
    echo "</ol>";
}

// Helper functions
function getComponentNotes($component, $exists) {
    $notes = [
        'AutoNewsImporter' => $exists ? 'Main import system' : 'Core import functionality missing',
        'EnhancedRSSParser' => $exists ? 'Advanced RSS parsing' : 'Basic RSS parsing only',
        'WebScraper' => $exists ? 'Content and image extraction' : 'Limited scraping capability',
        'AIImageGenerator' => $exists ? 'AI image generation available' : 'No AI image generation',
        'cURL extension' => $exists ? 'HTTP requests available' : 'Cannot fetch remote content',
        'DOM extension' => $exists ? 'HTML parsing available' : 'Limited content extraction',
        'File info extension' => $exists ? 'File type detection' : 'Cannot validate image types'
    ];
    
    return $notes[$component] ?? '';
}

function detectImageMethod($imageUrl) {
    if (empty($imageUrl)) return 'None';
    
    if (strpos($imageUrl, 'media:content') !== false) return 'Media Content';
    if (strpos($imageUrl, 'media:thumbnail') !== false) return 'Media Thumbnail';
    if (strpos($imageUrl, 'enclosure') !== false) return 'Enclosure';
    if (strpos($imageUrl, 'og:image') !== false) return 'Open Graph';
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imageUrl)) return 'Direct Image';
    
    return 'Other';
}

function isImageDownloadable($url) {
    if (empty($url)) return false;
    
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close is deprecated in PHP 8.1+, PHP handles cleanup automatically
        
        return $httpCode === 200;
    } catch (Exception $e) {
        return false;
    }
}

function downloadSingleImage($url) {
    $result = [
        'success' => false,
        'file_size' => 0,
        'local_path' => '',
        'error' => ''
    ];
    
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Image-Test/1.0)');
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close is deprecated in PHP 8.1+, PHP handles cleanup automatically
        
        if ($httpCode === 200 && !empty($data)) {
            // Check if it's actually an image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $data);
            // finfo_close is deprecated in PHP 8.1+, PHP handles cleanup automatically
            
            if (strpos($mimeType, 'image/') === 0) {
                $filename = 'test_' . uniqid() . '.jpg';
                $tempPath = sys_get_temp_dir() . '/' . $filename;
                
                if (file_put_contents($tempPath, $data)) {
                    $result['success'] = true;
                    $result['file_size'] = strlen($data);
                    $result['local_path'] = $tempPath;
                }
            }
        }
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }
    
    return $result;
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function getDatabaseConnection() {
    try {
        $conn = new mysqli('localhost', 'root', '', 'pk_live_news');
        if ($conn->connect_error) {
            throw new Exception("Database connection failed");
        }
        return $conn;
    } catch (Exception $e) {
        return null;
    }
}

?>
