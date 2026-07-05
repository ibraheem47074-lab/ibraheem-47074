<?php
require_once '../config/database.php';

echo "<h2>Test Video Publishing</h2>";

// Create a test article with video
$title = "Test Video Article " . date('Y-m-d H:i:s');
$content = "This is a test article with video content to verify video display on index page.";
$excerpt = "Test video article excerpt";
$category_id = 1; // Assuming category 1 exists
$status = 'published';
$author_id = 1; // Assuming user 1 exists
$image_path = 'uploads/news/images/test_image.jpg';
$video_path = 'uploads/news/videos/vid_69ce9834b6ed0_1775147060.mp4'; // Use existing uploaded video
$video_url = '';
$news_type = 'pk_live';
$source_url = '';
$urgency = 'medium';

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
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, published_at, news_type, source_url, urgency) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssssssisssss', 
        $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path,
        $category_id, $author_id, $status, $news_type, $source_url, $urgency
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✓ Test video article created successfully! ID: $insert_id</p>";
        echo "<p><a href='../news.php?slug=" . htmlspecialchars($slug) . "' target='_blank'>View Article</a></p>";
        echo "<p><a href='../index.php' target='_blank'>Check Index Page</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create test article: " . mysqli_stmt_error($stmt) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Test article with this title already exists</p>";
}

// Check existing video articles
echo "<hr><h3>Existing Published Video Articles</h3>";
$query = "SELECT id, title, image, video_url, video_path, published_at FROM news 
          WHERE status = 'published' AND ((video_url IS NOT NULL AND video_url != '') OR (video_path IS NOT NULL AND video_path != '')) 
          ORDER BY id DESC LIMIT 5";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Image</th><th>Video URL</th><th>Video Path</th><th>Published</th><th>Actions</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . (!empty($row['image']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_url']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_path']) ? '✓' : '-') . "</td>";
        echo "<td>" . $row['published_at'] . "</td>";
        
        // Get slug
        $slug_query = "SELECT slug FROM news WHERE id = ?";
        $stmt = mysqli_prepare($conn, $slug_query);
        mysqli_stmt_bind_param($stmt, 'i', $row['id']);
        mysqli_stmt_execute($stmt);
        $slug_result = mysqli_stmt_get_result($stmt);
        $slug_row = mysqli_fetch_assoc($slug_result);
        $slug = $slug_row ? $slug_row['slug'] : '';
        
        echo "<td><a href='../news.php?slug=" . htmlspecialchars($slug) . "' target='_blank'>View</a> | <a href='../index.php' target='_blank'>Index</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No published video articles found.</p>";
}

echo "<hr><h3>Index Page Query Test</h3>";
// Test the same query used by index.php
$latest_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                CASE 
                    WHEN n.image IS NOT NULL AND n.image != '' THEN 3
                    WHEN n.video_url IS NOT NULL AND n.video_url != '' THEN 2
                    WHEN n.video_path IS NOT NULL AND n.video_path != '' THEN 2
                    ELSE 1
                END as media_priority
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' 
                ORDER BY media_priority DESC, n.published_at DESC LIMIT 10";
$latest_result = mysqli_query($conn, $latest_query);

if ($latest_result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Media Priority</th><th>Image</th><th>Video URL</th><th>Video Path</th></tr>";
    
    while ($row = mysqli_fetch_assoc($latest_result)) {
        $has_video = (!empty($row['video_url']) || !empty($row['video_path']));
        echo "<tr style='" . ($has_video ? 'background-color: #e8f5e8;' : '') . "'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 40)) . "...</td>";
        echo "<td>" . $row['media_priority'] . "</td>";
        echo "<td>" . (!empty($row['image']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_url']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_path']) ? '✓' : '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Query failed: " . mysqli_error($conn) . "</p>";
}
?>

<p><a href="add-news.php">← Back to Add News</a></p>
