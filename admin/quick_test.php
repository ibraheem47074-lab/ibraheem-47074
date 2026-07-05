<?php
require_once '../config/database.php';

echo "<h2>Quick Form Test</h2>";

// Test creating a simple article with default values
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    $title = "Test Article " . date('Y-m-d H:i:s');
    $content = "This is a test article to verify the form is working properly. It contains enough content to pass the minimum length validation.";
    $excerpt = "Test article excerpt";
    $category_id = 1; // Assuming category 1 exists
    $status = 'published';
    $author_id = 1; // Assuming user 1 exists
    $image_path = '';
    $video_path = '';
    $video_url = '';
    $news_type = 'pk_live';
    $source_url = '';
    $urgency = 'medium';
    $sentiment_score = 0.5;
    $sentiment_label = 'neutral';
    
    // Generate slug
    $slug = slugify($title);
    
    // Check if slug already exists
    $slug_check_query = "SELECT id FROM news WHERE slug = ?";
    $stmt = mysqli_prepare($conn, $slug_check_query);
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $slug_check = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($slug_check) == 0) {
        // Insert test article
        $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, published_at, sentiment_score, sentiment_label, news_type, source_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssissssss', 
            $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path,
            $category_id, $author_id, $status, $sentiment_score, $sentiment_label, $news_type, $source_url
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $insert_id = mysqli_insert_id($conn);
            echo "<p style='color: green;'>✓ Test article created successfully! ID: $insert_id</p>";
            echo "<p><a href='../news.php?slug=" . htmlspecialchars($slug) . "' target='_blank'>View Article</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test article: " . mysqli_stmt_error($stmt) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Test article with this title already exists</p>";
    }
}

echo "<hr>";
echo "<form method='post'>";
echo "<input type='submit' name='test_submit' value='Create Test Article' class='btn btn-primary'>";
echo "</form>";

echo "<hr><h3>Current Status:</h3>";

// Check recent articles
$query = "SELECT id, title, status, published_at FROM news ORDER BY id DESC LIMIT 3";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Published</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['published_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No articles found.</p>";
}
?>

<p><a href='add-news.php'>← Test the Actual Form</a></p>
