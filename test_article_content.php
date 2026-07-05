<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "Testing RSS Article Content Display\n";
echo "===================================\n\n";

// Get a recent RSS imported article
$query = "SELECT id, title, content, excerpt, news_type, source_url, created_at 
          FROM news 
          WHERE news_type = 'rss_import' 
          ORDER BY created_at DESC 
          LIMIT 3";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($article = mysqli_fetch_assoc($result)) {
        echo "Article ID: {$article['id']}\n";
        echo "Title: {$article['title']}\n";
        echo "Created: {$article['created_at']}\n";
        echo "Source: {$article['source_url']}\n";
        echo "\n--- EXCERPT ---\n";
        echo $article['excerpt'] . "\n";
        echo "\n--- FULL CONTENT ---\n";
        echo $article['content'] . "\n";
        echo "\n" . str_repeat("=", 80) . "\n\n";
    }
} else {
    echo "No RSS imported articles found\n";
}

echo "Content display test completed!\n";
?>
