<?php
/**
 * Quick Image Import Test
 * Simple test to verify real image import functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Quick Image Import Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .test-result { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .image-preview { max-width: 200px; max-height: 150px; border: 1px solid #ccc; margin: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🖼️ Quick Image Import Test</h1>
    
    <div class='info'>
        <h3>📋 Test Overview</h3>
        <p>This test will verify that real images are being imported from RSS feeds.</p>
    </div>";

try {
    // Initialize database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Create importer instance
    $importer = new AutoNewsImporter($conn);
    
    // Configure for testing
    $importer->setMaxArticlesPerFeed(2); // Test with 2 articles per feed
    $importer->setDownloadImages(true);
    
    echo "<div class='success'>✅ Importer configured with image download enabled</div>";
    
    // Test with a sample RSS feed
    echo "<div class='test-result'>";
    echo "<h2>📡 Testing RSS Feed Image Import</h2>";
    
    // Test with BBC RSS feed
    $testFeed = [
        'name' => 'BBC News World',
        'url' => 'http://feeds.bbci.co.uk/news/world/rss.xml',
        'category_id' => 1
    ];
    
    echo "<h3>Testing: {$testFeed['name']}</h3>";
    echo "<p><strong>Feed URL:</strong> " . htmlspecialchars($testFeed['url']) . "</p>";
    
    try {
        $result = $importer->importFromSource($testFeed, 2);
        
        echo "<h4>Results:</h4>";
        echo "<table>";
        echo "<tr><th>Article</th><th>Title</th><th>Status</th><th>Image Downloaded</th><th>Image Path</th><th>Preview</th></tr>";
        
        $importedCount = 0;
        $imageCount = 0;
        
        foreach ($result['articles'] as $index => $article) {
            $title = substr(htmlspecialchars($article['title'] ?? 'Unknown'), 0, 60);
            $status = htmlspecialchars($article['status'] ?? 'unknown');
            $imageDownloaded = ($article['image_downloaded'] ?? false) ? '✅ Yes' : '❌ No';
            $imagePath = htmlspecialchars($article['image_path'] ?? 'N/A');
            
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>{$title}...</td>";
            echo "<td>{$status}</td>";
            echo "<td>{$imageDownloaded}</td>";
            echo "<td><small>{$imagePath}</small></td>";
            echo "<td>";
            
            // Show image preview if available
            if (!empty($article['image_path'])) {
                $imageUrl = $article['image_path'];
                if (file_exists(__DIR__ . '/' . $imageUrl)) {
                    echo "<img src='{$imageUrl}' class='image-preview' alt='Article image'>";
                } else {
                    echo "<span style='color: red;'>File not found</span>";
                }
            }
            
            echo "</td>";
            echo "</tr>";
            
            if ($article['status'] === 'imported') {
                $importedCount++;
            }
            if ($article['image_downloaded'] ?? false) {
                $imageCount++;
            }
        }
        
        echo "</table>";
        
        echo "<h4>📊 Summary:</h4>";
        echo "<ul>";
        echo "<li>Total articles found: {$result['total_articles']}</li>";
        echo "<li>Articles imported: {$importedCount}</li>";
        echo "<li>Images downloaded: {$imageCount}</li>";
        echo "<li>Success rate: " . round(($imageCount / max($importedCount, 1)) * 100, 1) . "%</li>";
        echo "</ul>";
        
        if ($imageCount > 0) {
            echo "<div class='success'>✅ Real images are being imported successfully!</div>";
        } else {
            echo "<div class='error'>❌ No images were downloaded. Check image extraction settings.</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error testing feed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "</div>";
    
    // Test database check
    echo "<div class='test-result'>";
    echo "<h2>🗄️ Database Image Check</h2>";
    
    // Check for recent news with images
    $checkQuery = "SELECT id, title, image, image_type, created_at FROM news 
                   WHERE image IS NOT NULL AND image != '' 
                   ORDER BY created_at DESC LIMIT 5";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        echo "<h4>Recent articles with images:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Image Type</th><th>Created</th><th>Preview</th></tr>";
        
        while ($row = mysqli_fetch_assoc($checkResult)) {
            $title = substr(htmlspecialchars($row['title']), 0, 50);
            $imageType = htmlspecialchars($row['image_type']);
            $createdAt = htmlspecialchars($row['created_at']);
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$title}...</td>";
            echo "<td>{$imageType}</td>";
            echo "<td>{$createdAt}</td>";
            echo "<td>";
            
            if (!empty($row['image']) && file_exists(__DIR__ . '/' . $row['image'])) {
                echo "<img src='{$row['image']}' class='image-preview' alt='Article image'>";
            } else {
                echo "<span style='color: red;'>Missing</span>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No articles with images found in database.</p>";
    }
    
    echo "</div>";
    
    // Upload directory check
    echo "<div class='test-result'>";
    echo "<h2>📁 Upload Directory Check</h2>";
    
    $uploadDir = __DIR__ . '/uploads/news';
    if (is_dir($uploadDir)) {
        $files = glob($uploadDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $fileCount = count($files);
        
        echo "<p>Upload directory exists: <strong>✅ Yes</strong></p>";
        echo "<p>Images in uploads/news: <strong>{$fileCount}</strong></p>";
        
        if ($fileCount > 0) {
            echo "<h4>Recent uploads:</h4>";
            echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
            
            $recentFiles = array_slice($files, -5);
            foreach ($recentFiles as $file) {
                $filename = basename($file);
                $relativePath = 'uploads/news/' . $filename;
                echo "<div style='text-align: center;'>";
                echo "<img src='{$relativePath}' class='image-preview' alt='{$filename}'>";
                echo "<br><small>{$filename}</small>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        
        if (is_writable($uploadDir)) {
            echo "<p>Directory writable: <strong>✅ Yes</strong></p>";
        } else {
            echo "<p>Directory writable: <strong>❌ No</strong></p>";
        }
    } else {
        echo "<p>Upload directory exists: <strong>❌ No</strong></p>";
        echo "<p>Creating upload directory...</p>";
        
        if (mkdir($uploadDir, 0755, true)) {
            echo "<p class='success'>✅ Upload directory created successfully</p>";
        } else {
            echo "<p class='error'>❌ Failed to create upload directory</p>";
        }
    }
    
    echo "</div>";
    
    // Recommendations
    echo "<div class='test-result'>";
    echo "<h2>🚀 Recommendations</h2>";
    echo "<ol>";
    echo "<li><strong>Enable image downloads:</strong> Make sure <code>\$importer->setDownloadImages(true);</code> is called</li>";
    echo "<li><strong>Check RSS feeds:</strong> Verify your RSS feeds contain image URLs in media:content or enclosure tags</li>";
    echo "<li><strong>Monitor uploads:</strong> Check the uploads/news directory for downloaded images</li>";
    echo "<li><strong>Database verification:</strong> Check the news table for image_path and image_type columns</li>";
    echo "<li><strong>AI fallback:</strong> Enable AI image generation as backup: <code>\$importer->setGenerateAIImages(true);</code></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body>
</html>";
?>
