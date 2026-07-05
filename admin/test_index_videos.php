<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

echo "<h1>Test Index Video Display</h1>";

// Simulate the same query as index.php
$latest_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                CASE 
                    WHEN n.image IS NOT NULL AND n.image != '' THEN 3
                    WHEN n.video_url IS NOT NULL AND n.video_url != '' THEN 2
                    WHEN n.video_path IS NOT NULL AND n.video_path != '' THEN 2
                    ELSE 1
                END as media_priority,
                CASE 
                    WHEN n.source_url IS NOT NULL AND n.source_url != '' THEN 'external'
                    ELSE 'internal'
                END as news_type,
                CASE 
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
                    ELSE 'older'
                END as time_status,
                n.news_type as article_type,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                CASE 
                    WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                    ELSE 'PK Live News'
                END as source_name,
                COALESCE(n.published_at, n.created_at) as real_post_time
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' AND (n.published_at <= NOW() OR n.published_at IS NULL) 
                ORDER BY real_post_time DESC, media_priority DESC LIMIT 5";

$per_page = 5;
$latest_stmt = mysqli_prepare($conn, $latest_query);
mysqli_stmt_bind_param($latest_stmt, 'i', $per_page);
mysqli_stmt_execute($latest_stmt);
$latest_result = mysqli_stmt_get_result($latest_stmt);

echo "<h2>Latest 5 Articles with Media Info:</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th>";
echo "<th>Title</th>";
echo "<th>Media Priority</th>";
echo "<th>Video URL</th>";
echo "<th>Video Path</th>";
echo "<th>Image</th>";
echo "<th>Should Show Video</th>";
echo "</tr>";

if ($latest_result && mysqli_num_rows($latest_result) > 0) {
    while ($row = mysqli_fetch_assoc($latest_result)) {
        $should_show_video = ($row['media_priority'] == 2);
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
        echo "<td>{$row['media_priority']}</td>";
        echo "<td>" . htmlspecialchars($row['video_url'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['video_path'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['image'] ?? 'NULL') . "</td>";
        echo "<td style='color: " . ($should_show_video ? 'green' : 'red') . ";'>" . ($should_show_video ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' style='color: red;'>No articles found</td></tr>";
}

echo "</table>";

echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
echo "<p><a href='../index.php'>→ View Index Page</a></p>";
?>
