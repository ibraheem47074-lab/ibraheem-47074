<?php
require_once '../config/database.php';

echo "<h2>Form Fix Test</h2>";
echo "<p>This page helps test if the form fixes are working correctly.</p>";

// Check if any recent articles have been created
$query = "SELECT id, title, image, video_url, video_path, status, published_at FROM news ORDER BY id DESC LIMIT 5";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<h3>Recent Articles:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Image</th><th>Video URL</th><th>Video Path</th><th>Status</th><th>Published</th><th>Actions</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $has_media = (!empty($row['image']) || !empty($row['video_url']) || !empty($row['video_path']));
        echo "<tr style='" . ($has_media ? 'background-color: #e8f5e8;' : '') . "'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
        echo "<td>" . (!empty($row['image']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_url']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_path']) ? '✓' : '-') . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['published_at'] . "</td>";
        
        // Get slug for view link
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
    echo "<p>No articles found in database.</p>";
}

echo "<hr><h3>Test the Form:</h3>";
echo "<ol>";
echo "<li><strong>Go to:</strong> <a href='add-news.php' target='_blank'>Add News Form</a></li>";
echo "<li><strong>Fill in:</strong> Title, content, and select a category</li>";
echo "<li><strong>Upload:</strong> An image or video (or both)</li>";
echo "<li><strong>Notice:</strong> Media type should automatically change when you select files</li>";
echo "<li><strong>Click:</strong> 'Save News Article' button</li>";
echo "<li><strong>Check:</strong> Come back to this page to see if your article was saved</li>";
echo "</ol>";

echo "<hr><h3>Debugging Tips:</h3>";
echo "<ul>";
echo "<li>Open browser console (F12) to see debug messages</li>";
echo "<li>Check for 'Auto-updated media type to:' messages</li>";
echo "<li>Look for 'Form validation passed - submitting form' message</li>";
echo "<li>If validation fails, read the error message carefully</li>";
echo "</ul>";

echo "<hr><h3>Upload Directory Status:</h3>";
$image_dir = '../uploads/news/images/';
$video_dir = '../uploads/news/videos/';

echo "<p><strong>Image Directory:</strong> " . (is_dir($image_dir) ? '✓ Exists' : '✗ Missing') . " | " . (is_writable($image_dir) ? '✓ Writable' : '✗ Not Writable') . "</p>";
echo "<p><strong>Video Directory:</strong> " . (is_dir($video_dir) ? '✓ Exists' : '✗ Missing') . " | " . (is_writable($video_dir) ? '✓ Writable' : '✗ Not Writable') . "</p>";

// Count files
if (is_dir($image_dir)) {
    $image_files = array_diff(scandir($image_dir), ['.', '..']);
    echo "<p><strong>Image Files:</strong> " . count($image_files) . "</p>";
}

if (is_dir($video_dir)) {
    $video_files = array_diff(scandir($video_dir), ['.', '..']);
    echo "<p><strong>Video Files:</strong> " . count($video_files) . "</p>";
}
?>

<p><a href="manage-news.php">← Manage News</a></p>
