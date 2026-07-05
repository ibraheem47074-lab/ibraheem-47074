<?php
/**
 * Test Real Image Import from RSS Feeds
 * This script tests the actual image extraction and download functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';

echo "<h1>Real Image Import Test</h1>\n";

try {
    // Initialize database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Create importer instance
    $importer = new AutoNewsImporter($conn);
    
    // Configure for testing
    $importer->setMaxArticlesPerFeed(3); // Test with 3 articles per feed
    $importer->setDownloadImages(true);
    
    echo "<h2>Testing RSS Feed Image Extraction</h2>\n";
    
    // Get some active RSS sources for testing
    $sources_query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 3";
    $sources_result = mysqli_query($conn, $sources_query);
    
    if (!$sources_result || mysqli_num_rows($sources_result) === 0) {
        echo "<p>No active RSS sources found. Let's test with some popular news feeds...</p>\n";
        
        // Test with some popular RSS feeds
        $testFeeds = [
            [
                'name' => 'BBC News',
                'url' => 'http://feeds.bbci.co.uk/news/rss.xml',
                'category_id' => 1
            ],
            [
                'name' => 'CNN News',
                'url' => 'http://rss.cnn.com/rss/edition.rss',
                'category_id' => 1
            ],
            [
                'name' => 'Reuters',
                'url' => 'https://www.reuters.com/rssFeed/worldNews',
                'category_id' => 1
            ]
        ];
        
        foreach ($testFeeds as $source) {
            echo "<h3>Testing: {$source['name']}</h3>\n";
            testFeedImageExtraction($importer, $source);
        }
    } else {
        while ($source = mysqli_fetch_assoc($sources_result)) {
            echo "<h3>Testing: {$source['name']}</h3>\n";
            testFeedImageExtraction($importer, $source);
        }
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

/**
 * Test image extraction from a specific feed
 */
function testFeedImageExtraction($importer, $source) {
    try {
        echo "<div class='feed-test'>\n";
        echo "<p><strong>Feed URL:</strong> " . htmlspecialchars($source['url']) . "</p>\n";
        
        // Import from this source
        $result = $importer->importFromSource($source, 3);
        
        echo "<h4>Results:</h4>\n";
        echo "<ul>\n";
        echo "<li>Total articles found: {$result['total_articles']}</li>\n";
        echo "<li>Articles imported: {$result['imported_articles']}</li>\n";
        echo "<li>Duplicate articles: {$result['duplicate_articles']}</li>\n";
        echo "</ul>\n";
        
        // Show detailed results for each article
        if (!empty($result['articles'])) {
            echo "<h5>Article Details:</h5>\n";
            echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
            echo "<tr><th>Title</th><th>Status</th><th>Image Found</th><th>Image Downloaded</th><th>Image Path</th></tr>\n";
            
            foreach ($result['articles'] as $article) {
                $title = substr(htmlspecialchars($article['title'] ?? 'Unknown'), 0, 50);
                $status = htmlspecialchars($article['status'] ?? 'unknown');
                $imageDownloaded = $article['image_downloaded'] ?? false ? 'Yes' : 'No';
                $imagePath = htmlspecialchars($article['image_path'] ?? 'N/A');
                
                // Show if AI image was generated
                $aiGenerated = $article['ai_image_generated'] ?? false ? ' (AI)' : '';
                
                echo "<tr>\n";
                echo "<td>{$title}{$aiGenerated}</td>\n";
                echo "<td>{$status}</td>\n";
                echo "<td>" . (isset($article['image_found']) ? 'Yes' : 'No') . "</td>\n";
                echo "<td>{$imageDownloaded}</td>\n";
                echo "<td>{$imagePath}</td>\n";
                echo "</tr>\n";
                
                // Show image if available
                if (!empty($article['image_path'])) {
                    $imageUrl = $article['image_path'];
                    echo "<tr><td colspan='5' style='text-align:center;'>\n";
                    echo "<img src='{$imageUrl}' style='max-width:200px;max-height:150px;' alt='Article image'>\n";
                    echo "</td></tr>\n";
                }
            }
            
            echo "</table>\n";
        }
        
        echo "</div>\n";
        echo "<hr>\n";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-warning'>Error testing {$source['name']}: " . htmlspecialchars($e->getMessage()) . "</div>\n";
    }
}

/**
 * Test direct image extraction from RSS
 */
function testDirectRSSParsing() {
    echo "<h2>Direct RSS Parsing Test</h2>\n";
    
    $testUrls = [
        'http://feeds.bbci.co.uk/news/rss.xml',
        'http://rss.cnn.com/rss/edition.rss',
        'https://www.reuters.com/rssFeed/worldNews'
    ];
    
    foreach ($testUrls as $url) {
        echo "<h3>Testing: $url</h3>\n";
        
        try {
            $parser = new EnhancedRSSParser();
            $articles = $parser->parseRSS($url);
            
            echo "<p>Found " . count($articles) . " articles</p>\n";
            
            // Show first 3 articles with image info
            $count = 0;
            foreach ($articles as $article) {
                if ($count >= 3) break;
                
                echo "<div style='border:1px solid #ccc; margin:10px; padding:10px;'>\n";
                echo "<h4>" . htmlspecialchars($article['title']) . "</h4>\n";
                echo "<p><strong>Image URL:</strong> " . htmlspecialchars($article['image'] ?? 'None') . "</p>\n";
                
                if (!empty($article['image'])) {
                    echo "<img src='" . htmlspecialchars($article['image']) . "' style='max-width:150px;max-height:100px;' alt='Article image'><br>\n";
                }
                
                echo "<p><strong>Excerpt:</strong> " . htmlspecialchars(substr($article['excerpt'], 0, 200)) . "...</p>\n";
                echo "</div>\n";
                
                $count++;
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
        
        echo "<hr>\n";
    }
}

?>

<style>
.alert {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}
.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}
.feed-test {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 15px;
    margin: 10px 0;
    border-radius: 4px;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 10px 0;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
</style>
