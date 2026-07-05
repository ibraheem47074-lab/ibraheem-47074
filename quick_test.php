<?php
// Quick test to verify the fix
require_once 'config/database.php';

echo "<h2>PK Live News - Quick Test</h2>";

try {
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Test news table
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $count = mysqli_fetch_assoc($result)['count'];
    echo "<p><strong>Total news articles:</strong> $count</p>";
    
    // Test published news
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'");
    $published = mysqli_fetch_assoc($result)['count'];
    echo "<p><strong>Published articles:</strong> $published</p>";
    
    // Create sample news if none exist
    if ($published == 0) {
        echo "<p style='color: orange;'>Creating sample news...</p>";
        
        $sql = "INSERT INTO news (title, slug, content, excerpt, status, published_at, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        $title = 'Sample News Article - ' . date('Y-m-d H:i:s');
        $slug = 'sample-news-' . time();
        $content = '<p>This is a sample news article created for testing purposes.</p>';
        $excerpt = 'Sample news article for testing.';
        $status = 'published';
        
        mysqli_stmt_bind_param($stmt, 'sssss', $title, $slug, $content, $excerpt, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Sample news created</p>";
        }
    }
    
    // Test get_news_title function
    $test_news = ['title' => 'Test Title'];
    if (function_exists('get_news_title')) {
        $title = get_news_title($test_news);
        echo "<p style='color: green;'>✅ get_news_title() works: $title</p>";
    } else {
        echo "<p style='color: red;'>❌ get_news_title() function not found</p>";
    }
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Visit Homepage</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
