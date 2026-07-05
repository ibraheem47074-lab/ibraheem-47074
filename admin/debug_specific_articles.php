<?php
require_once '../config/database.php';

echo "<h1>Debug Specific Video Articles</h1>";

// Get the specific articles mentioned
$articles = ['2nd video', 'first video'];

foreach ($articles as $article_title) {
    echo "<h2>Checking: " . htmlspecialchars($article_title) . "</h2>";
    
    $query = "SELECT n.*, c.name as category_name, u.name as author_name,
                    CASE 
                        WHEN n.image IS NOT NULL AND n.image != '' THEN 3
                        WHEN n.video_url IS NOT NULL AND n.video_url != '' THEN 2
                        WHEN n.video_path IS NOT NULL AND n.video_path != '' THEN 2
                        ELSE 1
                    END as media_priority,
                    n.video_path,
                    n.video_url,
                    n.image,
                    n.status
                    FROM news n 
                    LEFT JOIN categories c ON n.category_id = c.id 
                    LEFT JOIN users u ON n.author_id = u.id 
                    WHERE n.title = ? AND n.status = 'published'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $article_title);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Field</th><th>Value</th><th>Should Show Video</th></tr>";
        
        // Title
        echo "<tr>";
        echo "<td>Title</td><td>" . htmlspecialchars($row['title'] ?? '') . "</td><td>-</td></tr>";
        
        // Media Priority
        echo "<tr>";
        echo "<td>Media Priority</td><td>{$row['media_priority']}</td>";
        $should_show_video = ($row['media_priority'] == 2);
        echo "<td style='color: " . ($should_show_video ? 'green' : 'red') . ";'>" . ($should_show_video ? 'YES' : 'NO') . "</td></tr>";
        
        // Video Path
        echo "<tr>";
        echo "<td>Video Path</td><td>" . htmlspecialchars($row['video_path'] ?? 'NULL') . "</td><td>-</td></tr>";
        
        // Video URL
        echo "<tr>";
        echo "<td>Video URL</td><td>" . htmlspecialchars($row['video_url'] ?? 'NULL') . "</td><td>-</td></tr>";
        
        // Image
        echo "<tr>";
        echo "<td>Image</td><td>" . htmlspecialchars($row['image'] ?? 'NULL') . "</td><td>-</td></tr>";
        
        // Status
        echo "<tr>";
        echo "<td>Status</td><td>{$row['status']}</td><td>-</td></tr>";
        
        echo "</table>";
        
        // Check if video file actually exists
        if (!empty($row['video_path'])) {
            $video_file_path = '../' . $row['video_path'];
            echo "<h3>Video File Check:</h3>";
            echo "<p>Path: " . htmlspecialchars($video_file_path) . "</p>";
            echo "<p>File exists: " . (file_exists($video_file_path) ? 'YES' : 'NO') . "</p>";
            if (file_exists($video_file_path)) {
                echo "<p>File size: " . number_format(filesize($video_file_path)) . " bytes</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>Article not found or not published.</p>";
    }
    
    echo "<hr>";
}

echo "<p><a href='../index.php'>→ View Index Page</a></p>";
?>
