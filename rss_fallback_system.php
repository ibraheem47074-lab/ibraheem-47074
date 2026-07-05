<?php
/**
 * RSS Fallback System
 * Create sample RSS articles when network is down
 */

require_once 'config/database.php';

echo "<h2>🔄 RSS Fallback System</h2>";

// Sample news articles for testing
$sample_articles = [
    [
        'title' => 'Breaking: Local Technology Summit Announced for Next Month',
        'content' => '<p>Local authorities have announced a major technology summit scheduled for next month, bringing together industry leaders and innovators from across the region.</p><p>The event aims to showcase latest technological advancements and foster collaboration between startups and established companies.</p>',
        'excerpt' => 'Local authorities have announced a major technology summit scheduled for next month...',
        'category' => 'Technology'
    ],
    [
        'title' => 'Sports: National Team Wins Championship in Thrilling Final',
        'content' => '<p>The national team secured a dramatic victory in the championship final, defeating their rivals in a nail-biting match that went into extra time.</p><p>Captain scored the winning goal in the final minutes, securing the team\'s first championship in five years.</p>',
        'excerpt' => 'The national team secured a dramatic victory in the championship final...',
        'category' => 'Sports'
    ],
    [
        'title' => 'Business: Local Startup Secures Major Investment Deal',
        'content' => '<p>A promising local startup has secured a significant investment deal from international investors, valuing the company at several million dollars.</p><p>The funding will be used to expand operations and hire additional staff as the company prepares for its next growth phase.</p>',
        'excerpt' => 'A promising local startup has secured a significant investment deal...',
        'category' => 'Business'
    ]
];

echo "<h3>📝 Creating Sample RSS Articles</h3>";

$created_count = 0;
foreach ($sample_articles as $article_data) {
    try {
        // Check if article already exists (avoid duplicates)
        $check_query = "SELECT id FROM news WHERE title LIKE ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $check_query);
        $title_search = "%" . substr($article_data['title'], 0, 30) . "%";
        mysqli_stmt_bind_param($stmt, 's', $title_search);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo "⚠️ Article already exists: " . htmlspecialchars(substr($article_data['title'], 0, 50)) . "...<br>";
            continue;
        }
        
        // Create article
        $title = $article_data['title'] . " - " . date('Y-m-d H:i:s');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $content = $article_data['content'];
        $excerpt = $article_data['excerpt'];
        $source_url = "https://fallback.example.com/news/" . time() . rand(1000, 9999);
        
        $insertQuery = "INSERT INTO news (title, slug, content, excerpt, image, image_type, category_id, 
                        author_id, status, sentiment_score, sentiment_label, published_at, 
                        source_url, news_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rss_import', NOW())";
        
        $stmt = mysqli_prepare($conn, $insertQuery);
        if ($stmt) {
            $image = '';
            $image_type = 'fallback';
            $category_id = 1;
            $author_id = 1;
            $status = 'published';
            $sentiment_score = 0;
            $sentiment_label = 'neutral';
            $published_at = date('Y-m-d H:i:s');
            
            mysqli_stmt_bind_param($stmt, 'sssssiidsssss', 
                $title, $slug, $content, $excerpt, $image, $image_type, 
                $category_id, $author_id, $status, $sentiment_score, $sentiment_label, 
                $published_at, $source_url
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $news_id = mysqli_insert_id($conn);
                echo "✅ Created: " . htmlspecialchars(substr($article_data['title'], 0, 60)) . "... (ID: {$news_id})<br>";
                $created_count++;
            }
            mysqli_stmt_close($stmt);
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

echo "<h3>📊 Results</h3>";
echo "<strong>Articles Created:</strong> {$created_count}<br>";

// Show recent RSS imports
echo "<h3>📋 Recent RSS Articles</h3>";
$recent_query = "SELECT id, title, image_type, created_at FROM news WHERE news_type = 'rss_import' ORDER BY created_at DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Created</th></tr>";
    
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 80)) . "...</td>";
        echo "<td>{$row['image_type']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No RSS articles found.</p>";
}

echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
echo "<h4>✅ Fallback System Status</h4>";
echo "<p>The RSS fallback system is working! This creates sample articles when network connectivity prevents fetching real RSS feeds.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Fix network connectivity issues (DNS settings)</li>";
echo "<li>Resume normal RSS imports once network is restored</li>";
echo "<li>Articles created by fallback system will be marked with 'fallback' image type</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><small>Fallback system completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
