<?php
/**
 * Manual RSS Test
 * Create sample RSS article to test import system
 */

require_once 'config/database.php';
require_once 'includes/auto_news_importer.php';

echo "<h2>🧪 Manual RSS Import Test</h2>";

try {
    // Create sample article data
    $sampleArticle = [
        'title' => 'Test Article: Breaking News from Manual RSS Test',
        'link' => 'https://example.com/news/test-article-' . time(),
        'description' => 'This is a test article created manually to verify the RSS import system is working properly. The content should be clean and readable without HTML artifacts.',
        'content' => '<p>This is a test article created manually to verify the RSS import system is working properly. The content should be clean and readable without HTML artifacts.</p><p>This article was created on ' . date('Y-m-d H:i:s') . ' to test the RSS import functionality.</p>',
        'published_date' => date('Y-m-d H:i:s'),
        'author' => 'Test System',
        'categories' => ['Technology', 'Testing']
    ];
    
    // Create sample source
    $sampleSource = [
        'id' => 999,
        'name' => 'Test RSS Source',
        'url' => 'https://example.com',
        'feed_url' => 'https://example.com/rss',
        'category_id' => 1 // Assuming category 1 exists
    ];
    
    echo "<h3>📝 Sample Article Data:</h3>";
    echo "<strong>Title:</strong> " . htmlspecialchars($sampleArticle['title']) . "<br>";
    echo "<strong>Content:</strong> " . htmlspecialchars(substr(strip_tags($sampleArticle['content']), 0, 200)) . "...<br>";
    echo "<strong>Source:</strong> " . htmlspecialchars($sampleSource['name']) . "<br>";
    
    // Initialize importer
    $importer = new AutoNewsImporter($conn);
    
    echo "<h3>🔄 Testing Import Process:</h3>";
    
    // Test import
    $result = $importer->importArticle($sampleArticle, $sampleSource);
    
    if ($result['status'] === 'imported') {
        echo "✅ <strong>Import Successful!</strong><br>";
        echo "📰 Article ID: {$result['news_id']}<br>";
        echo "📊 Sentiment: " . ($result['sentiment'] ?? 'N/A') . "<br>";
        echo "🖼️ Image downloaded: " . ($result['image_downloaded'] ? 'Yes' : 'No') . "<br>";
        
        if (isset($result['ai_image_generated'])) {
            echo "🤖 AI image generated: " . ($result['ai_image_generated'] ? 'Yes' : 'No') . "<br>";
        }
        
        // Verify article in database
        $verifyQuery = "SELECT id, title, content, excerpt, news_type, created_at FROM news WHERE id = ?";
        $stmt = mysqli_prepare($conn, $verifyQuery);
        mysqli_stmt_bind_param($stmt, 'i', $result['news_id']);
        mysqli_stmt_execute($stmt);
        $verifyResult = mysqli_stmt_get_result($stmt);
        
        if ($article = mysqli_fetch_assoc($verifyResult)) {
            echo "<h4>✅ Database Verification:</h4>";
            echo "<strong>Title in DB:</strong> " . htmlspecialchars($article['title']) . "<br>";
            echo "<strong>News Type:</strong> " . htmlspecialchars($article['news_type']) . "<br>";
            echo "<strong>Created:</strong> " . htmlspecialchars($article['created_at']) . "<br>";
            echo "<strong>Excerpt:</strong> " . htmlspecialchars(substr($article['excerpt'], 0, 150)) . "...<br>";
        }
        
    } else {
        echo "❌ <strong>Import Failed:</strong> " . htmlspecialchars($result['error'] ?? 'Unknown error') . "<br>";
    }
    
    // Test duplicate detection
    echo "<h3>🔄 Testing Duplicate Detection:</h3>";
    $duplicateResult = $importer->importArticle($sampleArticle, $sampleSource);
    
    if ($duplicateResult['status'] === 'duplicate') {
        echo "✅ <strong>Duplicate Detection Working!</strong><br>";
        echo "🔄 System correctly identified duplicate article<br>";
    } else {
        echo "⚠️ <strong>Duplicate Detection Issue:</strong><br>";
        echo "Expected duplicate detection but got: " . htmlspecialchars($duplicateResult['status']) . "<br>";
    }
    
    // Show recent RSS imports
    echo "<h3>📋 Recent RSS Imports:</h3>";
    $recentQuery = "SELECT id, title, news_type, created_at FROM news WHERE news_type = 'rss_import' ORDER BY created_at DESC LIMIT 5";
    $recentResult = mysqli_query($conn, $recentQuery);
    
    if (mysqli_num_rows($recentResult) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Created</th></tr>";
        
        while ($row = mysqli_fetch_assoc($recentResult)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
            echo "<td>{$row['news_type']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ No RSS imports found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
