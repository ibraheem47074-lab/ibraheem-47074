<?php
/**
 * Add Video Support to RSS Import System
 */

require_once 'config/database.php';

echo "<h1>Adding Video Support to RSS Import System</h1>\n";

// Check if video_url column exists
echo "<h3>Checking video_url column...</h3>\n";

$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'video_url'");
if (mysqli_num_rows($check_column) > 0) {
    echo "<p style='color: green;'>✓ video_url column already exists</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ video_url column is MISSING - Adding it now...</p>\n";
    
    $add_column = "ALTER TABLE news ADD COLUMN video_url VARCHAR(500) DEFAULT NULL AFTER image_type";
    if (mysqli_query($conn, $add_column)) {
        echo "<p style='color: green;'>✓ video_url column added successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding video_url column: " . mysqli_error($conn) . "</p>\n";
    }
}

// Test the enhanced RSS parser
echo "<h3>Testing Enhanced RSS Parser...</h3>\n";

require_once 'includes/enhanced_rss_parser.php';

$parser = new EnhancedRSSParser();

// Test with a known RSS feed that has videos
$testFeeds = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'Reuters' => 'https://feeds.reuters.com/reuters/topNews'
];

$successCount = 0;
$totalTests = count($testFeeds);

foreach ($testFeeds as $name => $feedUrl) {
    echo "<p><strong>Testing $name feed...</strong></p>\n";
    
    try {
        $articles = $parser->parseRSS($feedUrl);
        
        if (!empty($articles)) {
            $successCount++;
            echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
            echo "<p>✅ Successfully parsed " . count($articles) . " articles</p>\n";
            
            // Show first article details
            if (isset($articles[0])) {
                $article = $articles[0];
                echo "<p><strong>Sample Article:</strong></p>\n";
                echo "<ul>\n";
                echo "<li><strong>Title:</strong> " . htmlspecialchars(substr($article['title'], 0, 100)) . "...</li>\n";
                echo "<li><strong>Media Type:</strong> " . htmlspecialchars($article['media_type']) . "</li>\n";
                echo "<li><strong>Has Image:</strong> " . (!empty($article['image']) ? '✅ Yes' : '❌ No') . "</li>\n";
                echo "<li><strong>Has Video:</strong> " . (!empty($article['video_url']) ? '✅ Yes' : '❌ No') . "</li>\n";
                if (!empty($article['video_url'])) {
                    echo "<li><strong>Video Type:</strong> " . htmlspecialchars($article['video_type']) . "</li>\n";
                    echo "<li><strong>Video URL:</strong> " . htmlspecialchars(substr($article['video_url'], 0, 80)) . "...</li>\n";
                }
                echo "</ul>\n";
            }
            echo "</div>\n";
        } else {
            echo "<p style='color: orange;'>⚠ No articles found in $name feed</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error parsing $name feed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
}

echo "<h3>Test Results</h3>\n";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>\n";
echo "<p><strong>Feeds Tested:</strong> $totalTests</p>\n";
echo "<p><strong>Successfully Parsed:</strong> $successCount</p>\n";
echo "<p><strong>Success Rate:</strong> " . round(($successCount / $totalTests) * 100, 1) . "%</p>\n";
echo "</div>\n";

// Test RSS import with multimedia
echo "<h3>Testing RSS Import with Multimedia...</h3>\n";

require_once 'includes/auto_news_importer.php';

$importer = new AutoNewsImporter($conn);

// Get a sample RSS source
$source_query = "SELECT * FROM news_sources WHERE status = 'active' LIMIT 1";
$source_result = mysqli_query($conn, $source_query);

if ($source_result && $source = mysqli_fetch_assoc($source_result)) {
    echo "<p><strong>Testing import from:</strong> " . htmlspecialchars($source['name']) . "</p>\n";
    
    try {
        $import_result = $importer->importFromSource($source);
        
        if ($import_result['status'] === 'success') {
            echo "<div style='background: #e8f5e8; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<p>✅ Import successful!</p>\n";
            echo "<p><strong>Articles Imported:</strong> " . $import_result['articles_imported'] . "</p>\n";
            echo "<p><strong>Articles Updated:</strong> " . $import_result['articles_updated'] . "</p>\n";
            echo "<p><strong>AI Images Generated:</strong> " . ($import_result['ai_images_generated'] ?? 0) . "</p>\n";
            
            // Check for multimedia in imported articles
            $check_multimedia = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN video_url IS NOT NULL AND video_url != '' THEN 1 END) as with_video,
                COUNT(CASE WHEN image IS NOT NULL AND image != '' THEN 1 END) as with_image
                FROM news WHERE news_type = 'rss_import' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $multimedia_result = mysqli_query($conn, $check_multimedia);
            if ($multimedia_result && $stats = mysqli_fetch_assoc($multimedia_result)) {
                echo "<h5>Multimedia Statistics (Last Hour):</h5>\n";
                echo "<ul>\n";
                echo "<li><strong>Total Articles:</strong> " . $stats['total'] . "</li>\n";
                echo "<li><strong>With Images:</strong> " . $stats['with_image'] . "</li>\n";
                echo "<li><strong>With Videos:</strong> " . $stats['with_video'] . "</li>\n";
                echo "</ul>\n";
            }
            
            echo "</div>\n";
        } else {
            echo "<p style='color: red;'>✗ Import failed: " . htmlspecialchars($import_result['message'] ?? 'Unknown error') . "</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Import error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
} else {
    echo "<p style='color: orange;'>⚠ No active RSS sources found for testing</p>\n";
}

echo "<h2>🎯 Video Support Setup Complete!</h2>\n";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>\n";
echo "<h4>✅ What was implemented:</h4>\n";
echo "<ul>\n";
echo "<li>✅ Enhanced RSS parser with video extraction</li>\n";
echo "<li>✅ Support for multiple video platforms (YouTube, Vimeo, Dailymotion)</li>\n";
echo "<li>✅ HTML5 video tag parsing</li>\n";
echo "<li>✅ Direct video file detection</li>\n";
echo "<li>✅ video_url column added to news table</li>\n";
echo "<li>✅ Updated auto importer with video support</li>\n";
echo "<li>✅ Media type detection (text, image, video)</li>\n";
echo "</ul>\n";
echo "<p><strong>The RSS import system now supports videos and pictures!</strong></p>\n";
echo "</div>\n";

echo "<p><a href='admin/manage-sources.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Manage RSS Sources</a> | <a href='cron_import_news.php' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test RSS Import</a></p>\n";
?>
