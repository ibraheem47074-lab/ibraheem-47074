<?php
require_once '../config/database.php';

echo "<h2>Database Video Check</h2>";

// Check recent news articles with any media
$query = "SELECT id, title, image, video_url, video_path, status, published_at FROM news ORDER BY id DESC LIMIT 20";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Image</th><th>Video URL</th><th>Video Path</th><th>Publish Date</th></tr>";
    
    $video_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $has_video = (!empty($row['video_url']) || !empty($row['video_path']));
        if ($has_video) $video_count++;
        
        echo "<tr style='" . ($has_video ? 'background-color: #e8f5e8;' : '') . "'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . (!empty($row['image']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_url']) ? '✓' : '-') . "</td>";
        echo "<td>" . (!empty($row['video_path']) ? '✓' : '-') . "</td>";
        echo "<td>" . $row['published_at'] . "</td>";
        echo "</tr>";
        
        if ($has_video) {
            echo "<tr><td colspan='7' style='background-color: #f0f8f0; font-size: 12px;'>";
            echo "<strong>Video Details:</strong><br>";
            if (!empty($row['image'])) echo "Image: " . htmlspecialchars($row['image']) . "<br>";
            if (!empty($row['video_url'])) echo "Video URL: " . htmlspecialchars($row['video_url']) . "<br>";
            if (!empty($row['video_path'])) echo "Video Path: " . htmlspecialchars($row['video_path']) . "<br>";
            echo "</td></tr>";
        }
    }
    echo "</table>";
    
    echo "<h3>Summary:</h3>";
    echo "<p>Total articles checked: " . mysqli_num_rows($result) . "</p>";
    echo "<p>Articles with videos: " . $video_count . "</p>";
    
} else {
    echo "Query failed: " . mysqli_error($conn);
}

echo "<hr><h3>Published Video Articles Only</h3>";
$query = "SELECT id, title, image, video_url, video_path FROM news WHERE status = 'published' AND ((video_url IS NOT NULL AND video_url != '') OR (video_path IS NOT NULL AND video_path != '')) ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Image</th><th>Video URL</th><th>Video Path</th><th>View</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . (!empty($row['image']) ? '✓' : '-') . "</td>";
            echo "<td>" . (!empty($row['video_url']) ? '✓' : '-') . "</td>";
            echo "<td>" . (!empty($row['video_path']) ? '✓' : '-') . "</td>";
            
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
        echo "<p>No published articles with videos found.</p>";
    }
} else {
    echo "Query failed: " . mysqli_error($conn);
}
?>

<p><a href="add-news.php">← Back to Add News</a></p>
