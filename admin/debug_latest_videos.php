<?php
require_once '../config/database.php';

echo "<h1>Debug Latest Videos</h1>";

// Simple query to get latest articles with video_path
echo "<h2>Latest 5 Articles (Simple Query):</h2>";
$simple_query = "SELECT id, title, video_path, status, created_at FROM news WHERE video_path IS NOT NULL ORDER BY id DESC LIMIT 5";
$simple_result = mysqli_query($conn, $simple_query);

if ($simple_result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Title</th><th>Video Path</th><th>Status</th><th>Created</th></tr>";
    
    while ($row = mysqli_fetch_assoc($simple_result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['video_path']) . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
        
        // Also check if file exists
        if (!empty($row['video_path'])) {
            $full_path = '../' . $row['video_path'];
            echo "<tr><td colspan='4'><small>File exists: " . (file_exists($full_path) ? 'YES' : 'NO') . " - Path: " . htmlspecialchars($full_path) . "</small></td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No video articles found. Error: " . mysqli_error($conn) . "</p>";
}

echo "<h2>Published Status Check:</h2>";
$published_query = "SELECT COUNT(*) as published, COUNT(*) as total FROM news WHERE video_path IS NOT NULL";
$published_result = mysqli_query($conn, $published_query);
if ($published_result) {
    $counts = mysqli_fetch_assoc($published_result);
    echo "<p>Published video articles: {$counts['published']} / {$counts['total']}</p>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>1. Check if your test articles have 'published' status</li>";
echo "<li>2. Try refreshing the index page (Ctrl+F5)</li>";
echo "<li>3. Clear browser cache if needed</li>";
echo "<li>4. Check the actual index page to see if videos appear</li>";
echo "</ul>";

echo "<p><a href='../index.php'>→ View Index Page</a></p>";
echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
?>
