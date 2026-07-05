<?php
require_once '../config/database.php';

echo "<h1>All Articles with Video Path</h1>";

// Get all articles with video_path
$query = "SELECT id, title, video_path, video_url, image, status, created_at 
          FROM news 
          WHERE video_path IS NOT NULL AND video_path != '' 
          ORDER BY id DESC 
          LIMIT 10";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th>";
    echo "<th>Title</th>";
    echo "<th>Video Path</th>";
    echo "<th>Status</th>";
    echo "<th>Created</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_path'] ?? '') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No articles with video_path found.</p>";
}

echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
echo "<p><a href='../index.php'>→ View Index Page</a></p>";
?>
