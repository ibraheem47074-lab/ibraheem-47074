<?php
require_once 'config/database.php';

// Get search query
$query = isset($_GET['q']) ? clean_input($_GET['q']) : '';

if (empty($query)) {
    echo '<!DOCTYPE html>
<html>
<head><title>Search Test</title></head>
<body>
    <h1>PK Live News Search Test</h1>
    <form method="GET">
        <input type="text" name="q" placeholder="Search..." style="padding: 10px; width: 300px;">
        <button type="submit" style="padding: 10px;">Search</button>
    </form>
</body>
</html>';
    exit;
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>Search Results for: ' . htmlspecialchars($query) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .result h3 { margin-top: 0; }
        .result a { text-decoration: none; color: #007bff; }
        .result a:hover { text-decoration: underline; }
        .meta { color: #666; font-size: 14px; }
        img { max-width: 200px; height: auto; margin-right: 15px; float: left; }
    </style>
</head>
<body>
    <h1>Search Results for: "' . htmlspecialchars($query) . '"</h1>
    <p><a href="minimal_search_test.php">← Back to search</a></p>';

// Execute search
$search_term = "%$query%";
$search_query = "SELECT n.*, c.name as category_name 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' 
                 AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)
                 ORDER BY n.published_at DESC 
                 LIMIT 10";

$stmt = mysqli_prepare($conn, $search_query);
mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$count = mysqli_num_rows($result);
echo "<p><strong>Found $count results</strong></p>";

if ($count > 0) {
    while ($news = mysqli_fetch_assoc($result)) {
        echo '<div class="result" style="clear: both;">';
        
        // Display image if available
        if (!empty($news['image']) && file_exists($news['image'])) {
            echo '<img src="' . htmlspecialchars($news['image']) . '" alt="' . htmlspecialchars($news['title']) . '">';
        }
        
        echo '<div style="overflow: hidden;">';
        echo '<h3><a href="news.php?slug=' . htmlspecialchars($news['slug']) . '">' . htmlspecialchars($news['title']) . '</a></h3>';
        
        echo '<div class="meta">';
        echo 'Category: ' . htmlspecialchars($news['category_name'] ?? 'None') . ' | ';
        echo 'Views: ' . number_format($news['views'] ?? 0) . ' | ';
        echo 'Date: ' . format_date($news['published_at']);
        echo '</div>';
        
        // Show excerpt or content preview
        $excerpt = !empty($news['excerpt']) ? $news['excerpt'] : substr(strip_tags($news['content']), 0, 300);
        echo '<p>' . htmlspecialchars($excerpt) . '...</p>';
        
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p>No results found for your search.</p>';
}

echo '</body></html>';
?>
