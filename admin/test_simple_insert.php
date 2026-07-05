<?php
session_start();
require_once '../config/database.php';

// Simulate logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

echo "<h2>Simple Insert Test</h2>";

// Test data
$title = "Test Article " . date('Y-m-d H:i:s');
$content = "This is test content for the article.";
$excerpt = "Test excerpt";
$category_id = 1;
$status = 'published';
$is_breaking = 0;
$published_at = date('Y-m-d H:i:s');
$sentiment_score = 0.5;
$sentiment_label = 'neutral';
$news_type = 'pk_live';
$source_url = '';
$image_path = '';
$video_url = '';
$video_path = '';

// Generate slug
$slug = slugify($title);

// Test the exact same query structure as in add-news.php
$query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, is_breaking, published_at, sentiment_score, sentiment_label, news_type, source_url) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    echo "<p style='color: green;'>✓ Query prepared successfully</p>";
    
    mysqli_stmt_bind_param($stmt, 'sssssssisississs', 
        $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path,
        $category_id, $_SESSION['user_id'], $status, $is_breaking, $published_at, 
        $sentiment_score, $sentiment_label, $news_type, $source_url
    );
    
    echo "<p style='color: green;'>✓ Parameters bound successfully</p>";
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✓ Article inserted successfully! ID: $insert_id</p>";
        echo "<p><a href='../news.php?slug=" . htmlspecialchars($slug) . "' target='_blank'>View Article</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Execute failed: " . mysqli_stmt_error($stmt) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Prepare failed: " . mysqli_error($conn) . "</p>";
}
?>
