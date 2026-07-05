<?php
require_once '../config/database.php';

echo "<h1>Check Recent Video Articles</h1>";

// Get recent articles with video_path
$query = "SELECT id, title, video_path, video_url, image, status, created_at 
          FROM news 
          WHERE (video_path IS NOT NULL OR video_url IS NOT NULL) 
          ORDER BY id DESC 
          LIMIT 5";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th>";
    echo "<th>Title</th>";
    echo "<th>Video Path</th>";
    echo "<th>Video URL</th>";
    echo "<th>Image</th>";
    echo "<th>Status</th>";
    echo "<th>Created</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_path'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_url'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['image'] ?? 'NULL') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No articles with videos found.</p>";
}

// Check specific recent article
echo "<h2>Check Last Inserted Article</h2>";
$last_query = "SELECT * FROM news ORDER BY id DESC LIMIT 1";
$last_result = mysqli_query($conn, $last_query);

if ($last_row = mysqli_fetch_assoc($last_result)) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Field</th>";
    echo "<th>Value</th>";
    echo "</tr>";
    
    foreach ($last_row as $key => $value) {
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No articles found in database.</p>";
}

echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
?>
