<?php
require_once '../config/database.php';

echo "<h2>Video News Articles Check</h2>";

$query = "SELECT id, title, image, video_url, video_path, status FROM news WHERE (video_url IS NOT NULL AND video_url != '') OR (video_path IS NOT NULL AND video_path != '') ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Image</th><th>Video URL</th><th>Video Path</th><th>View</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . ($row['image'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($row['video_url'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($row['video_path'] ? 'Yes' : 'No') . "</td>";
        echo "<td><a href='../news.php?slug=" . get_slug_by_id($row['id']) . "' target='_blank'>View</a></td>";
        echo "</tr>";
        
        echo "<tr><td colspan='7' style='background-color: #f5f5f5;'>";
        echo "<strong>Details:</strong><br>";
        echo "Image: " . htmlspecialchars($row['image'] ?? 'NULL') . "<br>";
        echo "Video URL: " . htmlspecialchars($row['video_url'] ?? 'NULL') . "<br>";
        echo "Video Path: " . htmlspecialchars($row['video_path'] ?? 'NULL') . "<br>";
        echo "</td></tr>";
    }
    echo "</table>";
    
    echo "<p>Total video articles: " . mysqli_num_rows($result) . "</p>";
} else {
    echo "Query failed: " . mysqli_error($conn);
}

// Helper function to get slug by ID
function get_slug_by_id($id) {
    global $conn;
    $query = "SELECT slug FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['slug'] : '';
}

echo "<hr><h3>Upload Directory Check</h3>";
$video_dir = '../uploads/news/videos/';
if (is_dir($video_dir)) {
    echo "Video directory exists: Yes<br>";
    echo "Video directory writable: " . (is_writable($video_dir) ? 'Yes' : 'No') . "<br>";
    
    $files = scandir($video_dir);
    $video_files = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && !is_dir($video_dir . $file);
    });
    echo "Video files in directory: " . count($video_files) . "<br>";
    
    if (!empty($video_files)) {
        echo "<ul>";
        foreach (array_slice($video_files, 0, 10) as $file) {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "Video directory exists: No<br>";
}
?>

<p><a href="add-news.php">← Back to Add News</a></p>
