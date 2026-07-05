<?php
require_once "config/database.php";

// Get search query
$query = isset($_GET["q"]) ? clean_input($_GET["q"]) : "";

if (empty($query)) {
    echo "<h1>Search Test</h1>";
    echo "<form method='GET'>";
    echo "<input type='text' name='q' placeholder='Search...' style='padding: 10px; width: 300px;'>";
    echo "<button type='submit' style='padding: 10px;'>Search</button>";
    echo "</form>";
    exit;
}

echo "<h1>Search Results for: " . htmlspecialchars($query) . "</h1>";

// Simple search
$search_term = "%$query%";
$search_query = "SELECT n.*, c.name as category_name 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' 
                 AND (n.title LIKE ? OR n.content LIKE ?)
                 ORDER BY n.published_at DESC 
                 LIMIT 10";

$stmt = mysqli_prepare($conn, $search_query);
mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$count = mysqli_num_rows($result);
echo "<p>Found $count results</p>";

if ($count > 0) {
    while ($news = mysqli_fetch_assoc($result)) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
        echo "<h3><a href='news.php?slug=" . $news["slug"] . "'>" . htmlspecialchars($news["title"]) . "</a></h3>";
        echo "<p><small>Category: " . htmlspecialchars($news["category_name"] ?? "None") . " | Views: " . number_format($news["views"] ?? 0) . "</small></p>";
        if ($news["image"]) {
            echo "<img src='" . htmlspecialchars($news["image"]) . "' style='max-width: 200px; height: auto;' alt=''>";
        }
        echo "<p>" . substr(strip_tags($news["content"]), 0, 200) . "...</p>";
        echo "</div>";
    }
} else {
    echo "<p>No results found.</p>";
}

echo "<p><a href='simple_search_test.php'>← Back to search</a></p>";
?>