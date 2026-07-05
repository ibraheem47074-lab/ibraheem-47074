<?php
require_once 'config/database.php';

echo "<h2>Testing News Time Display and Ordering</h2>";

// Test the updated query
$test_query = "SELECT n.*, c.name as category_name,
              COALESCE(n.published_at, n.created_at) as real_post_time
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              WHERE n.status = 'published' 
              ORDER BY real_post_time DESC LIMIT 10";

$result = mysqli_query($conn, $test_query);

echo "<h3>Latest 10 News Articles (Ordered by Real Post Time)</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Created At</th><th>Published At</th><th>Real Post Time</th><th>Formatted Time</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "<td>" . ($row['published_at'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['real_post_time'] . "</td>";
    echo "<td><strong>" . format_date_realtime($row['real_post_time']) . "</strong></td>";
    echo "</tr>";
}

echo "</table>";

// Test time status calculation
echo "<h3>Time Display Examples</h3>";
$status_query = "SELECT n.title,
                 COALESCE(n.published_at, n.created_at) as real_post_time
                 FROM news n 
                 WHERE n.status = 'published' 
                 ORDER BY real_post_time DESC LIMIT 8";

$status_result = mysqli_query($conn, $status_query);

echo "<div style='max-width: 600px;'>";
while ($row = mysqli_fetch_assoc($status_result)) {
    echo "<div style='border: 1px solid #ccc; margin: 5px; padding: 10px; background: #f9f9f9;'>";
    echo "<strong>" . htmlspecialchars(substr($row['title'], 0, 60)) . "...</strong><br>";
    echo "<small style='color: #666;'>Raw Time: " . $row['real_post_time'] . "</small><br>";
    echo "<strong style='color: #0066cc; font-size: 16px;'>🕐 " . format_date_realtime($row['real_post_time']) . "</strong>";
    echo "</div>";
}
echo "</div>";

echo "<h3>Time Display Rules:</h3>";
echo "<ul>";
echo "<li><strong>Just now</strong> - Posts less than 1 minute old</li>";
echo "<li><strong>X minutes ago</strong> - Posts less than 1 hour old</li>";
echo "<li><strong>X hours ago</strong> - Posts less than 24 hours old</li>";
echo "<li><strong>Yesterday at X:XX AM/PM</strong> - Posts from yesterday</li>";
echo "<li><strong>X days ago</strong> - Posts 2-6 days old</li>";
echo "<li><strong>Month Day, Year at X:XX AM/PM</strong> - Posts older than 7 days</li>";
echo "</ul>";

echo "<h3>Test Complete!</h3>";
echo "<p><a href='index.php'>Back to Homepage</a> | <a href='news.php'>View News Page</a></p>";
?>
