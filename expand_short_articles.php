<?php
require_once 'config/database.php';

echo "=== Expanding Short Articles ===\n\n";

// Find articles with less than 300 words
$query = "SELECT id, title, content FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 50";
$result = mysqli_query($conn, $query);

$short_articles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $word_count = str_word_count(strip_tags($row['content']));
    if ($word_count < 300) {
        $short_articles[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'words' => $word_count,
            'needed' => 300 - $word_count
        ];
    }
}

if (count($short_articles) > 0) {
    echo "Found " . count($short_articles) . " articles under 300 words:\n\n";
    
    foreach ($short_articles as $article) {
        echo "ID: {$article['id']}\n";
        echo "Title: {$article['title']}\n";
        echo "Current words: {$article['words']}\n";
        echo "Need: {$article['needed']} more words\n";
        echo "---\n";
    }
    
    echo "\nTo expand these articles:\n";
    echo "1. Edit each article in the admin panel\n";
    echo "2. Add relevant details, quotes, or analysis\n";
    echo "3. Ensure content is original and valuable\n";
    echo "4. Target 300+ words per article\n";
    
} else {
    echo "✅ All articles are 300+ words!\n";
}

echo "\n=== Short Article Check Complete ===\n";
