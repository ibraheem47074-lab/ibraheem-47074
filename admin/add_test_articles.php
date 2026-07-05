<?php
require_once '../config/database.php';

echo "<h2>Add Test Articles</h2>";

// Create some test articles with different statuses
$test_articles = [
    [
        'title' => 'Breaking News: Local Elections Announced',
        'content' => 'The local election committee has announced the dates for the upcoming municipal elections...',
        'status' => 'published',
        'category_id' => 1,
        'author_id' => 2,
        'views' => 150
    ],
    [
        'title' => 'New Technology Hub Opens Downtown',
        'content' => 'A new technology hub has opened in the downtown area, offering various services...',
        'status' => 'published',
        'category_id' => 2,
        'author_id' => 3,
        'views' => 89
    ],
    [
        'title' => 'Sports Update: Local Team Wins Championship',
        'content' => 'The local sports team has won the championship in an exciting match...',
        'status' => 'pending',
        'category_id' => 3,
        'author_id' => 4,
        'views' => 45
    ],
    [
        'title' => 'Community Event: Food Festival This Weekend',
        'content' => 'The annual food festival will take place this weekend with various vendors...',
        'status' => 'draft',
        'category_id' => 1,
        'author_id' => 5,
        'views' => 23
    ]
];

// Insert test articles
foreach ($test_articles as $article) {
    $slug = strtolower(str_replace(' ', '-', $article['title'])) . '-' . time();
    
    $insert_query = "INSERT INTO news (title, slug, content, status, category_id, author_id, views, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, 'ssisiii', 
        $article['title'], 
        $slug, 
        $article['content'], 
        $article['status'], 
        $article['category_id'], 
        $article['author_id'], 
        $article['views']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($article['title']) . " (" . ucfirst($article['status']) . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($article['title']) . "</p>";
    }
}

echo "<br><a href='editor-dashboard-enhanced.php'>← Back to Editor Dashboard</a>";
echo "<br><a href='manage-news.php'>→ Go to Manage News</a>";
?>
