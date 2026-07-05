<?php
require_once '../config/database.php';

echo "<h1>Simple Database Test</h1>";

// Test basic connection
echo "<h2>Database Connection:</h2>";
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Test simple query
echo "<h2>Test Query:</h2>";
$test_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news");
if ($test_result) {
    $row = mysqli_fetch_assoc($test_result);
    echo "<p>Total articles in database: {$row['total']}</p>";
} else {
    echo "<p style='color: red;'>✗ Query failed: " . mysqli_error($conn) . "</p>";
}

// Test video_path query
echo "<h2>Test Video Path Query:</h2>";
$video_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE video_path IS NOT NULL AND video_path != ''");
if ($video_result) {
    $video_row = mysqli_fetch_assoc($video_result);
    echo "<p>Articles with video_path: {$video_row['total']}</p>";
} else {
    echo "<p style='color: red;'>✗ Video query failed: " . mysqli_error($conn) . "</p>";
}

// Get recent 5 articles to see actual data
echo "<h2>Recent 5 Articles:</h2>";
$recent_query = "SELECT id, title, video_path, video_url, image, status FROM news ORDER BY id DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

if ($recent_result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Title</th><th>Video Path</th><th>Video URL</th><th>Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_path'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_url'] ?? 'NULL') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Recent query failed: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
?>
